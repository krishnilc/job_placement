<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Mail\EmployerJobPosted;
use App\Models\Category;
use App\Models\College;
use App\Models\JobType;
use App\Models\Job;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class JobController extends Controller
{
    public function createJob()
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin', 'employer'], true)) {
            session()->flash('error', 'Only admins and employers can create jobs.');

            return redirect()->route('home');
        }

        $colleges = College::active()->orderBy('name')->get();
        $categories = Category::orderBy('name', 'ASC')->where('status', '1')->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', '1')->get();

        return view('employer.jobs.create', [
            'colleges' => $colleges,
            'categories' => $categories,
            'jobTypes' => $jobTypes
        ]);
    }

    public function saveJob(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'super_admin', 'employer'], true), 403);
        $isEmployer = Auth::user()->role === 'employer';

        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'job_type' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'closing_date' => 'nullable|date',
            'experience' => 'required',
        ];

        // Admins/super admins can still type free-form company details; employers' organizational
        // details are locked to their organization profile and are not accepted from the request.
        if (!$isEmployer) {
            $rules['company_name'] = 'required|min:3|max:75';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        if ($isEmployer && empty(Auth::user()->company_name)) {
            return response()->json([
                'status' => false,
                'errors' => ['company_name' => ['Please complete your organization profile before posting a job.']]
            ]);
        }

        $job = new Job();

        $job->title = $request->title;
        $job->category_id = $request->category;
        $job->job_type_id = $request->job_type;
        $job->user_id = Auth::id();
        $job->organization_id = Auth::user()->employerProfile?->organization_id;
        $job->vacancy = $request->vacancy;
        $job->closing_date = $request->closing_date;
        $job->salary = $request->salary;
        $job->location = $request->location;
        $job->description = $request->description;
        $job->responsibilities = $request->responsibilities;
        $job->qualifications = $request->qualifications;
        $job->keywords = $request->keywords;
        $job->experience = $request->experience;
        $job->company_name = $isEmployer ? Auth::user()->company_name : $request->company_name;
        $job->company_location = $isEmployer ? Auth::user()->company_address : $request->company_location;
        $job->company_website = $isEmployer ? Auth::user()->website_url : $request->company_website;
        $job->status = $isEmployer ? 0 : 1;

        $job->save();

        $message = $isEmployer
            ? 'Job submitted successfully and is awaiting admin approval.'
            : 'Job created successfully!';
        $notificationSent = null;
        if ($isEmployer) {
            try {
                Mail::to(config('mail.contact.address'))->send(new EmployerJobPosted($job->load('user')));
                $notificationSent = true;
                $message .= ' The Placement Officer has been notified.';
            } catch (TransportExceptionInterface $exception) {
                report($exception);
                $notificationSent = false;
                $message .= ' The job is saved and awaiting approval, but the Placement Officer notification email could not be sent. Please contact them directly.';
            }
        }

        session()->flash($notificationSent === false ? 'error' : 'success', $message);
        return response()->json([
            'status' => true,
            'errors' => [],
            'notification_sent' => $notificationSent,
            'message' => $message,
        ]);
    }

    public function myJobs(Request $request)
    {
        $sortableColumns = [
            'title' => 'title',
            'company_name' => 'company_name',
            'created_at' => 'created_at',
            'closing_date' => 'closing_date',
            'status' => 'status',
            'featured' => 'isFeatured',
        ];
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'created_at';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $search = trim((string) $request->query('search', ''));

        $jobs = Job::where('user_id', Auth::id())
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('location', 'like', $like);
                });
            })
            ->with(['jobType', 'applications'])
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('employer.jobs.index', [
            'jobs' => $jobs,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function editJob(Request $request, $id)
    {

        $colleges = College::active()->orderBy('name')->get();
        $categories = Category::orderBy('name', 'ASC')->where('status', '1')->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', '1')->get();

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->first();

        if (!$job) {
            abort(404); // Job not found or does not belong to the authenticated user
        }

        return view('employer.jobs.edit', [
            'colleges' => $colleges,
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'job' => $job
        ]);
    }

    public function updateJob(Request $request, $id)
    {
        $isEmployer = Auth::user()->role === 'employer';

        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'job_type' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'closing_date' => 'nullable|date',
        ];

        // Admins/super admins can still type free-form company details; employers' organizational
        // details are locked to their organization profile and are not accepted from the request.
        if (!$isEmployer) {
            $rules['company_name'] = 'required|min:3|max:75';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        if ($isEmployer && empty(Auth::user()->company_name)) {
            return response()->json([
                'status' => false,
                'errors' => ['company_name' => ['Please complete your organization profile before posting a job.']]
            ]);
        }

        $job = Job::find($id);

        $job->title = $request->title;
        $job->category_id = $request->category;
        $job->job_type_id = $request->job_type;
        $job->user_id = Auth::id();
        $job->vacancy = $request->vacancy;
        $job->closing_date = $request->closing_date;
        $job->salary = $request->salary;
        $job->location = $request->location;
        $job->description = $request->description;
        $job->responsibilities = $request->responsibilities;
        $job->qualifications = $request->qualifications;
        $job->keywords = $request->keywords;
        $job->experience = $request->experience;
        $job->company_name = $isEmployer ? Auth::user()->company_name : $request->company_name;
        $job->company_location = $isEmployer ? Auth::user()->company_address : $request->company_location;
        $job->company_website = $isEmployer ? Auth::user()->website_url : $request->company_website;

        $job->save();

        session()->flash('success', 'Job updated successfully!');
        return response()->json([
            'status' => true,
            'errors' => []
        ]);
    }

    public function deleteJob(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            session()->flash('error', 'Only admins can delete jobs.');
            return response()->json([
                'status' => false,
                'errors' => ['Only admins can delete jobs.']
            ]);
        }

        $job = Job::where([
            'id' => $request->jobId
        ])->first();

        if (!$job) {
            session()->flash('error', 'Job not found or you do not have permission to delete this job!');
            return response()->json([
                'status' => false,
                'errors' => ['Job not found or you do not have permission to delete this job!']
            ]);
        }

        Job::where('id', $request->jobId)->delete();
        session()->flash('success', 'Job deleted successfully!');

        return response()->json([
            'status' => true
        ]);
    }

    public function blockJob(Request $request)
    {
        if (Auth::user()->role !== 'employer') {
            session()->flash('error', 'Only employers can block jobs.');
            return response()->json([
                'status' => false,
                'errors' => ['Only employers can block jobs.']
            ]);
        }

        $job = Job::where([
            'id' => $request->jobId,
            'user_id' => Auth::id()
        ])->first();

        if (!$job) {
            session()->flash('error', 'Job not found or you do not have permission to block this job!');
            return response()->json([
                'status' => false,
                'errors' => ['Job not found or you do not have permission to block this job!']
            ]);
        }

        $job->status = 2;
        $job->save();

        session()->flash('success', 'Job blocked successfully!');

        return response()->json([
            'status' => true
        ]);
    }

    public function unblockJob(Request $request)
    {
        if (Auth::user()->role !== 'employer') {
            session()->flash('error', 'Only employers can unblock jobs.');
            return response()->json([
                'status' => false,
                'errors' => ['Only employers can unblock jobs.']
            ]);
        }

        $job = Job::where([
            'id' => $request->jobId,
            'user_id' => Auth::id()
        ])->first();

        if (!$job) {
            session()->flash('error', 'Job not found or you do not have permission to unblock this job!');
            return response()->json([
                'status' => false,
                'errors' => ['Job not found or you do not have permission to unblock this job!']
            ]);
        }

        $job->status = 1;
        $job->save();

        session()->flash('success', 'Job unblocked successfully!');

        return response()->json([
            'status' => true
        ]);
    }
}
