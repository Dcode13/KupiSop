<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_home_to_menu_for_guests(): void
    {
        // Beranda mengarahkan tamu ke halaman menu publik.
        $this->get('/')->assertRedirect('/menu');
    }
}
