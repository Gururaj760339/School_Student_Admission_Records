<?php

namespace Tests\Feature;

use App\Exceptions\GoogleSheetsException;
use App\Jobs\SyncSectionWorksheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Support\FakeGoogleSheetsService;
use Tests\TestCase;

class ClassSectionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function fake(): FakeGoogleSheetsService
    {
        $fake = new FakeGoogleSheetsService;
        $this->app->instance(GoogleSheetsService::class, $fake);

        return $fake;
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function createClass(User $admin, string $name = 'Class 1'): SchoolClass
    {
        $this->actingAs($admin)
            ->post(route('admin.classes.store'), ['name' => $name])
            ->assertRedirect();

        return SchoolClass::query()->where('name', $name)->firstOrFail();
    }

    private function createSection(User $admin, SchoolClass $class, string $name = 'A'): Section
    {
        $this->actingAs($admin)
            ->post(route('admin.sections.store', ['class' => $class]), ['name' => $name])
            ->assertRedirect();

        return Section::query()
            ->where('school_class_id', $class->id)
            ->where('name', $name)
            ->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
        ];
    }

    private function extractToken(string $html): string
    {
        preg_match('/name="submission_token" value="([^"]+)"/', $html, $matches);

        return $matches[1] ?? '';
    }

    public function test_guests_cannot_manage_classes_or_sections(): void
    {
        $this->fake();

        $this->get(route('admin.classes.index'))->assertRedirect(route('login'));
        $this->post(route('admin.classes.store'), ['name' => 'Class 1'])->assertRedirect(route('login'));
    }

    public function test_admin_can_view_the_management_page(): void
    {
        $this->fake();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index'))
            ->assertOk()
            ->assertSee('Classes &amp; Sections', false);
    }

    public function test_management_page_shows_sections_and_sync_status(): void
    {
        $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);
        $this->createSection($admin, $class, 'A');

        $this->actingAs($admin)
            ->get(route('admin.classes.index'))
            ->assertOk()
            ->assertSee('Class 1')
            ->assertSee('Class 1 - A')
            ->assertSee('Synced');
    }

    public function test_admin_can_create_a_class(): void
    {
        $this->fake();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.classes.store'), ['name' => 'Class 1'])
            ->assertRedirect();

        $this->assertDatabaseHas('school_classes', ['name' => 'Class 1', 'slug' => 'class-1']);
    }

    public function test_class_names_must_be_unique(): void
    {
        $this->fake();
        $admin = $this->admin();

        $this->createClass($admin);

        $this->actingAs($admin)
            ->post(route('admin.classes.store'), ['name' => 'Class 1'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, SchoolClass::query()->count());
    }

    public function test_creating_a_section_creates_its_worksheet_with_headers(): void
    {
        $fake = $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);

        $section = $this->createSection($admin, $class, 'A');

        $this->assertSame('Class 1 - A', $section->worksheet);
        $this->assertSame(Section::SYNC_SYNCED, $section->google_sync_status);
        $this->assertContains('Class 1 - A', $fake->createdWorksheets);
    }

    public function test_section_names_must_be_unique_within_the_same_class(): void
    {
        $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);

        $this->createSection($admin, $class, 'A');

        $this->actingAs($admin)
            ->post(route('admin.sections.store', ['class' => $class]), ['name' => 'A'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Section::query()->count());
    }

    public function test_same_section_name_is_allowed_in_a_different_class(): void
    {
        $this->fake();
        $admin = $this->admin();
        $classOne = $this->createClass($admin, 'Class 1');
        $classTwo = $this->createClass($admin, 'Class 2');

        $this->createSection($admin, $classOne, 'A');
        $this->createSection($admin, $classTwo, 'A');

        $this->assertSame(2, Section::query()->count());
    }

    public function test_renaming_a_section_renames_its_worksheet_without_losing_records(): void
    {
        $fake = $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);
        $section = $this->createSection($admin, $class, 'A');

        $this->actingAs($admin)
            ->put(route('admin.sections.update', ['section' => $section]), ['name' => 'B'])
            ->assertRedirect();

        $section->refresh();

        $this->assertSame('B', $section->name);
        $this->assertSame('Class 1 - B', $section->worksheet);
        $this->assertContains(
            ['from' => 'Class 1 - A', 'to' => 'Class 1 - B'],
            $fake->renamedWorksheets
        );
    }

    public function test_renaming_a_class_resynchronises_its_sections(): void
    {
        $fake = $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);
        $section = $this->createSection($admin, $class, 'A');

        $this->actingAs($admin)
            ->put(route('admin.classes.update', ['class' => $class]), ['name' => 'Grade 1'])
            ->assertRedirect();

        $section->refresh();

        $this->assertSame('Grade 1 - A', $section->worksheet);
        $this->assertContains(
            ['from' => 'Class 1 - A', 'to' => 'Grade 1 - A'],
            $fake->renamedWorksheets
        );
    }

    public function test_repeated_sync_does_not_create_duplicate_worksheets(): void
    {
        $fake = $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);
        $section = $this->createSection($admin, $class, 'A');

        $this->actingAs($admin)
            ->post(route('admin.sections.sync', ['section' => $section]))
            ->assertRedirect();

        $this->assertCount(1, $fake->createdWorksheets);
    }

    public function test_sync_failure_keeps_the_record_and_queues_a_retry(): void
    {
        Queue::fake();
        $fake = $this->fake();
        $fake->throwOnSync = GoogleSheetsException::credentialsMissing('storage/app/google/service-account.json');

        $admin = $this->admin();
        $class = $this->createClass($admin);

        $this->actingAs($admin)
            ->post(route('admin.sections.store', ['class' => $class]), ['name' => 'A'])
            ->assertRedirect()
            ->assertSessionHas('warning');

        $section = Section::query()->firstOrFail();

        $this->assertSame(Section::SYNC_FAILED, $section->google_sync_status);
        $this->assertNotNull($section->google_sync_error);
        Queue::assertPushed(SyncSectionWorksheet::class, fn ($job) => $job->sectionId === $section->id);
    }

    public function test_section_with_records_cannot_be_deleted(): void
    {
        $fake = $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);
        $section = $this->createSection($admin, $class, 'A');

        $fake->worksheetsWithRecords['Class 1 - A'] = true;

        $this->actingAs($admin)
            ->delete(route('admin.sections.destroy', ['section' => $section]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sections', ['id' => $section->id]);
        $this->assertSame([], $fake->deletedWorksheets);
    }

    public function test_empty_section_can_be_deleted_and_its_worksheet_removed(): void
    {
        $fake = $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin);
        $section = $this->createSection($admin, $class, 'A');

        $this->actingAs($admin)
            ->delete(route('admin.sections.destroy', ['section' => $section]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sections', ['id' => $section->id]);
        $this->assertContains('Class 1 - A', $fake->deletedWorksheets);
    }

    public function test_public_admission_page_lists_classes_and_sections_dynamically(): void
    {
        $this->fake();
        $admin = $this->admin();
        $classOne = $this->createClass($admin, 'Class 1');
        $classTwo = $this->createClass($admin, 'Class 2');
        $this->createSection($admin, $classOne, 'A');
        $this->createSection($admin, $classOne, 'B');
        $this->createSection($admin, $classTwo, 'A');

        $this->get(route('admission.select'))
            ->assertOk()
            ->assertSee('Class 1')
            ->assertSee('Class 2')
            ->assertSee('Section A')
            ->assertSee('Section B');
    }

    public function test_admission_uses_the_section_worksheet_from_the_database(): void
    {
        $fake = $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin, 'Class 1');
        $section = $this->createSection($admin, $class, 'A');

        // The form identifies the class and section.
        $this->get(route('admission.create', ['class' => $section->slug]))
            ->assertOk()
            ->assertSee('Class 1 - A');

        // Submitting writes to the correct worksheet, resolved server-side.
        $preview = $this->post(route('admission.preview', ['class' => $section->slug]), $this->validPayload());
        $token = $this->extractToken($preview->getContent());

        $this->post(route('admission.submit', ['class' => $section->slug]), ['submission_token' => $token])
            ->assertOk()
            ->assertSee('Admission submitted');

        $this->assertCount(1, $fake->appended);
        $this->assertSame('Class 1 - A', $fake->appended[0]['worksheet']);
    }

    public function test_unknown_section_slug_cannot_reach_a_worksheet(): void
    {
        $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin, 'Class 1');
        $this->createSection($admin, $class, 'A');

        $this->get(route('admission.create', ['class' => 'does-not-exist']))->assertNotFound();

        $this->post(route('admission.preview', ['class' => 'does-not-exist']), $this->validPayload())
            ->assertNotFound();
    }

    public function test_admission_page_drops_legacy_classes_once_a_database_class_exists(): void
    {
        $this->fake();
        $admin = $this->admin();
        $class = $this->createClass($admin, 'Class 7');
        $this->createSection($admin, $class, 'A');

        $this->get(route('admission.select'))
            ->assertOk()
            ->assertSee('Class 7')
            ->assertDontSee('Class 2');
    }
}
