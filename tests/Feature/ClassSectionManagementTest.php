<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassSectionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function school(string $name = 'Alpha School'): School
    {
        return School::create(['name' => $name]);
    }

    private function createClass(School $school, string $name = 'Class 1', ?string $section = null): SchoolClass
    {
        $payload = ['name' => $name];

        if ($section !== null) {
            $payload['section_name'] = $section;
        }

        $this->actingAs($this->admin())
            ->post(route('admin.classes.store', $school), $payload)
            ->assertRedirect();

        return SchoolClass::query()
            ->where('school_id', $school->id)
            ->where('name', $name)
            ->firstOrFail();
    }

    private function createSection(School $school, SchoolClass $class, string $name = 'A'): Section
    {
        $this->actingAs($this->admin())
            ->post(route('admin.sections.store', [$school, $class]), ['name' => $name])
            ->assertRedirect();

        return Section::query()
            ->where('school_class_id', $class->id)
            ->where('name', $name)
            ->firstOrFail();
    }

    public function test_guests_cannot_manage_classes_or_sections(): void
    {
        $school = $this->school();

        $this->get(route('admin.classes.index', $school))->assertRedirect(route('login'));
        $this->post(route('admin.classes.store', $school), ['name' => 'Class 1'])->assertRedirect(route('login'));
    }

    public function test_admin_can_view_the_management_page(): void
    {
        $school = $this->school();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index', $school))
            ->assertOk()
            ->assertSee('Classes &amp; Sections', false)
            ->assertSee('Alpha School');
    }

    public function test_management_page_shows_classes_and_sections(): void
    {
        $school = $this->school();
        $class = $this->createClass($school, 'Class 1', 'A');

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index', $school))
            ->assertOk()
            ->assertSee('Class 1')
            ->assertSee('Section A')
            ->assertDontSee('Google Sheets');
    }

    public function test_admin_can_create_a_class_with_a_section(): void
    {
        $school = $this->school();

        $this->actingAs($this->admin())
            ->post(route('admin.classes.store', $school), [
                'name' => 'Class 1',
                'section_name' => 'A',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('school_classes', [
            'school_id' => $school->id,
            'name' => 'Class 1',
            'slug' => 'class-1',
        ]);

        $this->assertDatabaseHas('sections', ['name' => 'A']);
    }

    public function test_admin_can_create_a_class_without_a_section(): void
    {
        $school = $this->school();

        $this->actingAs($this->admin())
            ->post(route('admin.classes.store', $school), ['name' => 'Class 1'])
            ->assertRedirect();

        $this->assertDatabaseHas('school_classes', ['name' => 'Class 1']);
        $this->assertDatabaseCount('sections', 0);
    }

    public function test_class_names_must_be_unique_within_a_school(): void
    {
        $school = $this->school();
        $this->createClass($school);

        $this->actingAs($this->admin())
            ->post(route('admin.classes.store', $school), ['name' => 'Class 1'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, SchoolClass::query()->count());
    }

    public function test_the_same_class_name_is_allowed_in_a_different_school(): void
    {
        $schoolA = $this->school('Alpha School');
        $schoolB = $this->school('Beta School');

        $this->createClass($schoolA, 'Class 1');
        $this->createClass($schoolB, 'Class 1');

        $this->assertSame(2, SchoolClass::query()->where('name', 'Class 1')->count());
    }

    public function test_section_names_must_be_unique_within_the_same_class(): void
    {
        $school = $this->school();
        $class = $this->createClass($school, 'Class 1', 'A');

        $this->actingAs($this->admin())
            ->post(route('admin.sections.store', [$school, $class]), ['name' => 'A'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Section::query()->count());
    }

    public function test_same_section_name_is_allowed_in_a_different_class(): void
    {
        $school = $this->school();
        $classOne = $this->createClass($school, 'Class 1');
        $classTwo = $this->createClass($school, 'Class 2');

        $this->createSection($school, $classOne, 'A');
        $this->createSection($school, $classTwo, 'A');

        $this->assertSame(2, Section::query()->count());
    }

    public function test_admin_can_rename_a_class_and_a_section(): void
    {
        $school = $this->school();
        $class = $this->createClass($school, 'Class 1', 'A');
        $section = $class->sections()->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.classes.update', [$school, $class]), ['name' => 'Grade 1'])
            ->assertRedirect();

        $this->actingAs($this->admin())
            ->put(route('admin.sections.update', [$school, $class, $section]), ['name' => 'B'])
            ->assertRedirect();

        $this->assertSame('Grade 1', $class->refresh()->name);
        $this->assertSame('B', $section->refresh()->name);
    }

    public function test_section_with_students_cannot_be_deleted(): void
    {
        $school = $this->school();
        $class = $this->createClass($school, 'Class 1', 'A');
        $section = $class->sections()->firstOrFail();

        $school->students()->create([
            'class_id' => $class->id,
            'section_id' => $section->id,
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.sections.destroy', [$school, $class, $section]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sections', ['id' => $section->id]);
    }

    public function test_empty_section_can_be_deleted(): void
    {
        $school = $this->school();
        $class = $this->createClass($school, 'Class 1', 'A');
        $section = $class->sections()->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('admin.sections.destroy', [$school, $class, $section]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sections', ['id' => $section->id]);
    }

    public function test_class_with_students_cannot_be_deleted(): void
    {
        $school = $this->school();
        $class = $this->createClass($school, 'Class 1', 'A');

        $school->students()->create([
            'class_id' => $class->id,
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.classes.destroy', [$school, $class]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('school_classes', ['id' => $class->id]);
    }

    public function test_empty_class_can_be_deleted(): void
    {
        $school = $this->school();
        $class = $this->createClass($school, 'Class 1', 'A');

        $this->actingAs($this->admin())
            ->delete(route('admin.classes.destroy', [$school, $class]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('school_classes', ['id' => $class->id]);
        $this->assertDatabaseCount('sections', 0);
    }

    public function test_classes_and_sections_cannot_be_managed_through_another_school(): void
    {
        $schoolA = $this->school('Alpha School');
        $schoolB = $this->school('Beta School');
        $class = $this->createClass($schoolA, 'Class 1', 'A');
        $section = $class->sections()->firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index', $schoolB))
            ->assertOk()
            ->assertSee('No Classes Found');

        $this->actingAs($this->admin())
            ->put(route('admin.classes.update', [$schoolB, $class]), ['name' => 'Hijacked'])
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->put(route('admin.sections.update', [$schoolB, $class, $section]), ['name' => 'Hijacked'])
            ->assertNotFound();
    }

    public function test_public_admission_page_lists_classes_and_sections_dynamically(): void
    {
        $school = $this->school();
        $classOne = $this->createClass($school, 'Class 1', 'A');
        $classTwo = $this->createClass($school, 'Class 2', 'B');
        $this->createSection($school, $classOne, 'C');

        $this->get(route('admission.create', ['token' => $school->admission_token]))
            ->assertOk()
            ->assertSee('Class 1')
            ->assertSee('Class 2')
            ->assertSee('Class 1 - A')
            ->assertSee('Class 1 - C')
            ->assertSee('Class 2 - B');
    }

    public function test_unknown_school_token_cannot_reach_the_admission_form(): void
    {
        $this->get(route('admission.create', ['token' => 'does-not-exist']))->assertNotFound();
    }
}
