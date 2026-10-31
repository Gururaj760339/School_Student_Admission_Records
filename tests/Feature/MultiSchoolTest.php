<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiSchoolTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_redirects_to_school_management(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.schools.index'));
    }

    public function test_admin_can_create_a_school_and_manage_its_classes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.schools.store'), ['name' => 'Alpha School'])
            ->assertRedirect();

        $school = School::query()->where('name', 'Alpha School')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.schools.show', $school))
            ->assertOk()
            ->assertSee('Alpha School')
            ->assertSee($school->admissionUrl());

        $this->actingAs($user)
            ->post(route('admin.classes.store', $school), [
                'name' => 'Class 1',
                'section_name' => 'A',
            ])
            ->assertRedirect();

        $class = $school->classes()->where('name', 'Class 1')->firstOrFail();
        $section = $class->sections()->where('name', 'A')->firstOrFail();

        $this->assertSame($school->id, $class->school_id);
        $this->assertSame($class->id, $section->school_class_id);

        $this->actingAs($user)
            ->get(route('admin.classes.index', $school))
            ->assertOk()
            ->assertSee('Class 1')
            ->assertSee('Section A')
            ->assertDontSee('Google Sheets');
    }

    public function test_public_admission_preview_and_submit_saves_to_the_correct_school(): void
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

        $payload = [
            'class_id' => $class->id,
            'section_id' => $section->id,
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
            'email' => 'asha@example.com',
        ];

        $preview = $this->post(route('admission.preview', ['token' => $school->admission_token]), $payload);

        $preview->assertOk();
        $preview->assertSee('Review &amp; Confirm', false);
        $preview->assertSee('Asha');

        preg_match('/name="submission_token" value="([^"]+)"/', $preview->getContent(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        $this->post(route('admission.submit', ['token' => $school->admission_token]), [
            'submission_token' => $matches[1],
        ])->assertOk()->assertSee('Admission Submitted');

        $student = Student::query()->firstOrFail();

        $this->assertSame($school->id, $student->school_id);
        $this->assertSame($class->id, $student->class_id);
        $this->assertSame($section->id, $student->section_id);
        $this->assertSame('Asha', $student->first_name);
        $this->assertSame('09876543210', $student->mobile);
    }

    public function test_students_cannot_be_accessed_through_another_school(): void
    {
        $user = User::factory()->create();

        $schoolA = School::create(['name' => 'Alpha School']);
        $schoolB = School::create(['name' => 'Beta School']);

        $class = $schoolA->classes()->create([
            'name' => 'Class 1',
            'slug' => SchoolClass::generateSlug('Class 1'),
        ]);

        $student = $schoolA->students()->create([
            'class_id' => $class->id,
            'first_name' => 'Asha',
            'last_name' => 'Kumar',
            'gender' => 'Female',
            'date_of_birth' => '2015-06-01',
            'mobile' => '09876543210',
        ]);

        $this->actingAs($user)
            ->get(route('admin.students.show', [$schoolB, $student]))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('admin.students.index', $schoolB))
            ->assertOk()
            ->assertDontSee('Asha');
    }

    public function test_school_with_classes_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $school = School::create(['name' => 'Alpha School']);

        $school->classes()->create([
            'name' => 'Class 1',
            'slug' => SchoolClass::generateSlug('Class 1'),
        ]);

        $this->actingAs($user)
            ->delete(route('admin.schools.destroy', $school))
            ->assertRedirect(route('admin.schools.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('schools', ['id' => $school->id]);
    }

    public function test_school_logo_upload_is_stored_per_school(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $school = School::create(['name' => 'Alpha School']);

        $this->actingAs($user)
            ->post(route('admin.logo.store', $school), [
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('admin.logo.edit', $school));

        $school->refresh();

        $this->assertNotNull($school->logo_path);
        Storage::disk('local')->assertExists($school->logo_path);
    }

    public function test_unknown_admission_token_returns_not_found(): void
    {
        $this->get(route('admission.create', ['token' => 'not-a-real-token']))
            ->assertNotFound();
    }
}
