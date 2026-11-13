<?php

namespace App\Console\Commands;

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
                $this->dispatchUserReminder($user, $userSubs);
                $sentCount++;
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
                    $sub->user?->email ?? '(no user)',
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

    private function dispatchUserReminder(User $user, Collection $userSubs): void
    {
        $user->notify(new RenewalReminderNotification($userSubs));
        $this->line("Sent reminder email to: {$user->email} ({$userSubs->count()} subscription(s))");

        foreach ($userSubs as $sub) {
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
}
