<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_with_an_empty_database(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertViewIs('front.home');
    }
}
