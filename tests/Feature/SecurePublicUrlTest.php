<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\SecurePublicUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SecurePublicUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_secure_public_url_accepts_valid_existing_https_domains(): void
    {
        $rule = new SecurePublicUrl;

        $validUrls = [
            'https://netflix.com',
            'https://spotify.com/premium',
            'https://calm.com',
            'https://aia.com.my',
            'https://example.com/billing',
        ];

        foreach ($validUrls as $url) {
            $validator = Validator::make(['url' => $url], ['url' => [$rule]]);
            $this->assertTrue($validator->passes(), "Expected {$url} to pass SecurePublicUrl validation.");
        }
    }

    public function test_secure_public_url_rejects_insecure_or_malicious_schemes(): void
    {
        $rule = new SecurePublicUrl;

        $insecureUrls = [
            'http://netflix.com',
            'javascript:alert(1)',
            'data:text/html;base64,PHNjcmlwdD4=',
            'file:///etc/passwd',
            'ftp://ftp.example.com',
        ];

        foreach ($insecureUrls as $url) {
            $validator = Validator::make(['url' => $url], ['url' => [$rule]]);
            $this->assertTrue($validator->fails(), "Expected {$url} to fail SecurePublicUrl validation.");
        }
    }

    public function test_secure_public_url_rejects_local_private_and_internal_addresses(): void
    {
        $rule = new SecurePublicUrl;

        $internalUrls = [
            'https://localhost',
            'https://127.0.0.1',
            'https://192.168.1.1',
            'https://10.0.0.1',
            'https://169.254.169.254',
            'https://service.local',
            'https://company.internal',
            'https://mock.test',
        ];

        foreach ($internalUrls as $url) {
            $validator = Validator::make(['url' => $url], ['url' => [$rule]]);
            $this->assertTrue($validator->fails(), "Expected {$url} to be rejected as a local/private address.");
        }
    }

    public function test_secure_public_url_rejects_non_existent_unverified_domains(): void
    {
        $rule = new SecurePublicUrl;

        $fakeUrl = 'https://fake-unverified-nonexistent-domain-xyz9876543210.com';
        $validator = Validator::make(['url' => $fakeUrl], ['url' => [$rule]]);

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('active, verified public domain', (string) $validator->errors()->first('url'));
    }

    public function test_subscription_creation_rejects_unverified_non_existent_website_domains(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => 'Ghost Service',
            'amount' => '10.00',
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
            'website_url' => 'https://fake-unverified-nonexistent-domain-xyz9876543210.com',
        ]);

        $response->assertSessionHasErrors(['website_url']);
    }
}
