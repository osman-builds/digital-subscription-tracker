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
        $response->assertHeader('X-XSS-Protection', '0');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
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

    public function test_hsts_header_is_attached_when_forwarded_https_from_trusted_cloudflare_proxy(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '173.245.48.10',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.42',
        ])->get(route('splash'));

        $response->assertOk();
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_untrusted_proxy_cannot_spoof_forwarded_https_or_client_ip(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.77',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.99',
        ])->get(route('splash'));

        $response->assertOk();
        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}
