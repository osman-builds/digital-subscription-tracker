<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $otp) {}

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
            ->subject("SubShield: Your Verification Code is {$this->otp}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for registering with SubShield! Please enter the 6-digit verification code below to verify your email address:')
            ->line("# **{$this->otp}**")
            ->line('This verification code is valid for 10 minutes. For your security, do not share this code with anyone.')
            ->salutation('Regards, The SubShield Team');
    }
}
