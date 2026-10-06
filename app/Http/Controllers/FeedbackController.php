<?php

namespace App\Http\Controllers;

use App\Mail\FeedbackSubmitted;
use App\Models\Feedback;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class FeedbackController extends Controller
{
    /**
     * Store feedback submitted by either the employer (about the placed student)
     * or the student (about the company/employer). The acting role is derived
     * from the authenticated user's relationship to the job application, and
     * feedback is only allowed once the application has been marked "Placed".
     * Feedback is never shown back to employers or students - only admins and
     * super admins can view submitted feedback.
     */
    public function store(Request $request, JobApplication $application)
    {
        $user = $request->user();

        if ($user->role === 'employer' && (int) $application->employer_id === $user->id) {
            $feedbackType = Feedback::TYPE_EMPLOYER_TO_STUDENT;
            $givenTo = $application->user_id;
        } elseif ($user->id === $application->user_id) {
            $feedbackType = Feedback::TYPE_STUDENT_TO_EMPLOYER;
            $givenTo = $application->employer_id;
        } else {
            abort(403, 'You do not have permission to submit feedback for this application.');
        }

        if (!$application->isPlaced()) {
            return back()->with('error', 'Feedback can only be submitted once the application status is "Placed".');
        }

        $alreadySubmitted = Feedback::where('job_application_id', $application->id)
            ->where('feedback_type', $feedbackType)
            ->exists();

        if ($alreadySubmitted) {
            return back()->with('error', 'You have already submitted feedback for this application.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        $feedback = Feedback::create([
            'job_application_id' => $application->id,
            'given_by' => $user->id,
            'given_to' => $givenTo,
            'feedback_type' => $feedbackType,
            'rating' => $validated['rating'],
            'comments' => $validated['comments'] ?? null,
        ]);

        $feedback->load(['givenBy', 'givenTo', 'jobApplication.job']);
        try {
            Mail::to(config('mail.contact.address'))->send(new FeedbackSubmitted($feedback));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->with('error', 'Your feedback has been saved, but the notification email to the Placement Officer could not be sent. Do not submit it again. Please contact the Placement Officer directly if you need assistance.');
        }

        return back()->with('success', 'Thank you! Your feedback has been submitted and the Placement Officer has been notified.');
    }
}
