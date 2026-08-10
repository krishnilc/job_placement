@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item active"><a href="{{ route('student.dashboard') }}">Home</a></li>
                            <!-- <li class="breadcrumb-item active">Account Settings</li> -->
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    @include('front.account.sidebar')
                </div>
                <div class="col-lg-9">
                    <div class="card border-0 shadow mb-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8 p-5">
                                <h4 class="fw-bold ">
                                    Welcome back, {{ Auth::user()->name }} 👋
                                </h4>

                                <p class="text-muted fs-5 mb-4">
                                    Explore job opportunities, internships, and industrial attachments tailored for you.
                                </p>

                                <!-- <div class="d-flex flex-wrap gap-3">
                                            <a href="{{ route('front.jobs') }}" class="btn btn-primary px-4 py-2 rounded-pill">
                                                Browse Jobs
                                            </a>

                                            <a href="{{ route('account.savedJobs') }}"
                                                class="btn btn-outline-primary px-4 py-2 rounded-pill">
                                                Saved Jobs
                                            </a>

                                            <a href="{{ route('account.editProfile') }}"
                                                class="btn btn-outline-dark px-4 py-2 rounded-pill">
                                                My Profile
                                            </a>
                                        </div> -->
                            </div>

                            <div class="col-lg-4 text-center mt-4 mt-lg-0">
                                <img src="{{ asset('assets/images/welcome-job.png') }}" alt="Welcome" class="img-fluid"
                                    style="max-height: 250px;">
                            </div>
                        </div>
                    </div>
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body p-4">
                            <h3 class="fs-4 mb-3">
                                Your Job Statistics
                            </h3>
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <a href="{{ route('front.jobs') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-primary">
                                                    {{ $availableJobs ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Available Jobs
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4">
                                    <a href="{{ route('account.savedJobs') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-danger">
                                                    {{ $savedJobsCount ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Saved Jobs
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4">
                                    <a href="{{ route('account.myJobApplications') }}">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body text-center p-4">
                                                <h3 class="fw-bold text-success">
                                                    {{ $appliedJobsCount ?? 0 }}
                                                </h3>
                                                <p class="mb-0 text-muted">
                                                    Applications Submitted
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="fs-4 mb-0">
                                    Latest Opportunities
                                </h3>
                                <a href="{{ route('front.jobs') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    View All
                                </a>
                            </div>

                            <div class="row g-4">
                                @forelse($latestJobs as $job)
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

                                                    <a href="{{ route('jobDetail', $job->id) }}"
                                                        class="btn btn-primary btn-sm rounded-pill px-3">
                                                        View Details
                                                    </a>
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-info rounded-4">
                                            No jobs available at the moment.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection