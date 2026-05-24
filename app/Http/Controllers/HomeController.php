<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;

// use Illuminate\Http\Request;

class HomeController extends Controller
{
    //home page
    public function index()
    {
        // $categories = Category::where('status', 1)->orderBy('name', 'asc')->take(8)->get();
         $categories = Category::where('status', 1)->orderBy('name', 'asc')->get();
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
}
