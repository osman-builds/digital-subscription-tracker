<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\BrandIconService;
use App\Services\SubscriptionUrlNormalizer;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class SubscriptionUrlNormalizerTest extends TestCase
{
    private SubscriptionUrlNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new SubscriptionUrlNormalizer(new BrandIconService);
    }

    public function test_it_upgrades_http_and_bare_domains_to_https_and_fills_logo_url(): void
    {
        $request = Request::create('/subscriptions', 'POST', [
            'name' => 'Custom Cloud',
            'website_url' => 'http://example.org',
            'cancellation_url' => 'billing.example.org/cancel',
        ]);

        $this->normalizer->normalize($request);

        $this->assertSame('https://example.org', $request->input('website_url'));
        $this->assertSame('https://billing.example.org/cancel', $request->input('cancellation_url'));
        $this->assertStringContainsString('example.org', (string) $request->input('logo_url'));
    }

    public function test_it_preserves_dangerous_schemes_so_validation_rejects_them(): void
    {
        $request = Request::create('/subscriptions', 'POST', [
            'name' => 'Malicious',
            'website_url' => 'javascript:alert(1)',
            'cancellation_url' => 'data:text/html,<script>alert(1)</script>',
        ]);

        $this->normalizer->normalize($request);

        $this->assertSame('javascript:alert(1)', $request->input('website_url'));
        $this->assertSame('data:text/html,<script>alert(1)</script>', $request->input('cancellation_url'));
    }

    public function test_it_infers_brand_website_and_logo_from_known_service_name(): void
    {
        $request = Request::create('/subscriptions', 'POST', [
            'name' => 'Netflix',
        ]);

        $this->normalizer->normalize($request);

        $this->assertSame('https://netflix.com', $request->input('website_url'));
        $this->assertStringContainsString('netflix.com', (string) $request->input('logo_url'));
    }

    public function test_it_infers_service_name_from_website_when_name_is_blank(): void
    {
        $request = Request::create('/subscriptions', 'POST', [
            'website_url' => 'https://www.linear.app',
        ]);

        $this->normalizer->normalize($request);

        $this->assertSame('Linear', $request->input('name'));
    }
}
