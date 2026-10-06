<?php

namespace App\Services;

use App\Mail\ApplicationSubmissionConfirmation;
use App\Mail\JobNotificationEmail;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ApplicationSubmissionNotifier
{
    /**
     * @return array{job_poster: bool, student: bool}
     */
    public function send(Job $job, User $applicant): array
    {
        $poster = $job->user()->firstOrFail();
        $sent = ['job_poster' => false, 'student' => false];

        try {
            Mail::to($poster->email)->send(new JobNotificationEmail($poster, $applicant, $job));
            $sent['job_poster'] = true;
        } catch (TransportExceptionInterface $exception) {
            report($exception);
        }

        try {
            Mail::to($applicant->email)->send(new ApplicationSubmissionConfirmation($applicant, $job));
            $sent['student'] = true;
        } catch (TransportExceptionInterface $exception) {
            report($exception);
        }

        return $sent;
    }
}
