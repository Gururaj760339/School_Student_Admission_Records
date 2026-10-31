<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Support\AdmissionFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: School, 1: SchoolClass, 2: Section, 3: Student}
     */
    private function schoolWithStudent(array $overrides = []): array
    {
        $school = School::create(['name' => 'Alpha School']);

        $class = $school->classes()->create([
            'name' => 'Class 1',
            'slug' => SchoolClass::generateSlug('Class 1'),
        ]);

        $section = $class->sections()->create([
            'name' => 'A',
            'slug' => Section::generateSlug($class, 'A'),
        ]);

        $student = $school->students()->create(array_merge([
            'class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => '000123',
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
            'roll_no' => '007',
            'email' => 'asha@example.com',
        ], $overrides));

        return [$school, $class, $section, $student];
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
        [$school] = $this->schoolWithStudent();

        $this->get(route('admin.students.index', $school))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_the_student_list(): void
    {
        [$school] = $this->schoolWithStudent();

        $this->actingAs($this->admin())
            ->get(route('admin.students.index', $school))
            ->assertOk()
            ->assertSee('Asha')
            ->assertSee('000123')
            ->assertSee('Class 1');
    }

    public function test_students_from_other_schools_are_not_listed(): void
    {
        [$school] = $this->schoolWithStudent();

        $otherSchool = School::create(['name' => 'Beta School']);
        $otherSchool->students()->create([
            'first_name' => 'Other',
            'last_name' => 'Student',
            'gender' => 'Male',
            'date_of_birth' => '2014-01-01',
            'mobile' => '01111111111',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.students.index', $school))
            ->assertOk()
            ->assertSee('Asha')
            ->assertDontSee('Other Student');
    }

    public function test_search_filters_by_admission_name_mobile_and_roll(): void
    {
        [$school] = $this->schoolWithStudent();
        $admin = $this->admin();

        foreach (['09876543210', '000123', 'Asha', '007', 'Asha Kumar'] as $search) {
            $this->actingAs($admin)
                ->get(route('admin.students.index', [$school, 'search' => $search]))
                ->assertOk()
                ->assertSee('Asha');
        }

        $this->actingAs($admin)
            ->get(route('admin.students.index', [$school, 'search' => 'zzz-nomatch']))
            ->assertOk()
            ->assertDontSee('Asha');
    }

    public function test_class_section_and_gender_filters_are_applied(): void
    {
        [$school, $class, $section] = $this->schoolWithStudent();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.students.index', [$school, 'class_id' => $class->id, 'section_id' => $section->id]))
            ->assertOk()
            ->assertSee('Asha');

        $this->actingAs($admin)
            ->get(route('admin.students.index', [$school, 'gender' => 'Male']))
            ->assertOk()
            ->assertDontSee('Asha');
    }

    public function test_admin_can_view_a_single_record_with_all_fields(): void
    {
        [$school, , , $student] = $this->schoolWithStudent();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.students.show', [$school, $student]))
            ->assertOk()
            ->assertSee('Asha')
            ->assertSee((string) $student->id);

        foreach (AdmissionFields::labels() as $label) {
            $response->assertSee($label);
        }
    }

    public function test_admin_can_open_the_edit_form(): void
    {
        [$school, , , $student] = $this->schoolWithStudent();

        $this->actingAs($this->admin())
            ->get(route('admin.students.edit', [$school, $student]))
            ->assertOk()
            ->assertSee('name="first_name"', false)
            ->assertSee('Asha');
    }

    public function test_admin_can_update_a_record(): void
    {
        [$school, , , $student] = $this->schoolWithStudent();

        $this->actingAs($this->admin())
            ->put(route('admin.students.update', [$school, $student]), $this->validPayload([
                'first_name' => 'Asha Updated',
            ]))
            ->assertRedirect(route('admin.students.index', $school))
            ->assertSessionHas('success');

        $this->assertSame('Asha Updated', $student->refresh()->first_name);
    }

    public function test_admin_can_delete_a_record(): void
    {
        [$school, , , $student] = $this->schoolWithStudent();

        $this->actingAs($this->admin())
            ->delete(route('admin.students.destroy', [$school, $student]))
            ->assertRedirect(route('admin.students.index', $school))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_missing_record_returns_not_found(): void
    {
        [$school] = $this->schoolWithStudent();

        $this->actingAs($this->admin())
            ->get(route('admin.students.show', [$school, 999999]))
            ->assertNotFound();
    }

    public function test_student_cannot_be_accessed_through_another_school(): void
    {
        [, , , $student] = $this->schoolWithStudent();
        $otherSchool = School::create(['name' => 'Beta School']);

        $this->actingAs($this->admin())
            ->get(route('admin.students.show', [$otherSchool, $student]))
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->get(route('admin.students.edit', [$otherSchool, $student]))
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->delete(route('admin.students.destroy', [$otherSchool, $student]))
            ->assertNotFound();

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }
}
