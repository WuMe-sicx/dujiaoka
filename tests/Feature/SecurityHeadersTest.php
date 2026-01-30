<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_response_contains_x_frame_options(): void
    {
        $response = $this->get('/');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_response_contains_x_content_type_options(): void
    {
        $response = $this->get('/');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_response_contains_x_xss_protection(): void
    {
        $response = $this->get('/');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_response_contains_referrer_policy(): void
    {
        $response = $this->get('/');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_response_contains_permissions_policy(): void
    {
        $response = $this->get('/');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }
}
