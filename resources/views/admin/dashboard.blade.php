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

                    <ul class="nav nav-pills mb-4" id="dashboardMainTabs" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-overview-btn" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab">Overview</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-placement-btn" data-bs-toggle="tab" data-bs-target="#tab-placement" type="button" role="tab">Placement</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-rejection-btn" data-bs-toggle="tab" data-bs-target="#tab-rejection" type="button" role="tab">Rejection Trends</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-employers-btn" data-bs-toggle="tab" data-bs-target="#tab-employers" type="button" role="tab">Employers &amp; Funnel</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-metrics-btn" data-bs-toggle="tab" data-bs-target="#tab-metrics" type="button" role="tab">Reporting Metrics</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-applications-btn" data-bs-toggle="tab" data-bs-target="#tab-applications" type="button" role="tab">Applications</button></li>
                    </ul>

                    <div class="tab-content" id="dashboardMainTabsContent">

                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h7 class="text-muted mb-1">Total Jobs</h7>
                                                    <h2 class="text-success mb-0">{{ $totalJobs }}</h2>
                                                </div>
                                                <div class="text-success" style="font-size: 2rem;"><i class="fa fa-briefcase"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h7 class="text-muted mb-1">Approval Pending </h7>
                                                    <h2 class="text-warning mb-0">{{ $pendingJobs }}</h2>
                                                </div>
                                                <div class="text-warning" style="font-size: 2rem;"><i class="fa fa-hourglass-half"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h7 class="text-muted mb-1">Featured Jobs</h7>
                                                    <h2 class="text-primary mb-0">{{ $featuredJobs }}</h2>
                                                </div>
                                                <div class="text-primary" style="font-size: 2rem;"><i class="fa fa-star"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h7 class="text-muted mb-1">Blocked Jobs</h7>
                                                    <h2 class="text-danger mb-0">{{ $blockedJobs }}</h2>
                                                </div>
                                                <div class="text-danger" style="font-size: 2rem;"><i class="fa fa-ban"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h7 class="text-muted mb-1">Total Users</h7>
                                                    <h2 class="text-primary mb-0">{{ $totalUsers }}</h2>
                                                </div>
                                                <div class="text-primary" style="font-size: 2rem;"><i class="fa fa-users"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h7 class="text-muted mb-1">Total Applications</h7>
                                                    <h2 class="text-info mb-0">{{ $totalApplications }}</h2>
                                                </div>
                                                <div class="text-info" style="font-size: 2rem;"><i class="fa fa-file-text"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card border-0 shadow h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h7 class="text-muted mb-1">Pending&nbsp;Applications</h7>
                                                    <h2 class="text-warning mb-0">{{ $pendingApplications }}</h2>
                                                </div>
                                                <div class="text-warning" style="font-size: 2rem;"><i class="fa fa-clock-o"></i></div>
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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow">
                                        <div class="card-header bg-light"><h6 class="mb-0">Quick Links</h6></div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li class="mb-2"><a href="{{ route('admin.users.students') }}" class="text-decoration-none"><i class="fa fa-arrow-right"></i> Students</a></li>
                                                <li class="mb-2"><a href="{{ route('admin.users.employers') }}" class="text-decoration-none"><i class="fa fa-arrow-right"></i> Employees</a></li>
                                                <li class="mb-2"><a href="{{ route('admin.jobs') }}" class="text-decoration-none"><i class="fa fa-arrow-right"></i> Manage Jobs</a></li>
                                                <li><a href="{{ route('admin.jobApplications') }}" class="text-decoration-none"><i class="fa fa-arrow-right"></i> Review Applications</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow">
                                        <div class="card-header bg-light"><h6 class="mb-0">System Information</h6></div>
                                        <div class="card-body">
                                            <p class="mb-2"><strong>Total Statistics:</strong></p>
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

                        <!-- Placement Tab -->
                        <div class="tab-pane fade" id="tab-placement" role="tabpanel">
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

                            <div class="card border-0 shadow">
                                <div class="card-header bg-light"><h5 class="mb-0">Interview Conversion Rate</h5></div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4 mb-3"><div class="card border-0 bg-light h-100"><div class="card-body"><p class="text-muted mb-1">Interviewed</p><h3 class="mb-0">{{ number_format($interviewedApplications) }}</h3></div></div></div>
                                        <div class="col-md-4 mb-3"><div class="card border-0 bg-light h-100"><div class="card-body"><p class="text-muted mb-1">Placed</p><h3 class="text-success mb-0">{{ number_format($interviewPlacedApplications) }}</h3></div></div></div>
                                        <div class="col-md-4 mb-3"><div class="card border-0 bg-light h-100"><div class="card-body"><p class="text-muted mb-1">Interview &rarr; Placement</p><h3 class="text-primary mb-0">{{ number_format($interviewConversionRate, 1) }}%</h3></div></div></div>
                                    </div>
                                    <p class="text-muted small mb-3">Of students who reached the interview stage, {{ number_format($interviewConversionRate, 1) }}% eventually secured placement.</p>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead><tr><th>Programme</th><th class="text-end">Interviewed</th><th class="text-end">Placed</th><th class="text-end">Interview &rarr; Placement</th></tr></thead>
                                            <tbody>
                                                @forelse($interviewConversionByProgramme as $report)
                                                    <tr><td>{{ $report->degree }}</td><td class="text-end">{{ $report->interviewed_count }}</td><td class="text-end">{{ $report->placed_count }}</td><td class="text-end fw-semibold">{{ number_format($report->conversion_rate, 1) }}%</td></tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center text-muted py-4">No interview data for the selected filters.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rejection Trends Tab -->
                        <div class="tab-pane fade" id="tab-rejection" role="tabpanel">
                            <div class="card border-0 shadow">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4 mb-3"><div class="card border-0 bg-light h-100"><div class="card-body"><p class="text-muted mb-1">Total Applications</p><h3 class="mb-0">{{ number_format($placementTotalApplications) }}</h3></div></div></div>
                                        <div class="col-md-4 mb-3"><div class="card border-0 bg-light h-100"><div class="card-body"><p class="text-muted mb-1">Rejected</p><h3 class="text-danger mb-0">{{ number_format($rejectedApplications) }}</h3></div></div></div>
                                        <div class="col-md-4 mb-3"><div class="card border-0 bg-light h-100"><div class="card-body"><p class="text-muted mb-1">Rejection Rate</p><h3 class="text-primary mb-0">{{ number_format($rejectionRate, 1) }}%</h3></div></div></div>
                                    </div>

                                    <h6 class="mb-2">Yearly Application Funnel</h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead><tr><th>Year</th><th class="text-end">Submitted</th><th class="text-end">Shortlisted</th><th class="text-end">Interviewed</th><th class="text-end">Placed</th><th class="text-end">Rejected</th><th class="text-end">Withdrawn</th><th class="text-end">Total</th></tr></thead>
                                            <tbody>
                                                @forelse($yearlyFunnelReports as $row)
                                                    <tr>
                                                        <td>{{ $row['year'] }}</td>
                                                        <td class="text-end">{{ number_format($row['submitted']) }}</td>
                                                        <td class="text-end">{{ number_format($row['shortlisted']) }}</td>
                                                        <td class="text-end">{{ number_format($row['interviewed']) }}</td>
                                                        <td class="text-end text-success">{{ number_format($row['placed']) }}</td>
                                                        <td class="text-end text-danger">{{ number_format($row['rejected']) }}</td>
                                                        <td class="text-end">{{ number_format($row['withdrawn']) }}</td>
                                                        <td class="text-end fw-semibold">{{ number_format($row['total']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="8" class="text-center text-muted py-4">No application data for the selected filters.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <ul class="nav nav-tabs" id="rejectionTrendsTabs" role="tablist">
                                        <li class="nav-item" role="presentation"><button class="nav-link active" id="rej-year-tab" data-bs-toggle="tab" data-bs-target="#rej-year" type="button" role="tab">Year</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="rej-month-tab" data-bs-toggle="tab" data-bs-target="#rej-month" type="button" role="tab">Month</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="rej-college-tab" data-bs-toggle="tab" data-bs-target="#rej-college" type="button" role="tab">College</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="rej-programme-tab" data-bs-toggle="tab" data-bs-target="#rej-programme" type="button" role="tab">Programme</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="rej-category-tab" data-bs-toggle="tab" data-bs-target="#rej-category" type="button" role="tab">Job Category</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="rej-employer-tab" data-bs-toggle="tab" data-bs-target="#rej-employer" type="button" role="tab">Employer</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="rej-type-tab" data-bs-toggle="tab" data-bs-target="#rej-type" type="button" role="tab">Opportunity Type</button></li>
                                    </ul>
                                    <div class="tab-content border border-top-0 p-3" id="rejectionTrendsTabsContent">
                                        <div class="tab-pane fade show active" id="rej-year" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead><tr><th>Year</th><th class="text-end">Applications</th><th class="text-end">Rejected</th><th class="text-end">Rejection Rate</th></tr></thead>
                                                    <tbody>
                                                        @forelse($rejectionByYear as $report)
                                                            <tr><td>{{ $report->year }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end">{{ $report->rejected_count }}</td><td class="text-end fw-semibold">{{ number_format($report->rejection_rate, 1) }}%</td></tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted py-4">No data for the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="rej-month" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead><tr><th>Month</th><th class="text-end">Applications</th><th class="text-end">Rejected</th><th class="text-end">Rejection Rate</th></tr></thead>
                                                    <tbody>
                                                        @forelse($rejectionByMonth as $report)
                                                            <tr><td>{{ $report->month }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end">{{ $report->rejected_count }}</td><td class="text-end fw-semibold">{{ number_format($report->rejection_rate, 1) }}%</td></tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted py-4">No data for the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="rej-college" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead><tr><th>College</th><th class="text-end">Applications</th><th class="text-end">Rejected</th><th class="text-end">Rejection Rate</th></tr></thead>
                                                    <tbody>
                                                        @forelse($rejectionByCollege as $report)
                                                            <tr><td>{{ $report->university }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end">{{ $report->rejected_count }}</td><td class="text-end fw-semibold">{{ number_format($report->rejection_rate, 1) }}%</td></tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted py-4">No data for the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="rej-programme" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead><tr><th>Programme</th><th class="text-end">Applications</th><th class="text-end">Rejected</th><th class="text-end">Rejection Rate</th></tr></thead>
                                                    <tbody>
                                                        @forelse($rejectionByProgramme as $report)
                                                            <tr><td>{{ $report->degree }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end">{{ $report->rejected_count }}</td><td class="text-end fw-semibold">{{ number_format($report->rejection_rate, 1) }}%</td></tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted py-4">No data for the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="rej-category" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead><tr><th>Job Category</th><th class="text-end">Applications</th><th class="text-end">Rejected</th><th class="text-end">Rejection Rate</th></tr></thead>
                                                    <tbody>
                                                        @forelse($rejectionByCategory as $report)
                                                            <tr><td>{{ $report->category }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end">{{ $report->rejected_count }}</td><td class="text-end fw-semibold">{{ number_format($report->rejection_rate, 1) }}%</td></tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted py-4">No data for the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="rej-employer" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead><tr><th>Employer</th><th class="text-end">Applications</th><th class="text-end">Rejected</th><th class="text-end">Rejection Rate</th></tr></thead>
                                                    <tbody>
                                                        @forelse($rejectionByEmployer as $report)
                                                            <tr><td>{{ $report->employer }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end">{{ $report->rejected_count }}</td><td class="text-end fw-semibold">{{ number_format($report->rejection_rate, 1) }}%</td></tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted py-4">No data for the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="rej-type" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead><tr><th>Opportunity Type</th><th class="text-end">Applications</th><th class="text-end">Rejected</th><th class="text-end">Rejection Rate</th></tr></thead>
                                                    <tbody>
                                                        @forelse($rejectionByOpportunityType as $report)
                                                            <tr><td>{{ $report->opportunity_type }}</td><td class="text-end">{{ $report->application_count }}</td><td class="text-end">{{ $report->rejected_count }}</td><td class="text-end fw-semibold">{{ number_format($report->rejection_rate, 1) }}%</td></tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted py-4">No data for the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employers & Funnel Tab -->
                        <div class="tab-pane fade" id="tab-employers" role="tabpanel">
                            <div class="card border-0 shadow mb-4">
                                <div class="card-header bg-light"><h5 class="mb-0">Employer-Level Reporting</h5></div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead><tr><th>Employer</th><th class="text-end">Applications</th><th class="text-end">Interviewed</th><th class="text-end">Placed</th><th class="text-end">Rejected</th></tr></thead>
                                        <tbody>
                                            @forelse($employerPerformanceReports as $report)
                                                <tr>
                                                    <td>{{ $report->employer_name }}</td>
                                                    <td class="text-end">{{ number_format($report->application_count) }}</td>
                                                    <td class="text-end">{{ number_format($report->interviewed_count) }}</td>
                                                    <td class="text-end text-success">{{ number_format($report->placed_count) }}</td>
                                                    <td class="text-end text-danger">{{ number_format($report->rejected_count) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-4">No employer data for the selected filters.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card border-0 shadow">
                                <div class="card-header bg-light"><h5 class="mb-0">Recruitment Funnel</h5></div>
                                <div class="card-body">
                                    @forelse($funnelReports as $index => $stage)
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-semibold">{{ $stage['name'] }}</span>
                                                <span>{{ number_format($stage['count']) }} <small class="text-muted">({{ $stage['conversion_from_start'] }}% of {{ $funnelReports[0]['name'] }})</small></span>
                                            </div>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $stage['conversion_from_start'] }}%;" aria-valuenow="{{ $stage['conversion_from_start'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        @if(!$loop->last)
                                            @php $nextStage = $funnelReports[$index + 1]; @endphp
                                            <div class="text-center text-muted small mb-2">
                                                &darr; dropped off: {{ number_format($nextStage['drop_off']) }} ({{ $nextStage['drop_off_rate'] }}%)
                                            </div>
                                        @endif
                                    @empty
                                        <p class="text-muted text-center py-4 mb-0">No funnel data for the selected filters.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Reporting Metrics Tab -->
                        <div class="tab-pane fade" id="tab-metrics" role="tabpanel">
                            <div class="card border-0 shadow">
                                <div class="card-header bg-light"><h5 class="mb-0">Application-Level vs Student-Level Metrics</h5></div>
                                <div class="card-body">
                                    <p class="text-muted small">Application counts can overstate outcomes since one student may submit many applications. Both views are tracked separately for accurate institutional reporting.</p>
                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <h6>Application-Level Metrics</h6>
                                            <table class="table table-sm table-borderless mb-0">
                                                <tbody>
                                                    <tr><td>Total Applications</td><td class="text-end fw-semibold">{{ number_format($applicationMetrics['total_applications']) }}</td></tr>
                                                    <tr><td>Shortlisting Rate</td><td class="text-end fw-semibold">{{ $applicationMetrics['shortlisting_rate'] }}%</td></tr>
                                                    <tr><td>Interview Conversion</td><td class="text-end fw-semibold">{{ $applicationMetrics['interview_conversion_rate'] }}%</td></tr>
                                                    <tr><td>Rejection Rate</td><td class="text-end fw-semibold">{{ $applicationMetrics['rejection_rate'] }}%</td></tr>
                                                    <tr><td>Offer Conversion</td><td class="text-end fw-semibold">{{ $applicationMetrics['offer_conversion_rate'] }}%</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Student-Level Metrics</h6>
                                            <table class="table table-sm table-borderless mb-0">
                                                <tbody>
                                                    <tr><td>Students Seeking Employment</td><td class="text-end fw-semibold">{{ number_format($studentMetrics['students_seeking_employment']) }}</td></tr>
                                                    <tr><td>Students Successfully Placed</td><td class="text-end fw-semibold text-success">{{ number_format($studentMetrics['students_successfully_placed']) }}</td></tr>
                                                    <tr><td>Graduate Employment Rate</td><td class="text-end fw-semibold text-primary">{{ $studentMetrics['graduate_employment_rate'] }}%</td></tr>
                                                </tbody>
                                            </table>
                                            <p class="text-muted small mb-0">Industrial Attachment (IA) tracking isn't part of the current data model, so it isn't reported here yet.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Applications Tab -->
                        <div class="tab-pane fade" id="tab-applications" role="tabpanel">
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
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentApplications as $application)
                                                        <tr>
                                                            <td><strong>{{ $application->job->title ?? 'N/A' }}</strong></td>
                                                            <td>{{ $application->user->name ?? 'N/A' }}</td>
                                                            <td>{{ $application->job->company_name ?? 'N/A' }}</td>
                                                            <td>{{ $application->applicationStatus?->name ?? 'Submitted' }}</td>
                                                            <td>{{ $application->created_at->format('M d, Y') }}</td>
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
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var filterKeys = ['college', 'programme', 'employer', 'year', 'opportunity_type', 'category'];
        var params = new URLSearchParams(window.location.search);
        var hasFilters = filterKeys.some(function (key) {
            return params.has(key) && params.get(key) !== '';
        });

        var targetSelector = window.location.hash && document.querySelector(window.location.hash)
            ? window.location.hash
            : (hasFilters ? '#tab-placement' : null);

        if (targetSelector) {
            var trigger = document.querySelector('#dashboardMainTabs [data-bs-target="' + targetSelector + '"]');
            if (trigger) {
                new bootstrap.Tab(trigger).show();
            }
        }

        document.querySelectorAll('#dashboardMainTabs button[data-bs-toggle="tab"]').forEach(function (button) {
            button.addEventListener('shown.bs.tab', function (event) {
                history.replaceState(null, '', window.location.pathname + window.location.search + event.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>
@endsection