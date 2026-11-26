<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_attached_to_responses(): void
    {
        $response = $this->get(route('splash'));

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('Content-Security-Policy');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('https://cdn.jsdelivr.net', $csp);
        $this->assertStringContainsString('https://challenges.cloudflare.com', $csp);
        $this->assertStringContainsString("worker-src 'self' blob:", $csp);
        $this->assertStringContainsString("child-src 'self' https://challenges.cloudflare.com blob:", $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([^']+)'/", $csp);

        preg_match("/script-src 'self' 'nonce-([^']+)'/", $csp, $matches);
        $nonce = $matches[1] ?? '';
        $this->assertNotSame('', $nonce);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
        $response->assertSee('nonce="'.$nonce.'"', false);
    }

    public function test_hsts_header_is_attached_on_https_requests(): void
    {
        $response = $this->get('https://mysubshield.me/');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
