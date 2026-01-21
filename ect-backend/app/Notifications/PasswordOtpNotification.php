<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $otp,
        public readonly int $expiresMinutes
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your ECT Learn password reset code')
            ->line("Your OTP is: {$this->otp}")
            ->line("This code expires in {$this->expiresMinutes} minutes.")
            ->line('If you did not request this, you can ignore this email.');
    }
}
