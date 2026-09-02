@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item active"><a href="{{ route('employer.dashboard') }}">Home</a></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    @include('employer.sidebar')
                </div>
                <div class="col-lg-9">
                    <div class="card border-0 shadow mb-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8 p-5">
                                <h4 class="fw-bold ">
                                    Welcome back, {{ Auth::user()->name }} 👋
                                </h4>

                                <p class="text-muted fs-5 mb-4">
                                    Manage your job postings, review applications, and grow your team.
                                </p>
                            </div>

                            <div class="col-lg-4 text-center mt-4 mt-lg-0">
                                <img src="{{ asset('assets/images/welcome-job.png') }}" alt="Welcome" class="img-fluid"
                                    style="max-height: 250px;">
                            </div>
                        </div>
                    </div>

                    <!-- Job Statistics -->
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body p-4">
                            <h3 class="fs-4 mb-3">
                                Your Job Statistics
                            </h3>
                            <div class="row g-4 mb-4">
                                 <div class="col-md-4">
                                    <div class="card border-0 shadow-sm rounded-4 h-100">
                                        <div class="card-body text-center p-4">
                                            <h3 class="fw-bold text-info">
                                                {{ $studentUsers ?? 0 }}
                                            </h3>
                                            <p class="mb-0 text-muted">
                                                Student Users
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <a href="{{ route('account.myJobApplications') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-success">
                                                    {{ $totalApplications ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Total Applications
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4">
                                    <a href="{{ route('account.myJobApplications') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-warning">
                                                    {{ $pendingApplications ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Pending Applications
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <h3 class="fs-4 mb-3">
                                Additional Statistics
                            </h3>
                            <div class="row g-4">
                                 <div class="col-md-4">
                                    <a href="{{ route('account.myJobs') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-primary">
                                                    {{ $totalJobs ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Total Jobs Posted
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                              

                                <div class="col-md-4">
                                    <a href="{{ route('account.myJobs') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-danger">
                                                    {{ $blockedJobs ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Blocked Jobs
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4">
                                    <a href="{{ route('account.myJobs') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-primary">
                                                    {{ $featuredJobs ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Featured Jobs
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Applications -->
                    @if (count($recentApplications) > 0)
                        <div class="card border-0 shadow mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="fs-4 mb-0">
                                        Recent Applications
                                    </h3>
                                    <a href="{{ route('account.myJobApplications') }}"
                                        class="btn btn-sm btn-outline-primary rounded-pill">
                                        View All
                                    </a>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Applicant</th>
                                                <th>Job Title</th>
                                                <th>Status</th>
                                                <th>Applied Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentApplications as $application)
                                                <tr>
                                                    <td class="fw-bold">{{ $application->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $application->job->title ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $application->applicationStatus?->name ?? 'Submitted' }}</span>
                                                    </td>
                                                    <td><small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($application->created_at)->format('M d, Y') }}</small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">
                                                        No applications yet
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Recent Jobs Posted -->
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="fs-4 mb-0">
                                    Your Latest Job Postings
                                </h3>
                                @if (in_array(auth()->user()->role, ['admin', 'employer'], true))
                                    <a href="{{ route('account.createJob') }}" class="btn btn-sm btn-primary rounded-pill">
                                        + New Job
                                    </a>
                                @endif
                            </div>

                            <div class="row g-4">
                                @forelse($recentJobs as $job)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body p-4">
                                                <h6 class="fw-bold mb-2">
                                                    {{ $job->title }}
                                                </h6>
                                                <div class="mb-3">
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $job->jobType->name ?? 'Job' }}
                                                    </span>
                                                </div>
                                                <p class="text-muted small mb-3">
                                                    {{ $job->company_name }}
                                                </p>
                                                <p class="text-muted small">
                                                    {{ $job->location }}
                                                </p>
                                                <div class="mt-4 d-flex justify-content-between align-items-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('account.editJob', $job->id) }}"
                                                            class="btn btn-sm btn-outline-primary rounded-start px-2">
                                                            Edit
                                                        </a>
                                                        <a href="{{ route('jobDetail', $job->id) }}"
                                                            class="btn btn-sm btn-primary rounded-end px-2">
                                                            View
                                                        </a>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-2">
                                                    {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-info rounded-4">
                                            You haven't posted any jobs yet.
                                            @if (in_array(auth()->user()->role, ['admin', 'employer'], true))
                                                <a href="{{ route('account.createJob') }}">Create your first job
                                                    posting</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
