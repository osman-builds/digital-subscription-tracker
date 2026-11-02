<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to SubShield — Your Digital Subscription Tracker')
            ->greeting("Welcome aboard, {$notifiable->name}!")
            ->line('Your email has been successfully verified. You are now ready to take full control of your recurring subscriptions.')
            ->line('Here is what you can do with SubShield:')
            ->line('• **MYR Normalisation**: Enter subscriptions in USD, EUR, SGD, GBP, or 15+ currencies — SubShield converts them to Malaysian Ringgit automatically using live FX rates.')
            ->line('• **Proactive Renewal Alerts**: Receive timely notifications 7 days and 3 days before renewal deadlines so you never get surprised.')
            ->line('• **Expenditure Insights**: Track your monthly and annual spending patterns with detailed category breakdowns.')
            ->action('Go to Dashboard', url('/dashboard'))
            ->line('Start adding your active subscriptions today to keep your budget protected.')
            ->salutation('Warm regards, The SubShield Team');
    }
}
