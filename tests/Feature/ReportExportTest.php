<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_arranged_subscriptions_csv_with_summary_and_category_breakdown(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'Streaming', 'color' => '#ef4444', 'user_id' => $user->id]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'name' => 'Netflix Deluxe',
            'amount' => '19.99',
            'currency' => 'USD',
            'amount_myr' => '90.00',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonths(2)->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
            'notes' => '4K Plan',
        ]);

        $response = $this->actingAs($user)->get(route('subscriptions.export'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('SubShield - Subscription Financial Portfolio Report', $content);
        $this->assertStringContainsString('Service Name', $content);
        $this->assertStringContainsString('Netflix Deluxe', $content);
        $this->assertStringContainsString('Streaming', $content);
        $this->assertStringContainsString('TOTAL', $content);
        $this->assertStringContainsString('CATEGORY SPEND BREAKDOWN', $content);
    }

    public function test_user_can_view_subscriptions_pdf_report(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'Cloud', 'color' => '#06b6d4', 'user_id' => $user->id]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'name' => 'iCloud Storage',
            'amount' => '44.90',
            'currency' => 'MYR',
            'amount_myr' => '44.90',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonths(3)->toDateString(),
            'next_billing_date' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('subscriptions.export_pdf'));
        $response->assertOk();
        $response->assertSee('Subscription Portfolio Report');
        $response->assertSee('iCloud Storage');
        $response->assertSee('Print / Save as PDF');
        $response->assertSee('Category Spend Allocation');
    }

    public function test_user_can_export_arranged_expenditure_csv(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'Dev', 'color' => '#6366f1', 'user_id' => $user->id]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'name' => 'GitHub Copilot',
            'amount' => '10.00',
            'currency' => 'USD',
            'amount_myr' => '44.50',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonths(4)->toDateString(),
            'next_billing_date' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('expenditure.export_csv', ['year' => now()->year]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('SubShield - Annual Expenditure Report', $content);
        $this->assertStringContainsString('MONTHLY EXPENDITURE BREAKDOWN', $content);
        $this->assertStringContainsString('CATEGORY EXPENDITURE BREAKDOWN', $content);
        $this->assertStringContainsString('ANNUAL SUBSCRIPTION COSTS', $content);
        $this->assertStringContainsString('GitHub Copilot', $content);
    }

    public function test_user_can_view_expenditure_pdf_report(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('expenditure.export_pdf', ['year' => now()->year]));

        $response->assertOk();
        $response->assertSee('Annual Expenditure & Billing Audit');
        $response->assertSee('Monthly Spend Breakdown');
        $response->assertSee('Print / Save as PDF');
    }

    public function test_sandbox_user_can_export_csv_and_pdf_without_database_records(): void
    {
        $this->post(route('demo.start'))->assertRedirect(route('dashboard'));

        $subCsv = $this->get(route('subscriptions.export'));
        $subCsv->assertOk();
        $this->assertStringContainsString('Netflix', $subCsv->streamedContent());

        $subPdf = $this->get(route('subscriptions.export_pdf'));
        $subPdf->assertOk();
        $subPdf->assertSee('Netflix');
        $subPdf->assertSee('ChatGPT Plus');

        $expCsv = $this->get(route('expenditure.export_csv'));
        $expCsv->assertOk();
        $this->assertStringContainsString('MONTHLY EXPENDITURE BREAKDOWN', $expCsv->streamedContent());

        $expPdf = $this->get(route('expenditure.export_pdf'));
        $expPdf->assertOk();
        $expPdf->assertSee('Annual Expenditure & Billing Audit');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('subscriptions', 0);
    }
}
