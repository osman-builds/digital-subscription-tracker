<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRenewalReminders extends Command
{
    protected $signature   = 'subscriptions:remind {--days=3 : Days ahead to check for renewals}';
    protected $description = 'Log (and optionally notify) users about subscriptions renewing soon';

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

        // Log for audit trail
        foreach ($subscriptions as $sub) {
            Log::info('Renewal reminder', [
                'subscription' => $sub->name,
                'user_id'      => $sub->user_id,
                'amount_myr'   => $sub->effectiveMyr(),
                'due_date'     => $sub->next_billing_date->toDateString(),
                'days_left'    => $sub->daysUntilRenewal(),
            ]);
        }

        $this->info("Processed {$subscriptions->count()} upcoming renewal(s).");

        // -----------------------------------------------------------------------
        // TO ENABLE EMAIL NOTIFICATIONS:
        // 1. Configure MAIL_* in .env (e.g. Mailtrap for local testing)
        // 2. Create a RenewalReminderMail Mailable
        // 3. Uncomment and adapt:
        //
        // foreach ($subscriptions->groupBy('user_id') as $userId => $userSubs) {
        //     $user = $userSubs->first()->user;
        //     if ($user) {
        //         Mail::to($user->email)->send(new RenewalReminderMail($user, $userSubs));
        //     }
        // }
        // -----------------------------------------------------------------------

        return self::SUCCESS;
    }
}
