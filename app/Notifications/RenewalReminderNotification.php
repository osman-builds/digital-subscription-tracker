<?php

namespace App\Notifications;

use App\Services\FxRateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RenewalReminderNotification extends Notification
{
    use Queueable;

    public Collection $subscriptions;

    public function __construct(Collection $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $settings = method_exists($notifiable, 'getSettings') ? $notifiable->getSettings() : null;
        $baseCurrency = $settings?->base_currency ?? 'MYR';
        $fx = app(FxRateService::class);
        $symbol = $fx->symbol($baseCurrency);

        $mail = (new MailMessage)
            ->subject('SubShield Alert: Upcoming Subscription Renewals')
            ->greeting("Hello {$notifiable->name},")
            ->line('You have upcoming subscription renewals scheduled soon:');

        foreach ($this->subscriptions as $sub) {
            $days = $sub->daysUntilRenewal();
            $dueText = match ($days) {
                0 => 'TODAY',
                1 => 'tomorrow',
                default => "in {$days} days",
            };

            $amountInBase = number_format($sub->effectiveAmountIn($baseCurrency), 2);

            $originalContext = '';
            if ($sub->currency && strtoupper($sub->currency) !== strtoupper($baseCurrency)) {
                $subSymbol = $fx->symbol($sub->currency);
                $originalContext = " ({$subSymbol} ".number_format((float) $sub->amount, 2)." {$sub->currency})";
            }

            $mail->line("• **{$sub->name}** — {$symbol} {$amountInBase}{$originalContext} (Due {$dueText} on {$sub->next_billing_date->format('M d, Y')})");
        }

        return $mail
            ->action('Manage Subscriptions', url('/subscriptions'))
            ->line('Review your subscriptions to avoid unwanted recurring charges.');
    }
}
