<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingLogoTest extends TestCase
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

    public function test_guests_cannot_open_or_upload_a_school_logo(): void
    {
        Storage::fake('local');
        $school = $this->school();

        $this->get(route('admin.logo.edit', $school))->assertRedirect(route('login'));

        $this->post(route('admin.logo.store', $school), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect(route('login'));

        $this->assertNull($school->refresh()->logo_path);
    }

    public function test_authenticated_user_can_upload_a_school_logo(): void
    {
        Storage::fake('local');
        $school = $this->school();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.logo.edit', $school))
            ->assertOk()
            ->assertSee('Upload Logo');

        $this->actingAs($admin)
            ->post(route('admin.logo.store', $school), [
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('admin.logo.edit', $school))
            ->assertSessionHas('success');

        $school->refresh();

        $this->assertNotNull($school->logo_path);
        Storage::disk('local')->assertExists($school->logo_path);
    }

    public function test_school_logo_can_be_replaced(): void
    {
        Storage::fake('local');
        $school = $this->school();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.logo.store', $school), [
            'logo' => UploadedFile::fake()->image('first.png'),
        ]);

        $first = $school->refresh()->logo_path;

        $this->actingAs($admin)->post(route('admin.logo.store', $school), [
            'logo' => UploadedFile::fake()->image('second.webp'),
        ]);

        $second = $school->refresh()->logo_path;

        $this->assertNotNull($second);
        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($second);
    }

    public function test_invalid_file_types_and_oversized_files_are_rejected(): void
    {
        Storage::fake('local');
        $school = $this->school();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.logo.store', $school), [
                'logo' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain'),
            ])
            ->assertSessionHasErrors('logo');

        $this->actingAs($admin)
            ->post(route('admin.logo.store', $school), [
                'logo' => UploadedFile::fake()->image('huge.png')->size(3000),
            ])
            ->assertSessionHasErrors('logo');

        $this->assertNull($school->refresh()->logo_path);
    }

    public function test_school_logo_image_route_serves_the_stored_logo_publicly(): void
    {
        Storage::fake('local');
        $school = $this->school();

        $this->get(route('schools.logo.image', ['token' => $school->admission_token]))->assertNotFound();

        $this->actingAs($this->admin())->post(route('admin.logo.store', $school), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $this->get(route('schools.logo.image', ['token' => $school->admission_token]))->assertOk();
    }

    public function test_unknown_school_logo_token_returns_not_found(): void
    {
        $this->get(route('schools.logo.image', ['token' => 'does-not-exist']))->assertNotFound();
    }

    public function test_admission_page_displays_the_uploaded_school_logo(): void
    {
        Storage::fake('local');
        $school = $this->school();

        $this->actingAs($this->admin())->post(route('admin.logo.store', $school), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $this->get(route('admission.create', ['token' => $school->admission_token]))
            ->assertOk()
            ->assertSee('<div class="af-crest"', false)
            ->assertSee('alt="Alpha School logo"', false)
            ->assertSee('school-logo/'.$school->admission_token, false);
    }

    public function test_admission_page_uses_the_default_logo_when_none_is_uploaded(): void
    {
        Storage::fake('local');
        $school = $this->school();

        $this->get(route('admission.create', ['token' => $school->admission_token]))
            ->assertOk()
            ->assertSee('<div class="af-crest"', false)
            ->assertSee('images/logo.png', false);
    }

    public function test_navbar_upload_option_is_only_visible_to_authenticated_users(): void
    {
        Storage::fake('local');
        $school = $this->school();

        $this->get(route('admission.create', ['token' => $school->admission_token]))
            ->assertOk()
            ->assertDontSee('Upload Logo');

        $this->actingAs($this->admin())
            ->get(route('admission.create', ['token' => $school->admission_token]))
            ->assertOk()
            ->assertSee('Upload Logo');
    }

    public function test_legacy_installation_wide_logo_still_works(): void
    {
        Storage::fake('local');

        $this->get(route('branding.logo.edit'))->assertRedirect(route('login'));

        $this->actingAs($this->admin())
            ->post(route('branding.logo.store'), [
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('branding.logo.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(SchoolBranding::hasLogo());
        $this->get(route('branding.logo.image'))->assertOk();
    }
}
