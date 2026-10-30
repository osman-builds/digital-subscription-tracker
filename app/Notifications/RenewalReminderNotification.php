<?php

namespace App\Notifications;

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
        $mail = (new MailMessage)
            ->subject('SubShield Alert: Upcoming Subscription Renewals')
            ->greeting("Hello {$notifiable->name},")
            ->line('You have upcoming subscription renewals scheduled soon:');

        foreach ($this->subscriptions as $sub) {
            $days = $sub->daysUntilRenewal();
            $dueText = $days === 0 ? 'TODAY' : ($days === 1 ? 'tomorrow' : "in {$days} days");
            $mail->line("• **{$sub->name}** — RM " . number_format($sub->effectiveMyr(), 2) . " (Due {$dueText} on {$sub->next_billing_date->format('M d, Y')})");
        }

        return $mail
            ->action('Manage Subscriptions', url('/subscriptions'))
            ->line('Review your subscriptions to avoid unwanted recurring charges.');
    }
}