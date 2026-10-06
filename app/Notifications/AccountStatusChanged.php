<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountStatusChanged extends Notification
{
    public function __construct(
        public string $previousStatus,
        public string $currentStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('FNU Job Placement account status: '.ucfirst($this->currentStatus))
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An administrator has changed your FNU Job Placement account status from '
                .ucfirst($this->previousStatus).' to '.ucfirst($this->currentStatus).'.');

        if ($this->currentStatus === 'active') {
            $mail->line('Your account has been approved.');
            if ($notifiable->needsEmailVerification()) {
                $mail->line('Please verify your email address before logging in. Use the link sent during registration, or request a new verification email below.')
                    ->action('Verify your email', route('verification.notice'));
            } else {
                $mail->line($notifiable->role === 'employer'
                    ? 'You can now log in to manage job postings and student applications.'
                    : 'You can now log in to access your account and placement opportunities.')
                    ->action('Log in', route('account.login'));
            }
        } elseif ($this->currentStatus === 'pending') {
            $mail->line('Your account is pending administrator approval. You cannot log in until your account has been approved.');
            if ($notifiable->needsEmailVerification()) {
                $mail->line('You also need to verify your email address before logging in.')
                    ->action('Verify your email', route('verification.notice'));
            }
        } else {
            $mail->line('Your account has been blocked. You cannot log in. Please contact the Placement Officer for assistance.');
        }

        return $mail
            ->line('For questions about this change, contact the Placement Officer at '.config('mail.contact.address').'.')
            ->replyTo(config('mail.contact.address'), 'Placement Officer')
            ->salutation('FNU Job Placement Team');
    }
}
