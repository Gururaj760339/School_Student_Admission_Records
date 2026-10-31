<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Support\AdmissionFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: School, 1: SchoolClass, 2: Section, 3: SchoolClass, 4: Section}
     */
    private function schoolWithClasses(): array
    {
        $school = School::create(['name' => 'Alpha School']);

        $classOne = $school->classes()->create([
            'name' => 'Class 1',
            'slug' => SchoolClass::generateSlug('Class 1'),
        ]);
        $sectionA = $classOne->sections()->create([
            'name' => 'A',
            'slug' => Section::generateSlug($classOne, 'A'),
        ]);

        $classTwo = $school->classes()->create([
            'name' => 'Class 2',
            'slug' => SchoolClass::generateSlug('Class 2'),
        ]);
        $sectionB = $classTwo->sections()->create([
            'name' => 'B',
            'slug' => Section::generateSlug($classTwo, 'B'),
        ]);

        return [$school, $classOne, $sectionA, $classTwo, $sectionB];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'admission_number' => '000123',
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
            'roll_no' => '007',
            'email' => 'asha@example.com',
            'height' => '120.5',
            'weight' => '30',
        ], $overrides);
    }

    private function extractToken(string $html): string
    {
        preg_match('/name="submission_token" value="([^"]+)"/', $html, $matches);

        return $matches[1] ?? '';
    }

    public function test_root_redirects_to_class_selection(): void
    {
        $this->get('/')->assertRedirect(route('admission.select'));
    }

    public function test_guests_can_open_the_public_admission_form_for_a_school(): void
    {
        [$school, $class, $section] = $this->schoolWithClasses();

        $this->get(route('admission.create', ['token' => $school->admission_token]))
            ->assertOk()
            ->assertSee('Alpha School')
            ->assertSee($class->name)
            ->assertSee('Class 2')
            ->assertSee($section->name);
    }

    public function test_form_renders_every_configured_field(): void
    {
        [$school] = $this->schoolWithClasses();

        $response = $this->get(route('admission.create', ['token' => $school->admission_token]))
            ->assertOk();

        foreach (AdmissionFields::names() as $name) {
            $response->assertSee('name="'.$name.'"', false);
        }

        $response->assertSee('name="class_id"', false);
        $response->assertSee('name="section_id"', false);
    }

    public function test_unknown_admission_token_returns_not_found(): void
    {
        $this->get(route('admission.create', ['token' => 'does-not-exist']))->assertNotFound();

        $this->post(route('admission.preview', ['token' => 'does-not-exist']), $this->validPayload())
            ->assertNotFound();
    }

    public function test_preview_does_not_write_to_the_database(): void
    {
        [$school] = $this->schoolWithClasses();

        $this->post(route('admission.preview', ['token' => $school->admission_token]), $this->validPayload())
            ->assertOk()
            ->assertSee('Review')
            ->assertSee('Asha');

        $this->assertDatabaseCount('students', 0);
    }

    public function test_preview_validates_required_fields(): void
    {
        [$school] = $this->schoolWithClasses();

        $this->post(route('admission.preview', ['token' => $school->admission_token]), [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'gender', 'date_of_birth', 'mobile']);

        $this->assertDatabaseCount('students', 0);
    }

    public function test_preview_rejects_invalid_email_and_date(): void
    {
        [$school] = $this->schoolWithClasses();

        $this->post(route('admission.preview', ['token' => $school->admission_token]), $this->validPayload([
            'email' => 'not-an-email',
            'date_of_birth' => '2999-01-01',
        ]))->assertSessionHasErrors(['email', 'date_of_birth']);
    }

    public function test_preview_rejects_a_class_from_another_school(): void
    {
        [$school] = $this->schoolWithClasses();

        $otherSchool = School::create(['name' => 'Beta School']);
        $otherClass = $otherSchool->classes()->create([
            'name' => 'Class 9',
            'slug' => SchoolClass::generateSlug('Class 9'),
        ]);

        $this->post(route('admission.preview', ['token' => $school->admission_token]), $this->validPayload([
            'class_id' => $otherClass->id,
        ]))->assertSessionHasErrors('class_id');
    }

    public function test_confirm_and_submit_creates_a_student_for_the_selected_class(): void
    {
        [$school, , , $classTwo, $sectionB] = $this->schoolWithClasses();

        $preview = $this->post(route('admission.preview', ['token' => $school->admission_token]), $this->validPayload([
            'class_id' => $classTwo->id,
            'section_id' => $sectionB->id,
        ]))->assertOk();

        $token = $this->extractToken($preview->getContent());
        $this->assertNotSame('', $token);

        $this->post(route('admission.submit', ['token' => $school->admission_token]), ['submission_token' => $token])
            ->assertOk()
            ->assertSee('Admission Submitted');

        $student = Student::query()->firstOrFail();

        $this->assertSame($school->id, $student->school_id);
        $this->assertSame($classTwo->id, $student->class_id);
        $this->assertSame($sectionB->id, $student->section_id);
        $this->assertSame('Asha', $student->first_name);
        $this->assertSame('Kumar', $student->last_name);
    }

    public function test_submit_without_a_valid_token_is_rejected(): void
    {
        [$school] = $this->schoolWithClasses();

        $this->post(route('admission.submit', ['token' => $school->admission_token]), ['submission_token' => 'nope'])
            ->assertRedirect(route('admission.create', ['token' => $school->admission_token]))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('students', 0);
    }

    public function test_submission_token_cannot_be_reused(): void
    {
        [$school] = $this->schoolWithClasses();

        $preview = $this->post(route('admission.preview', ['token' => $school->admission_token]), $this->validPayload());
        $token = $this->extractToken($preview->getContent());

        $this->post(route('admission.submit', ['token' => $school->admission_token]), ['submission_token' => $token])
            ->assertOk();

        $this->post(route('admission.submit', ['token' => $school->admission_token]), ['submission_token' => $token])
            ->assertRedirect(route('admission.create', ['token' => $school->admission_token]))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('students', 1);
    }

    public function test_leading_zeros_in_identifiers_are_preserved(): void
    {
        [$school] = $this->schoolWithClasses();

        $preview = $this->post(route('admission.preview', ['token' => $school->admission_token]), $this->validPayload([
            'admission_number' => '000123',
            'roll_no' => '007',
            'mobile' => '09876543210',
        ]));
        $token = $this->extractToken($preview->getContent());

        $this->post(route('admission.submit', ['token' => $school->admission_token]), ['submission_token' => $token]);

        $student = Student::query()->firstOrFail();

        $this->assertSame('000123', $student->admission_number);
        $this->assertSame('007', $student->roll_no);
        $this->assertSame('09876543210', $student->mobile);
    }

    public function test_edit_returns_the_pending_preview_data(): void
    {
        [$school] = $this->schoolWithClasses();

        $this->post(route('admission.preview', ['token' => $school->admission_token]), $this->validPayload());

        $this->get(route('admission.edit', ['token' => $school->admission_token]))
            ->assertOk()
            ->assertSee('Asha')
            ->assertSee('Kumar');
    }
}
