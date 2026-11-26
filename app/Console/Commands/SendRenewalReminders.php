<?php

namespace App\Console\Commands;

use App\Models\InsightAlert;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\RenewalReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendRenewalReminders extends Command
{
    protected $signature = 'subscriptions:remind {--days=3 : Days ahead to check for renewals}';

    protected $description = 'Log and notify users about subscriptions renewing soon';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Checking for subscriptions renewing within {$days} day(s)...");

        $subscriptions = Subscription::with(['user', 'category'])
            ->active()
            ->dueSoon($days)
            ->orderBy('next_billing_date')
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No upcoming renewals found.');

            return self::SUCCESS;
        }

        $this->renderSummaryTable($subscriptions);

        $sentCount = 0;
        foreach ($subscriptions->groupBy('user_id') as $userSubs) {
            $user = $userSubs->first()->user;

            if ($this->shouldNotifyUser($user, $days)) {
                $pendingSubs = $this->filterUnsentSubscriptions($user, $userSubs, $days);
                if ($pendingSubs->isNotEmpty()) {
                    $this->dispatchUserReminder($user, $pendingSubs, $days);
                    $sentCount++;
                }
            }
        }

        $this->info("Completed. Successfully notified {$sentCount} user(s).");

        return self::SUCCESS;
    }

    private function renderSummaryTable(Collection $subscriptions): void
    {
        $this->table(
            ['User', 'Subscription', 'Amount (MYR)', 'Next Billing', 'Days Left'],
            $subscriptions->map(function (Subscription $sub) {
                $days = $sub->daysUntilRenewal();
                $daysLeftText = match ($days) {
                    0 => 'TODAY',
                    1 => '1 day',
                    default => "{$days} days",
                };

                return [
                    $sub->user !== null ? $sub->user->email : '(no user)',
                    $sub->name,
                    'RM '.number_format($sub->effectiveMyr(), 2),
                    $sub->next_billing_date->format('Y-m-d'),
                    $daysLeftText,
                ];
            })->toArray()
        );
    }

    private function shouldNotifyUser(?User $user, int $days): bool
    {
        if (! $user || ! $user->email) {
            return false;
        }

        if ($this->isDeliverabilitySuppressed($user)) {
            return false;
        }

        return $this->isAlertEnabledForDays($user, $days);
    }

    private function isDeliverabilitySuppressed(User $user): bool
    {
        if (! $user->hasRealVerifiedEmail()) {
            $this->line("Skipping unverified/demo user {$user->email} to preserve mail deliverability.");

            return true;
        }

        return false;
    }

    private function isAlertEnabledForDays(User $user, int $days): bool
    {
        $settings = $user->getSettings();
        $enabled = $days <= 3 ? (bool) $settings->alert_3_days : (bool) $settings->alert_7_days;

        if (! $enabled) {
            $this->line("Skipping {$user->email}: renewal alerts for {$days} day(s) are disabled in user preferences.");
        }

        return $enabled;
    }

    /**
     * Exclude subscriptions that already had an alert emailed for this billing date and window.
     */
    private function filterUnsentSubscriptions(User $user, Collection $userSubs, int $days): Collection
    {
        $dedupeKeys = $userSubs
            ->map(fn (Subscription $sub): string => $this->reminderDedupeKey($sub, $days))
            ->values()
            ->all();

        $alreadyEmailedKeys = InsightAlert::where('user_id', $user->id)
            ->whereIn('dedupe_key', $dedupeKeys)
            ->whereNotNull('emailed_at')
            ->pluck('dedupe_key')
            ->all();

        return $userSubs
            ->reject(fn (Subscription $sub): bool => in_array($this->reminderDedupeKey($sub, $days), $alreadyEmailedKeys, true))
            ->values();
    }

    private function dispatchUserReminder(User $user, Collection $userSubs, int $days): void
    {
        $user->notify(new RenewalReminderNotification($userSubs));
        $this->line("Sent reminder email to: {$user->email} ({$userSubs->count()} subscription(s))");

        foreach ($userSubs as $sub) {
            InsightAlert::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'dedupe_key' => $this->reminderDedupeKey($sub, $days),
                ],
                [
                    'subscription_id' => $sub->id,
                    'alert_type' => 'renewal_due',
                    'severity' => $days <= 3 ? 'warning' : 'info',
                    'title' => "Upcoming Renewal: {$sub->name}",
                    'message' => "{$sub->name} ({$sub->currency} ".number_format((float) $sub->amount, 2).") renews on {$sub->next_billing_date->format('M d, Y')}.",
                    'is_read' => false,
                    'dismissed_at' => null,
                    'emailed_at' => now(),
                ]
            );

            Log::info('Renewal reminder sent', [
                'subscription' => $sub->name,
                'user_id' => $sub->user_id,
                'amount' => $sub->amount,
                'currency' => $sub->currency,
                'amount_myr' => $sub->effectiveMyr(),
                'due_date' => $sub->next_billing_date->toDateString(),
                'days_left' => $sub->daysUntilRenewal(),
            ]);
        }
    }

    private function reminderDedupeKey(Subscription $sub, int $days): string
    {
        $dueDate = $sub->next_billing_date?->toDateString() ?? now()->toDateString();

        return "renewal_due:{$sub->id}:{$dueDate}:{$days}d";
    }
}
