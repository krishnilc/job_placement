@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <!-- <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li> -->
                            <li class="breadcrumb-item active">Dashboard</li>
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

                    <div class="card border-0 shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Placement Rate Report</h5>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
                            </div>
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <label for="college" class="form-label">College</label>
                                    <select name="college" id="college" class="form-select">
                                        <option value="">All colleges</option>
                                        @foreach($collegeOptions as $college)
                                            <option value="{{ $college }}" {{ request('college') === $college ? 'selected' : '' }}>{{ $college }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label for="programme" class="form-label">Programme</label>
                                    <select name="programme" id="programme" class="form-select">
                                        <option value="">All programmes</option>
                                        @foreach($programmeOptions as $programme)
                                            <option value="{{ $programme }}" {{ request('programme') === $programme ? 'selected' : '' }}>{{ $programme }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label for="employer" class="form-label">Employer</label>
                                    <select name="employer" id="employer" class="form-select">
                                        <option value="">All employers</option>
                                        @foreach($employerOptions as $employer)
                                            <option value="{{ $employer->id }}" {{ (string) request('employer') === (string) $employer->id ? 'selected' : '' }}>{{ $employer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label for="year" class="form-label">Year</label>
                                    <select name="year" id="year" class="form-select">
                                        <option value="">All years</option>
                                        @foreach($yearOptions as $year)
                                            <option value="{{ $year }}" {{ (string) request('year') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label for="opportunity_type" class="form-label">Opportunity Type</label>
                                    <select name="opportunity_type" id="opportunity_type" class="form-select">
                                        <option value="">All opportunity types</option>
                                        @foreach($opportunityTypeOptions as $type)
                                            <option value="{{ $type->id }}" {{ (string) request('opportunity_type') === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label for="category" class="form-label">Job Category</label>
                                    <select name="category" id="category" class="form-select">
                                        <option value="">All job categories</option>
                                        @foreach($categoryOptions as $category)
                                            <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Apply filters</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3"><div class="card border-0 shadow h-100"><div class="card-body"><p class="text-muted mb-1">Total Applications</p><h2 class="mb-0">{{ number_format($placementTotalApplications) }}</h2></div></div></div>
                        <div class="col-md-4 mb-3"><div class="card border-0 shadow h-100"><div class="card-body"><p class="text-muted mb-1">Students Placed</p><h2 class="text-success mb-0">{{ number_format($placedApplications) }}</h2></div></div></div>
                        <div class="col-md-4 mb-3"><div class="card border-0 shadow h-100"><div class="card-body"><p class="text-muted mb-1">Placement Rate</p><h2 class="text-primary mb-0">{{ number_format($placementRate, 1) }}%</h2></div></div></div>
                    </div>

                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-light"><h5 class="mb-0">Placement Rate by College</h5></div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th>College</th><th class="text-end">Placed</th><th class="text-end">Applications</th><th class="text-end">Placement Rate</th></tr></thead>
                                <tbody>
                                    @forelse($collegePlacementReports as $report)
                                        <tr><td>{{ $report->university }}</td><td class="text-end">{{ $report->placed_count }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end fw-semibold">{{ number_format($report->placement_rate, 1) }}%</td></tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No placement data for the selected filters.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">

                        <!-- Total Jobs Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h7 class="text-muted mb-1">Total Jobs</h7>
                                            <h2 class="text-success mb-0">{{ $totalJobs }}</h2>
                                        </div>
                                        <div class="text-success" style="font-size: 2rem;">
                                            <i class="fa fa-briefcase"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Approval Pending Jobs Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h7 class="text-muted mb-1">Approval Pending </h7>
                                            <h2 class="text-warning mb-0">{{ $pendingJobs }}</h2>
                                        </div>
                                        <div class="text-warning" style="font-size: 2rem;">
                                            <i class="fa fa-hourglass-half"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Featured Jobs Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h7 class="text-muted mb-1">Featured Jobs</h7>
                                            <h2 class="text-primary mb-0">{{ $featuredJobs }}</h2>
                                        </div>
                                        <div class="text-primary" style="font-size: 2rem;">
                                            <i class="fa fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blocked Jobs Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h7 class="text-muted mb-1">Blocked Jobs</h7>
                                            <h2 class="text-danger mb-0">{{ $blockedJobs }}</h2>
                                        </div>
                                        <div class="text-danger" style="font-size: 2rem;">
                                            <i class="fa fa-ban"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Users Card -->
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h7 class="text-muted mb-1">Total Users</h7>
                                            <h2 class="text-primary mb-0">{{ $totalUsers }}</h2>
                                        </div>
                                        <div class="text-primary" style="font-size: 2rem;">
                                            <i class="fa fa-users"></i>
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
                                            <h7 class="text-muted mb-1">Total Applications</h7>
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
                                            <h7 class="text-muted mb-1">Pending&nbsp;Applications</h7>
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

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Application Status Reports</h5>
                            <a href="{{ route('admin.jobApplications') }}" class="btn btn-sm btn-outline-primary">Review Applications</a>
                        </div>

                        <div class="row mb-3">
                            @foreach($applicationStatusCategoryReports as $report)
                                @php
                                    $categoryClass = match ($report['category']) {
                                        'Successful' => 'success',
                                        'Unsuccessful' => 'danger',
                                        'Withdrawn' => 'secondary',
                                        default => 'warning',
                                    };
                                @endphp
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <p class="text-muted mb-1">{{ $report['category'] }}</p>
                                            <h3 class="text-{{ $categoryClass }} mb-0">{{ $report['application_count'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card border-0 shadow">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Status</th>
                                            <th>Category</th>
                                            <th class="text-end">Applications</th>
                                            <th style="min-width: 170px;">Share of Applications</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($applicationStatusReports as $report)
                                            @php
                                                $percentage = $totalApplications > 0 ? round(($report->application_count / $totalApplications) * 100) : 0;
                                            @endphp
                                            <tr>
                                                <td class="fw-semibold">{{ $report->name }}</td>
                                                <td><span class="badge bg-light text-dark border">{{ $report->category }}</span></td>
                                                <td class="text-end">{{ $report->application_count }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <small class="text-muted text-nowrap">{{ $percentage }}%</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
                                                <th>Status</th>
                                                <th>Applied Date</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentApplications as $application)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $application->job->title ?? 'N/A' }}</strong>
                                                    </td>
                                                    <td>{{ $application->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $application->job->company_name ?? 'N/A' }}</td>
                                                    <td>{{ $application->applicationStatus?->name ?? 'Submitted' }}</td>
                                                    <td>
                                                        {{ $application->created_at->format('M d, Y') }}
                                                    </td>
                                                    {{-- <td>
                                                        <a href="{{ route('admin.jobApplications') }}" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                    </td> --}}
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
                                            <a href="{{ route('admin.users.students') }}" class="text-decoration-none">
                                                <i class="fa fa-arrow-right"></i> Students
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <a href="{{ route('admin.users.employers') }}" class="text-decoration-none">
                                                <i class="fa fa-arrow-right"></i> Employees
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