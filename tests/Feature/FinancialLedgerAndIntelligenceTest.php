<?php

namespace Tests\Feature;

use App\Models\AiExtractionAudit;
use App\Models\BillingEvent;
use App\Models\Category;
use App\Models\InsightAlert;
use App\Models\Subscription;
use App\Models\SubscriptionPriceHistory;
use App\Models\User;
use App\Notifications\RenewalReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FinancialLedgerAndIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    private const ACCOUNT_EMAIL = 'architect.user@gmail.com';

    private const ACCOUNT_PASSWORD = 'StrongPassword123!';

    private const SERVICE_NAME = 'Netflix';

    private const SERVICE_URL = 'https://netflix.com';

    private const MONTHLY_CYCLE = 'monthly';

    private const PRIMARY_MODEL = 'gemini-flash-latest';

    public function test_creating_and_renewing_subscription_records_fx_snapshotted_billing_ledger(): void
    {
        $user = User::factory()->create(['email' => self::ACCOUNT_EMAIL]);
        $user->settings()->create(['base_currency' => 'MYR', 'budget_alert_percent' => 80]);

        $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => self::SERVICE_NAME,
            'website_url' => self::SERVICE_URL,
            'amount' => '15.00',
            'currency' => 'USD',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addDays(2)->toDateString(),
        ])->assertRedirect(route('subscriptions.index'));

        $sub = Subscription::where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('billing_events', [
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'currency' => 'USD',
            'base_currency' => 'MYR',
            'source' => 'manual',
        ]);

        $this->actingAs($user)->post(route('subscriptions.renew', $sub))->assertRedirect();

        $this->assertDatabaseHas('billing_events', [
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'source' => 'auto_rollover',
        ]);

        $this->assertSame(2, BillingEvent::where('subscription_id', $sub->id)->count());
    }

    public function test_updating_subscription_price_logs_price_history_and_raises_price_creep_alert(): void
    {
        $user = User::factory()->create(['email' => self::ACCOUNT_EMAIL]);
        $sub = Subscription::create([
            'user_id' => $user->id,
            'name' => self::SERVICE_NAME,
            'website_url' => self::SERVICE_URL,
            'amount' => '50.00',
            'currency' => 'MYR',
            'amount_myr' => '50.00',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->actingAs($user)->put(route('subscriptions.update', $sub), [
            'name' => self::SERVICE_NAME,
            'website_url' => self::SERVICE_URL,
            'amount' => '60.00',
            'currency' => 'MYR',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => $sub->start_date->toDateString(),
            'next_billing_date' => $sub->next_billing_date->toDateString(),
        ])->assertRedirect(route('subscriptions.index'));

        $this->assertDatabaseHas('subscription_price_histories', [
            'subscription_id' => $sub->id,
            'old_amount' => 50.00,
            'new_amount' => 60.00,
            'change_amount' => 10.00,
            'change_percent' => 20.00,
            'detected_via' => 'manual_edit',
        ]);

        $this->assertDatabaseHas('insight_alerts', [
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'alert_type' => 'price_creep',
            'severity' => 'critical',
        ]);

        $this->actingAs($user)->get(route('subscriptions.show', $sub))
            ->assertOk()
            ->assertSee('Price Change History')
            ->assertSee('+20.0%');
    }

    public function test_dashboard_and_expenditure_surface_budget_overlaps_trial_shock_and_spike_forecast(): void
    {
        $user = User::factory()->create(['email' => self::ACCOUNT_EMAIL]);

        $this->actingAs($user)->patch(route('settings.preferences'), [
            'base_currency' => 'MYR',
            'monthly_budget' => '100.00',
            'budget_alert_percent' => '80',
            'alert_7_days' => '1',
            'alert_3_days' => '1',
        ])->assertSessionHasNoErrors();

        $cat = Category::create(['name' => 'Streaming', 'color' => '#6366f1', 'user_id' => $user->id]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'name' => 'Plan Alpha',
            'amount' => '65.00',
            'currency' => 'MYR',
            'amount_myr' => '65.00',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDays(4)->toDateString(),
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'name' => 'Trial Beta',
            'amount' => '45.00',
            'currency' => 'MYR',
            'amount_myr' => '45.00',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'is_trial' => true,
            'trial_ends_at' => now()->addDays(2)->toDateString(),
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addDays(2)->toDateString(),
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'name' => 'Annual Cloud Vault',
            'amount' => '600.00',
            'currency' => 'MYR',
            'amount_myr' => '600.00',
            'billing_cycle' => 'annual',
            'status' => 'active',
            'start_date' => now()->subMonths(6)->toDateString(),
            'next_billing_date' => now()->addMonths(4)->startOfMonth()->toDateString(),
        ]);

        $dashboard = $this->actingAs($user)->get(route('dashboard'));
        $dashboard->assertOk()
            ->assertSee('Optimization Score')
            ->assertSee('Proactive Financial Insights')
            ->assertSee('Monthly Budget Exceeded')
            ->assertSee('Category Overlap: Streaming');

        $expenditure = $this->actingAs($user)->get(route('expenditure.index'));
        $expenditure->assertOk()
            ->assertSee('12-Month Cashflow Spike Forecast')
            ->assertSee('Price Creep Tracker')
            ->assertSee('Recent Billing Ledger (FX Snapshots)');
    }

    public function test_insight_dismissal_prevents_resurrection_and_enforces_anti_idor(): void
    {
        $owner = User::factory()->create(['email' => self::ACCOUNT_EMAIL]);
        $otherUser = User::factory()->create(['email' => 'other.user@gmail.com']);

        $owner->settings()->create(['base_currency' => 'MYR', 'monthly_budget' => 50.00]);
        Subscription::create([
            'user_id' => $owner->id,
            'name' => 'Expensive Plan',
            'amount' => '120.00',
            'currency' => 'MYR',
            'amount_myr' => '120.00',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $alert = InsightAlert::where('user_id', $owner->id)->where('alert_type', 'budget_cap')->firstOrFail();

        $this->actingAs($otherUser)->post(route('insights.dismiss', $alert))->assertForbidden();
        $this->actingAs($otherUser)->post(route('insights.read', $alert))->assertForbidden();

        $this->actingAs($owner)->post(route('insights.read', $alert))->assertRedirect();
        $this->assertTrue($alert->fresh()->is_read);

        $this->actingAs($owner)->post(route('insights.dismiss', $alert))->assertRedirect();
        $this->assertNotNull($alert->fresh()->dismissed_at);

        // Reloading dashboard must not resurrect the dismissed monthly alert
        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $this->assertNotNull($alert->fresh()->dismissed_at);
    }

    public function test_renewal_reminder_command_deduplicates_already_emailed_windows(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => self::ACCOUNT_EMAIL, 'email_verified_at' => now()]);
        $user->settings()->create(['base_currency' => 'MYR', 'alert_3_days' => true, 'alert_7_days' => true]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'name' => 'GitHub Copilot',
            'amount' => '10.00',
            'currency' => 'USD',
            'amount_myr' => '44.70',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDay()->toDateString(),
        ]);

        $this->artisan('subscriptions:remind --days=3')->assertSuccessful();
        $this->artisan('subscriptions:remind --days=3')->assertSuccessful();

        Notification::assertSentToTimes($user, RenewalReminderNotification::class, 1);
        $this->assertDatabaseHas('insight_alerts', [
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'alert_type' => 'renewal_due',
        ]);
    }

    public function test_gdpr_account_deletion_cascades_across_ledger_history_insights_and_audits(): void
    {
        $user = User::factory()->create([
            'email' => self::ACCOUNT_EMAIL,
            'password' => Hash::make(self::ACCOUNT_PASSWORD),
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'name' => self::SERVICE_NAME,
            'amount' => '55.00',
            'currency' => 'MYR',
            'amount_myr' => '55.00',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        BillingEvent::create([
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'amount' => 55.00,
            'currency' => 'MYR',
            'fx_rate_snapshot' => 1.0,
            'amount_base' => 55.00,
            'base_currency' => 'MYR',
            'billing_cycle' => self::MONTHLY_CYCLE,
            'billed_at' => now()->toDateString(),
            'source' => 'manual',
        ]);

        SubscriptionPriceHistory::create([
            'subscription_id' => $sub->id,
            'old_amount' => 45.00,
            'new_amount' => 55.00,
            'currency' => 'MYR',
            'change_amount' => 10.00,
            'change_percent' => 22.22,
            'detected_via' => 'manual_edit',
            'recorded_at' => now(),
        ]);

        InsightAlert::create([
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'alert_type' => 'price_creep',
            'severity' => 'warning',
            'title' => 'Test Alert',
            'message' => 'Test message',
            'dedupe_key' => 'test:delete',
        ]);

        AiExtractionAudit::create([
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'input_type' => 'pdf',
            'file_sha256' => hash('sha256', 'gdpr-test'),
            'model_used' => self::PRIMARY_MODEL,
            'confidence_score' => 92.0,
            'status' => 'applied_new',
        ]);

        $this->actingAs($user)
            ->delete(route('settings.delete_account'), ['password' => self::ACCOUNT_PASSWORD])
            ->assertRedirect('/login');

        $this->assertDatabaseCount('billing_events', 0);
        $this->assertDatabaseCount('subscription_price_histories', 0);
        $this->assertDatabaseCount('insight_alerts', 0);
        $this->assertDatabaseCount('ai_extraction_audits', 0);
    }

    public function test_prunable_models_purge_90_day_old_records_only(): void
    {
        $user = User::factory()->create(['email' => self::ACCOUNT_EMAIL]);

        $oldDismissed = InsightAlert::create([
            'user_id' => $user->id,
            'alert_type' => 'overlap',
            'severity' => 'info',
            'title' => 'Old Dismissed',
            'message' => 'Should be pruned',
            'dedupe_key' => 'old-dismissed',
            'is_read' => true,
            'dismissed_at' => now()->subDays(95),
        ]);
        $oldDismissed->forceFill(['created_at' => now()->subDays(95)])->saveQuietly();

        $oldUnreadActive = InsightAlert::create([
            'user_id' => $user->id,
            'alert_type' => 'budget_cap',
            'severity' => 'warning',
            'title' => 'Old Unread Active',
            'message' => 'Should remain active',
            'dedupe_key' => 'old-unread',
            'is_read' => false,
            'dismissed_at' => null,
        ]);
        $oldUnreadActive->forceFill(['created_at' => now()->subDays(95)])->saveQuietly();

        $oldAudit = AiExtractionAudit::create([
            'user_id' => $user->id,
            'input_type' => 'image',
            'file_sha256' => hash('sha256', 'old-audit'),
            'model_used' => self::PRIMARY_MODEL,
            'confidence_score' => 90.0,
            'status' => 'pending',
        ]);
        $oldAudit->forceFill(['created_at' => now()->subDays(95)])->saveQuietly();

        $freshAudit = AiExtractionAudit::create([
            'user_id' => $user->id,
            'input_type' => 'pdf',
            'file_sha256' => hash('sha256', 'fresh-audit'),
            'model_used' => self::PRIMARY_MODEL,
            'confidence_score' => 95.0,
            'status' => 'pending',
        ]);

        $this->artisan('model:prune')->assertSuccessful();

        $this->assertDatabaseMissing('insight_alerts', ['id' => $oldDismissed->id]);
        $this->assertDatabaseHas('insight_alerts', ['id' => $oldUnreadActive->id]);
        $this->assertDatabaseMissing('ai_extraction_audits', ['id' => $oldAudit->id]);
        $this->assertDatabaseHas('ai_extraction_audits', ['id' => $freshAudit->id]);
    }
}
