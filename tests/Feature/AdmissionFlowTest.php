<?php

namespace Tests\Feature;

use App\Services\GoogleSheetsService;
use App\Support\AdmissionClasses;
use App\Support\AdmissionFields;
use Tests\Support\FakeGoogleSheetsService;
use Tests\TestCase;

class AdmissionFlowTest extends TestCase
{
    private function fake(): FakeGoogleSheetsService
    {
        $fake = new FakeGoogleSheetsService;
        $this->app->instance(GoogleSheetsService::class, $fake);

        return $fake;
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
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
        $this->fake();

        $this->get('/')->assertRedirect(route('admission.select'));
    }

    public function test_class_selection_lists_configured_classes(): void
    {
        $this->fake();

        $this->get(route('admission.select'))
            ->assertOk()
            ->assertSee('Class 1')
            ->assertSee('Class 2');
    }

    public function test_form_renders_every_configured_field(): void
    {
        $this->fake();

        $response = $this->get(route('admission.create', ['class' => 'class_1']))->assertOk();

        foreach (AdmissionFields::names() as $name) {
            $response->assertSee('name="'.$name.'"', false);
        }
    }

    public function test_unknown_class_returns_not_found(): void
    {
        $this->fake();

        $this->get(route('admission.create', ['class' => 'class_999']))->assertNotFound();
    }

    public function test_preview_does_not_write_to_sheets(): void
    {
        $fake = $this->fake();

        $this->post(route('admission.preview', ['class' => 'class_1']), $this->validPayload())
            ->assertOk()
            ->assertSee('Review')
            ->assertSee('Asha');

        $this->assertCount(0, $fake->appended, 'Preview must never write to Google Sheets.');
    }

    public function test_preview_validates_required_fields(): void
    {
        $fake = $this->fake();

        $this->post(route('admission.preview', ['class' => 'class_1']), [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'gender', 'date_of_birth', 'mobile']);

        $this->assertCount(0, $fake->appended);
    }

    public function test_preview_rejects_invalid_email_and_date(): void
    {
        $this->fake();

        $this->post(route('admission.preview', ['class' => 'class_1']), $this->validPayload([
            'email' => 'not-an-email',
            'date_of_birth' => '2999-01-01',
        ]))->assertSessionHasErrors(['email', 'date_of_birth']);
    }

    public function test_confirm_and_submit_appends_to_the_selected_class(): void
    {
        $fake = $this->fake();

        $preview = $this->post(route('admission.preview', ['class' => 'class_2']), $this->validPayload());
        $token = $this->extractToken($preview->getContent());

        $this->assertNotSame('', $token);

        $this->post(route('admission.submit', ['class' => 'class_2']), ['submission_token' => $token])
            ->assertOk()
            ->assertSee('Admission submitted');

        $this->assertCount(1, $fake->appended);
        $this->assertSame('Class 2', $fake->appended[0]['worksheet']);
        $this->assertSame('generated-1', $fake->appended[0]['data']['record_id']);
    }

    public function test_submit_without_a_valid_token_is_rejected(): void
    {
        $fake = $this->fake();

        $this->post(route('admission.submit', ['class' => 'class_1']), ['submission_token' => 'nope'])
            ->assertRedirect(route('admission.create', ['class' => 'class_1']))
            ->assertSessionHas('error');

        $this->assertCount(0, $fake->appended);
    }

    public function test_submission_token_cannot_be_reused(): void
    {
        $fake = $this->fake();

        $preview = $this->post(route('admission.preview', ['class' => 'class_1']), $this->validPayload());
        $token = $this->extractToken($preview->getContent());

        $this->post(route('admission.submit', ['class' => 'class_1']), ['submission_token' => $token])->assertOk();

        // Replaying the same token must not create a duplicate record.
        $this->post(route('admission.submit', ['class' => 'class_1']), ['submission_token' => $token])
            ->assertRedirect(route('admission.create', ['class' => 'class_1']));

        $this->assertCount(1, $fake->appended);
    }

    public function test_leading_zeros_in_identifiers_are_preserved(): void
    {
        $fake = $this->fake();

        $preview = $this->post(route('admission.preview', ['class' => 'class_1']), $this->validPayload([
            'admission_number' => '000123',
            'roll_no' => '007',
            'mobile' => '09876543210',
        ]));
        $token = $this->extractToken($preview->getContent());

        $this->post(route('admission.submit', ['class' => 'class_1']), ['submission_token' => $token]);

        $stored = $fake->appended[0]['data'];
        $this->assertSame('000123', $stored['admission_number']);
        $this->assertSame('007', $stored['roll_no']);
        $this->assertSame('09876543210', $stored['mobile']);
    }

    public function test_all_configured_classes_are_valid_targets(): void
    {
        $this->fake();

        foreach (AdmissionClasses::slugs() as $slug) {
            $this->get(route('admission.create', ['class' => $slug]))->assertOk();
        }
    }
}
