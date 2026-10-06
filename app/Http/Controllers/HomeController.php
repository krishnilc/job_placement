<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ContactConfirmation;
use App\Mail\ContactSubmission;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class HomeController extends Controller
{
    //home page
    public function index()
    {
        // $categories = Category::where('status', 1)->orderBy('name', 'asc')->take(8)->get();
        $categories = Category::where('status', 1)->withCount('jobs')->orderBy('jobs_count', 'desc')->take(8)->get();
        $newCategories = Category::where('status', 1)->orderBy('name', 'ASC')->get();

        $featuredJobs = Job::where('status', 1)
            ->where('isFeatured', 1)
            ->where(function ($query) {
                $query->whereNull('closing_date')
                    ->orWhere('closing_date', '>=', now()->toDateString());
            })
            ->with('jobType')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $latestJobs = Job::where('status', 1)
            ->where(function ($query) {
                $query->whereNull('closing_date')
                    ->orWhere('closing_date', '>=', now()->toDateString());
            })
            ->with('jobType')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('front.home', [
            'categories' => $categories,
            'newCategories' => $newCategories,
            'featuredJobs' => $featuredJobs,
            'latestJobs' => $latestJobs
        ]);
    }
    public function contact()
    {
        return view('front.contact');
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            Mail::to(config('mail.contact.address'))->send(new ContactSubmission(
                $data['name'], $data['email'], $data['subject'], $data['message']
            ));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->route('front.contact')->withInput()->with('error', 'Unable to send your message right now. Please try again later or contact the Placement Officer directly.');
        }

        try {
            Mail::to($data['email'])->send(new ContactConfirmation($data['name'], $data['subject']));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->route('front.contact')->with('warning', 'Your message has been sent to the Placement Officer, but we could not send your confirmation email. You do not need to submit the form again. The Placement Officer will get back to you as soon as possible.');
        }

        return redirect()->route('front.contact')->with('success', 'Thank you! Your message has been sent to the Placement Officer. A confirmation email has been sent to your email address. The Placement Officer will get back to you as soon as possible.');
    }
}
