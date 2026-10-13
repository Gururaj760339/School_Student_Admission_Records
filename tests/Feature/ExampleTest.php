<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The application root redirects to the admission class selection.
     */
    public function test_the_application_redirects_to_the_admission_flow(): void
    {
        $this->get('/')->assertRedirect(route('admission.select'));
    }
}
