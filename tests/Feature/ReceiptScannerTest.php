<?php

namespace Tests\Feature;

use App\Models\AiExtractionAudit;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReceiptScannerTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFIED_EMAIL = 'verified.scanner@gmail.com';

    private const GROQ_KEY = 'gsk_fake-groq-key-for-testing';

    private const GROQ_KEY_CONFIG = 'services.groq.key';

    private const VISION_MODEL = 'qwen/qwen3.8-27b';

    private const CHAT_PRIMARY = 'openai/gpt-oss-20b';

    private const CHAT_FALLBACK = 'openai/gpt-oss-120b';

    private const JSON_MIME = 'application/json';

    private const SCAN_ROUTE = 'subscriptions.scan_receipt';

    private const START_DATE = '2026-11-01';

    private const NEXT_DATE = '2026-12-01';

    private const SAMPLE_PDF_CONTENT = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\n(Netflix MYR 55.00 monthly)\nendobj\ntrailer\n<<>>\n%%EOF";

    private const SAMPLE_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aW1EAAAAASUVORK5CYII=';

    private const NETFLIX_NAME = 'Netflix';

    private const NETFLIX_URL = 'https://netflix.com';

    private const MONTHLY_CYCLE = 'monthly';

    private const AMOUNT_55 = '55.00';

    public function test_verified_user_can_scan_receipt_in_ram_and_save_verified_subscription(): void
    {
        config()->set(self::GROQ_KEY_CONFIG, self::GROQ_KEY);
        config()->set('services.groq.vision_model', self::VISION_MODEL);

        Category::create(['name' => 'Entertainment', 'color' => '#ef4444', 'user_id' => null]);
        $user = User::factory()->create(['email' => self::VERIFIED_EMAIL]);

        Http::fake([
            '*api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'name' => self::NETFLIX_NAME,
                            'amount' => 55.00,
                            'currency' => 'MYR',
                            'billing_cycle' => self::MONTHLY_CYCLE,
                            'start_date' => self::START_DATE,
                            'next_billing_date' => self::NEXT_DATE,
                            'is_trial' => false,
                            'website_url' => self::NETFLIX_URL,
                            'category_suggestion' => 'Entertainment',
                            'description' => 'Standard HD Plan',
                            'confidence_score' => 96,
                            'field_confidence' => ['name' => 99, 'amount' => 97],
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $pngBinary = base64_decode(self::SAMPLE_PNG_BASE64, true);
        $file = UploadedFile::fake()->createWithContent('netflix-receipt.png', (string) $pngBinary);
        $expectedSha = hash('sha256', (string) $pngBinary);

        $scanResponse = $this->actingAs($user)
            ->post(route(self::SCAN_ROUTE), ['receipt' => $file], ['Accept' => self::JSON_MIME]);

        $scanResponse->assertOk()
            ->assertJson([
                'duplicate_receipt' => false,
                'sandbox_simulated' => false,
                'input_type' => 'image',
                'input_source' => 'ai_image',
                'file_sha256' => $expectedSha,
                'model_used' => self::VISION_MODEL,
                'extracted' => [
                    'name' => self::NETFLIX_NAME,
                    'amount' => self::AMOUNT_55,
                    'currency' => 'MYR',
                    'billing_cycle' => self::MONTHLY_CYCLE,
                ],
            ]);

        $auditId = $scanResponse->json('audit_id');
        $this->assertDatabaseHas('ai_extraction_audits', [
            'id' => $auditId,
            'user_id' => $user->id,
            'file_sha256' => $expectedSha,
            'status' => 'pending',
        ]);

        // Confirm zero user receipt files saved in public or private storage
        $this->assertSame([], glob(storage_path('app/*netflix*')) ?: []);
        $this->assertSame([], glob(storage_path('app/public/*netflix*')) ?: []);

        // Verify Human-in-the-Loop form save links audit + logs billing event
        $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => self::NETFLIX_NAME,
            'website_url' => self::NETFLIX_URL,
            'amount' => self::AMOUNT_55,
            'currency' => 'MYR',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => self::START_DATE,
            'next_billing_date' => self::NEXT_DATE,
            'input_source' => 'ai_image',
            'ai_audit_id' => $auditId,
        ])->assertRedirect(route('subscriptions.index'));

        $sub = Subscription::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('ai_image', $sub->input_source);
        $this->assertDatabaseHas('ai_extraction_audits', [
            'id' => $auditId,
            'subscription_id' => $sub->id,
            'status' => 'applied_new',
        ]);
        $this->assertDatabaseHas('billing_events', [
            'subscription_id' => $sub->id,
            'source' => 'ai_image',
        ]);
    }

    public function test_duplicate_pdf_receipt_is_short_circuited_without_second_ai_api_call(): void
    {
        config()->set(self::GROQ_KEY_CONFIG, self::GROQ_KEY);
        $user = User::factory()->create(['email' => self::VERIFIED_EMAIL]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'name' => self::NETFLIX_NAME,
            'amount' => self::AMOUNT_55,
            'currency' => 'MYR',
            'amount_myr' => self::AMOUNT_55,
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => self::START_DATE,
            'next_billing_date' => self::NEXT_DATE,
        ]);

        AiExtractionAudit::create([
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'input_type' => 'pdf',
            'file_sha256' => hash('sha256', self::SAMPLE_PDF_CONTENT),
            'model_used' => self::VISION_MODEL,
            'confidence_score' => 95.0,
            'status' => 'applied_new',
            'verified_at' => now(),
        ]);

        Http::fake();

        $pdf = UploadedFile::fake()->createWithContent('invoice.pdf', self::SAMPLE_PDF_CONTENT);
        $response = $this->actingAs($user)
            ->post(route(self::SCAN_ROUTE), ['receipt' => $pdf], ['Accept' => self::JSON_MIME]);

        $response->assertOk()
            ->assertJson([
                'duplicate_receipt' => true,
                'existing_subscription_id' => $sub->id,
                'existing_subscription_name' => self::NETFLIX_NAME,
            ]);

        Http::assertNothingSent();
    }

    public function test_groq_503_high_demand_automatically_falls_back_to_secondary_model_and_strips_unsafe_urls(): void
    {
        config()->set(self::GROQ_KEY_CONFIG, self::GROQ_KEY);
        config()->set('services.groq.chat_model', self::CHAT_PRIMARY);
        config()->set('services.groq.chat_fallback_model', self::CHAT_FALLBACK);

        $user = User::factory()->create(['email' => self::VERIFIED_EMAIL]);

        Http::fake([
            '*api.groq.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'High demand']], 503)
                ->push([
                    'choices' => [[
                        'message' => [
                            'content' => json_encode([
                                'name' => 'Custom Cloud Lab',
                                'amount' => 19.99,
                                'currency' => 'USD',
                                'billing_cycle' => self::MONTHLY_CYCLE,
                                'website_url' => 'https://127.0.0.1/admin',
                                'cancellation_url' => 'javascript:alert(1)',
                                'confidence_score' => 88,
                            ]),
                        ],
                    ]],
                ], 200),
        ]);

        $pdf = UploadedFile::fake()->createWithContent('cloud.pdf', self::SAMPLE_PDF_CONTENT);
        $response = $this->actingAs($user)
            ->post(route(self::SCAN_ROUTE), ['receipt' => $pdf], ['Accept' => self::JSON_MIME]);

        $response->assertOk()
            ->assertJson([
                'model_used' => self::CHAT_FALLBACK,
                'extracted' => [
                    'name' => 'Custom Cloud Lab',
                    'website_url' => null,
                    'cancellation_url' => null,
                ],
            ]);

        Http::assertSentCount(2);
    }

    public function test_scanning_matching_existing_subscription_and_applying_updates_ledger_and_enforces_anti_idor(): void
    {
        config()->set(self::GROQ_KEY_CONFIG, self::GROQ_KEY);
        $user = User::factory()->create(['email' => self::VERIFIED_EMAIL]);
        $otherUser = User::factory()->create(['email' => 'intruder@gmail.com']);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'name' => self::NETFLIX_NAME,
            'website_url' => self::NETFLIX_URL,
            'amount' => self::AMOUNT_55,
            'currency' => 'MYR',
            'amount_myr' => self::AMOUNT_55,
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => '2026-10-01',
            'next_billing_date' => '2026-11-30',
        ]);

        Http::fake([
            '*api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'name' => self::NETFLIX_NAME,
                            'amount' => 63.00,
                            'currency' => 'MYR',
                            'billing_cycle' => self::MONTHLY_CYCLE,
                            'next_billing_date' => '2026-12-30',
                            'website_url' => self::NETFLIX_URL,
                            'confidence_score' => 94,
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $pdf = UploadedFile::fake()->createWithContent('netflix-hike.pdf', self::SAMPLE_PDF_CONTENT);
        $scan = $this->actingAs($user)
            ->post(route(self::SCAN_ROUTE), ['receipt' => $pdf], ['Accept' => self::JSON_MIME]);

        $scan->assertOk()
            ->assertJsonPath('matched_subscription.id', $sub->id)
            ->assertJsonPath('matched_subscription.price_delta', 8);

        $auditId = $scan->json('audit_id');

        $payload = [
            'amount' => '63.00',
            'currency' => 'MYR',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'next_billing_date' => '2026-12-30',
            'ai_audit_id' => $auditId,
            'input_source' => 'ai_pdf',
        ];

        $this->actingAs($otherUser)
            ->post(route('subscriptions.apply_receipt', $sub), $payload)
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('subscriptions.apply_receipt', $sub), $payload)
            ->assertRedirect(route('subscriptions.show', $sub));

        $sub->refresh();
        $this->assertSame('63.00', (string) $sub->amount);
        $this->assertSame('ai_pdf', $sub->input_source);

        $this->assertDatabaseHas('subscription_price_histories', [
            'subscription_id' => $sub->id,
            'old_amount' => 55.00,
            'new_amount' => 63.00,
            'detected_via' => 'ai_receipt',
        ]);
        $this->assertDatabaseHas('billing_events', [
            'subscription_id' => $sub->id,
            'source' => 'ai_receipt',
        ]);
        $this->assertDatabaseHas('ai_extraction_audits', [
            'id' => $auditId,
            'status' => 'applied_update',
        ]);
    }

    public function test_disguised_non_image_file_is_rejected_and_unverified_and_sandbox_are_guarded(): void
    {
        config()->set(self::GROQ_KEY_CONFIG, self::GROQ_KEY);
        Http::fake();

        $verified = User::factory()->create(['email' => self::VERIFIED_EMAIL]);
        $disguisedHtmlAsJpg = UploadedFile::fake()->createWithContent(
            'disguised.jpg',
            '<!DOCTYPE html><html><body>Not an actual image binary</body></html>'
        );

        $this->actingAs($verified)
            ->post(route(self::SCAN_ROUTE), ['receipt' => $disguisedHtmlAsJpg], ['Accept' => self::JSON_MIME])
            ->assertStatus(422);

        $unverified = User::factory()->unverified()->create(['email' => 'pending@example.com']);
        $pdf = UploadedFile::fake()->createWithContent('valid.pdf', self::SAMPLE_PDF_CONTENT);

        $this->actingAs($unverified)
            ->post(route(self::SCAN_ROUTE), ['receipt' => $pdf], ['Accept' => self::JSON_MIME])
            ->assertForbidden();

        $this->post('/logout');

        // Sandbox simulation runs purely in memory with zero external HTTP calls and zero DB writes
        $this->post(route('demo.start'))->assertRedirect(route('dashboard'));
        $sandboxResponse = $this->post(route(self::SCAN_ROUTE), [], ['Accept' => self::JSON_MIME]);

        $sandboxResponse->assertOk()
            ->assertJson([
                'sandbox_simulated' => true,
                'model_used' => 'sandbox-simulator',
            ]);

        Http::assertNothingSent();
        $this->assertDatabaseCount('ai_extraction_audits', 0);
        $this->assertDatabaseCount('billing_events', 0);
    }
}
