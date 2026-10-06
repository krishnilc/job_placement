<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification
{
    public function __construct(
        public string $jobTitle,
        public string $companyName,
        public string $previousStatus,
        public string $currentStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = match ($this->currentStatus) {
            'Submitted' => 'Your application is marked as submitted and is awaiting review.',
            'Under Review' => 'Your application is being reviewed. You will be notified of further status changes.',
            'Shortlisted' => 'You have been shortlisted. Please watch for further communication about the next steps.',
            'Interview Scheduled' => 'Your application is marked as Interview Scheduled. Please contact the employer or Placement Officer to confirm the interview date, time, and arrangements.',
            'Interview Completed' => 'Your interview is marked as completed. Please await further communication about the outcome.',
            'Accepted' => 'Your application has been accepted. Please contact the employer or Placement Officer to confirm the next steps.',
            'Placed' => 'Congratulations! Your application is marked as placed. Please confirm your placement arrangements with the employer or Placement Officer.',
            'Rejected' => 'Unfortunately, your application has not been successful. You can continue exploring other placement opportunities.',
            'Withdrawn' => 'Your application is marked as withdrawn. If this was unexpected, please contact the Placement Officer.',
            default => 'Please review your applications for the latest status and contact the Placement Officer if you need assistance.',
        };

        return (new MailMessage)
            ->subject('FNU Job Placement application status: '.$this->currentStatus)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your application for '.$this->jobTitle.' at '.$this->companyName.' has changed from '
                .$this->previousStatus.' to '.$this->currentStatus.'.')
            ->line($message)
            ->action('View my applications', route('account.myJobApplications'))
            ->line('For questions about this update, contact the Placement Officer at '.config('mail.contact.address').'.')
            ->replyTo(config('mail.contact.address'), 'Placement Officer')
            ->salutation('FNU Job Placement Team');
    }
}
