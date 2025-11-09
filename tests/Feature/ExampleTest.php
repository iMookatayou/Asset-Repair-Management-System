<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Root redirects to /login in current app, assert redirect (302) then login page 200
        $response = $this->get('/');
        $response->assertRedirect('/login');
        $login = $this->get('/login');
        $login->assertStatus(200);
    }
}
