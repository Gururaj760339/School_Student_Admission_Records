<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleSheetsService;
use App\Support\SchoolBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakeGoogleSheetsService;
use Tests\TestCase;

class BrandingLogoTest extends TestCase
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

    public function test_guests_cannot_open_or_upload_the_logo(): void
    {
        $this->fake();
        Storage::fake('local');

        $this->get(route('branding.logo.edit'))->assertRedirect(route('login'));

        $this->post(route('branding.logo.store'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect(route('login'));

        $this->assertNull(SchoolBranding::logoPath());
    }

    public function test_authenticated_user_can_upload_a_logo(): void
    {
        $this->fake();
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('branding.logo.edit'))
            ->assertOk()
            ->assertSee('Upload Logo');

        $this->actingAs($admin)
            ->post(route('branding.logo.store'), [
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('branding.logo.edit'))
            ->assertSessionHas('success');

        $path = SchoolBranding::logoPath();

        $this->assertNotNull($path);
        Storage::disk('local')->assertExists($path);
        $this->assertTrue(SchoolBranding::hasLogo());
    }

    public function test_navbar_upload_option_is_only_visible_to_authenticated_users(): void
    {
        $this->fake();
        Storage::fake('local');

        $this->get(route('admission.create', ['class' => 'class_1']))
            ->assertOk()
            ->assertDontSee('Upload Logo');

        $this->actingAs($this->admin())
            ->get(route('admission.create', ['class' => 'class_1']))
            ->assertOk()
            ->assertSee('Upload Logo');
    }

    public function test_uploaded_logo_can_be_replaced(): void
    {
        $this->fake();
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('branding.logo.store'), [
            'logo' => UploadedFile::fake()->image('first.png'),
        ]);

        $first = SchoolBranding::logoPath();

        $this->actingAs($admin)->post(route('branding.logo.store'), [
            'logo' => UploadedFile::fake()->image('second.webp'),
        ]);

        $second = SchoolBranding::logoPath();

        $this->assertNotNull($second);
        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($second);
    }

    public function test_invalid_file_types_and_oversized_files_are_rejected(): void
    {
        $this->fake();
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('branding.logo.store'), [
                'logo' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain'),
            ])
            ->assertSessionHasErrors('logo');

        $this->actingAs($admin)
            ->post(route('branding.logo.store'), [
                'logo' => UploadedFile::fake()->image('huge.png')->size(3000),
            ])
            ->assertSessionHasErrors('logo');

        $this->assertNull(SchoolBranding::logoPath());
    }

    public function test_logo_image_route_serves_the_stored_logo_publicly(): void
    {
        $this->fake();
        Storage::fake('local');

        $this->get(route('branding.logo.image'))->assertNotFound();

        $this->actingAs($this->admin())->post(route('branding.logo.store'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $this->get(route('branding.logo.image'))->assertOk();
    }

    public function test_admission_page_hides_crest_when_no_logo_is_uploaded(): void
    {
        $this->fake();
        Storage::fake('local');

        $this->get(route('admission.create', ['class' => 'class_1']))
            ->assertOk()
            ->assertDontSee('<div class="af-crest"', false)
            ->assertDontSee('<img', false);
    }

    public function test_admission_page_displays_the_uploaded_logo(): void
    {
        $this->fake();
        Storage::fake('local');

        $this->actingAs($this->admin())->post(route('branding.logo.store'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $this->get(route('admission.create', ['class' => 'class_1']))
            ->assertOk()
            ->assertSee('<div class="af-crest"', false)
            ->assertSee('alt="YesEdu School And College logo"', false)
            ->assertSee('branding/logo/image', false);
    }
}
