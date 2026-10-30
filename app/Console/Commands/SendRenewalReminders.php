<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\RenewalReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRenewalReminders extends Command
{
    protected $signature   = 'subscriptions:remind {--days=3 : Days ahead to check for renewals}';
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

        $this->table(
            ['User', 'Subscription', 'Amount (MYR)', 'Next Billing', 'Days Left'],
            $subscriptions->map(function (Subscription $sub) {
                $days = $sub->daysUntilRenewal();
                return [
                    $sub->user?->email ?? '(no user)',
                    $sub->name,
                    'RM ' . number_format($sub->effectiveMyr(), 2),
                    $sub->next_billing_date->format('Y-m-d'),
                    $days === 0 ? 'TODAY' : ($days === 1 ? '1 day' : "{$days} days"),
                ];
            })->toArray()
        );

        // Group by user to send one combined email per user
        $grouped = $subscriptions->groupBy('user_id');
        $sentCount = 0;

        foreach ($grouped as $userId => $userSubs) {
            $user = $userSubs->first()->user;

            if ($user && $user->email) {
                // Dispatch real email via Brevo relay
                $user->notify(new RenewalReminderNotification($userSubs));
                $this->line("Sent reminder email to: {$user->email} ({$userSubs->count()} subscription(s))");
                $sentCount++;
            }

            // Audit log
            foreach ($userSubs as $sub) {
                Log::info('Renewal reminder sent', [
                    'subscription' => $sub->name,
                    'user_id'      => $sub->user_id,
                    'amount_myr'   => $sub->effectiveMyr(),
                    'due_date'     => $sub->next_billing_date->toDateString(),
                    'days_left'    => $sub->daysUntilRenewal(),
                ]);
            }
        }

        $this->info("Completed. Successfully notified {$sentCount} user(s).");

        return self::SUCCESS;
    }
}