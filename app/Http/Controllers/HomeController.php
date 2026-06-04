<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            ->with('jobType')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $latestJobs = Job::where('status', 1)
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
            $recipient = config('mail.from.address', env('MAIL_FROM_ADDRESS'));

            Mail::send([], [], function ($message) use ($data, $recipient) {
                $message->to($recipient)
                    ->subject('Contact form submission: ' . $data['subject'])
                    ->setBody("Name: {$data['name']}\nEmail: {$data['email']}\n\n{$data['message']}", 'text/plain')
                    ->replyTo($data['email']);
            });
        } catch (\Exception $exception) {
            return back()->withInput()->with('error', 'Unable to send your message right now. Please try again later.');
        }

        return back()->with('success', 'Thank you! Your message has been sent successfully.');
    }
}
