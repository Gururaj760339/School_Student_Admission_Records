<?php

namespace Tests\Feature;

use App\Exceptions\GoogleSheetsException;
use App\Models\User;
use App\Services\GoogleSheetsService;
use App\Support\AdmissionFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakeGoogleSheetsService;
use Tests\TestCase;

class AdminStudentTest extends TestCase
{
    use RefreshDatabase;

    private function fake(): FakeGoogleSheetsService
    {
        $fake = new FakeGoogleSheetsService;
        $fake->records = [$this->sampleRecord()];
        $this->app->instance(GoogleSheetsService::class, $fake);

        return $fake;
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleRecord(array $overrides = []): array
    {
        return array_merge(AdmissionFields::blank(), [
            'admission_number' => '000123',
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
            'roll_no' => '007',
            'email' => 'asha@example.com',
            'record_id' => 'rec-1',
            '_class_slug' => 'class_1',
            '_class_name' => 'Class 1',
            '_row' => 2,
        ], $overrides);
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
        ], $overrides);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->fake();

        $this->get(route('admin.students.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_the_student_list(): void
    {
        $this->fake();

        $this->actingAs($this->admin())
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Asha')
            ->assertSee('000123')
            ->assertSee('Class 1');
    }

    public function test_search_filters_by_admission_name_mobile_and_roll(): void
    {
        $this->fake();

        $this->actingAs($this->admin())
            ->get(route('admin.students.index', ['search' => '09876543210']))
            ->assertOk()
            ->assertSee('Asha');

        $this->actingAs($this->admin())
            ->get(route('admin.students.index', ['search' => 'zzz-nomatch']))
            ->assertOk()
            ->assertDontSee('Asha');
    }

    public function test_class_and_gender_filters_are_applied(): void
    {
        $this->fake();

        $this->actingAs($this->admin())
            ->get(route('admin.students.index', ['class' => 'class_2']))
            ->assertOk()
            ->assertDontSee('Asha');

        $this->actingAs($this->admin())
            ->get(route('admin.students.index', ['gender' => 'Male']))
            ->assertOk()
            ->assertDontSee('Asha');
    }

    public function test_admin_can_view_a_single_record_with_all_fields(): void
    {
        $this->fake();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.students.show', ['class' => 'class_1', 'recordId' => 'rec-1']))
            ->assertOk()
            ->assertSee('Asha')
            ->assertSee('rec-1');

        foreach (AdmissionFields::labels() as $label) {
            $response->assertSee($label);
        }
    }

    public function test_admin_can_open_the_edit_form(): void
    {
        $this->fake();

        $this->actingAs($this->admin())
            ->get(route('admin.students.edit', ['class' => 'class_1', 'recordId' => 'rec-1']))
            ->assertOk()
            ->assertSee('name="first_name"', false)
            ->assertSee('Asha');
    }

    public function test_admin_can_update_a_record(): void
    {
        $fake = $this->fake();

        $this->actingAs($this->admin())
            ->put(route('admin.students.update', ['class' => 'class_1', 'recordId' => 'rec-1']), $this->validPayload([
                'first_name' => 'Asha Updated',
            ]))
            ->assertRedirect(route('admin.students.index', ['class' => 'class_1']));

        $this->assertCount(1, $fake->updated);
        $this->assertSame('rec-1', $fake->updated[0]['record_id']);
        $this->assertSame('Class 1', $fake->updated[0]['worksheet']);
        $this->assertSame('Asha Updated', $fake->updated[0]['data']['first_name']);
    }

    public function test_admin_can_delete_only_the_selected_record(): void
    {
        $fake = $this->fake();

        $this->actingAs($this->admin())
            ->delete(route('admin.students.destroy', ['class' => 'class_1', 'recordId' => 'rec-1']))
            ->assertRedirect(route('admin.students.index', ['class' => 'class_1']));

        $this->assertCount(1, $fake->deleted);
        $this->assertSame('rec-1', $fake->deleted[0]['record_id']);
    }

    public function test_missing_record_returns_not_found(): void
    {
        $this->fake();

        $this->actingAs($this->admin())
            ->get(route('admin.students.show', ['class' => 'class_1', 'recordId' => 'does-not-exist']))
            ->assertNotFound();
    }

    public function test_service_errors_are_shown_without_false_success(): void
    {
        $fake = $this->fake();
        $fake->throwOnRead = GoogleSheetsException::credentialsMissing('storage/app/google/service-account.json');

        $this->actingAs($this->admin())
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('credentials')
            ->assertDontSee('Asha');
    }
}
