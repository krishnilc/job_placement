@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Admin Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('admin.sidebar')
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <!-- Total Users Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">Total Users</h6>
                                            <h2 class="text-primary mb-0">{{ $totalUsers }}</h2>
                                        </div>
                                        <div class="text-primary" style="font-size: 2rem;">
                                            <i class="fa fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Jobs Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">Total Jobs</h6>
                                            <h2 class="text-success mb-0">{{ $totalJobs }}</h2>
                                        </div>
                                        <div class="text-success" style="font-size: 2rem;">
                                            <i class="fa fa-briefcase"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Applications Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">Total Applications</h6>
                                            <h2 class="text-info mb-0">{{ $totalApplications }}</h2>
                                        </div>
                                        <div class="text-info" style="font-size: 2rem;">
                                            <i class="fa fa-file-text"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Applications Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">Pending Applications</h6>
                                            <h2 class="text-warning mb-0">{{ $pendingApplications }}</h2>
                                        </div>
                                        <div class="text-warning" style="font-size: 2rem;">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Applications Section -->
                    <div class="card border-0 shadow">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Recent Job Applications</h5>
                        </div>
                        <div class="card-body">
                            @if($recentApplications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Job Title</th>
                                                <th>Applicant</th>
                                                <th>Employer</th>
                                                <th>Applied Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentApplications as $application)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $application->job->title ?? 'N/A' }}</strong>
                                                    </td>
                                                    <td>{{ $application->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $application->employer->name ?? 'N/A' }}</td>
                                                    <td>
                                                        {{ $application->created_at->format('M d, Y') }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.jobApplications') }}" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">No job applications yet</p>
                            @endif
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ route('admin.jobApplications') }}" class="btn btn-primary">View All Applications</a>
                        </div>
                    </div>

                    <!-- Quick Links Section -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Quick Links</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <a href="{{ route('admin.users') }}" class="text-decoration-none">
                                                <i class="fa fa-arrow-right"></i> Manage Users
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <a href="{{ route('admin.jobs') }}" class="text-decoration-none">
                                                <i class="fa fa-arrow-right"></i> Manage Jobs
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.jobApplications') }}" class="text-decoration-none">
                                                <i class="fa fa-arrow-right"></i> Review Applications
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">System Information</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>Total Statistics:</strong>
                                    </p>
                                    <ul class="list-unstyled text-muted small">
                                        <li>Users: <strong>{{ $totalUsers }}</strong></li>
                                        <li>Jobs Posted: <strong>{{ $totalJobs }}</strong></li>
                                        <li>Applications Received: <strong>{{ $totalApplications }}</strong></li>
                                        <li>Avg. Applications per Job: <strong>{{ $totalJobs > 0 ? number_format($totalApplications / $totalJobs, 2) : 0 }}</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
@endsection