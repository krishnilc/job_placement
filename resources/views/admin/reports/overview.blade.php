<!-- Overview Tab -->
<div class="tab-pane fade {{ collect(['college', 'programme', 'employer', 'year', 'opportunity_type', 'category'])->contains(fn($key) => request($key) !== null && request($key) !== '') ? '' : 'show active' }}"
    id="tab-overview" role="tabpanel">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h5 class="mb-1">Overview Report</h5>
            <p class="text-muted small mb-0">Jobs, users, and application activity at a glance.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.export', ['report' => 'overview', 'format' => 'pdf']) }}"
                class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf-o"></i> Download
                PDF</a>
            <a href="{{ route('admin.reports.export', ['report' => 'overview', 'format' => 'excel']) }}"
                class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel-o"></i> Download
                Excel</a>
        </div>
    </div>
    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Job Report</h5>
            <a href="{{ route('admin.jobs') }}" class="btn btn-sm btn-outline-primary">Review
                Jobs</a>
        </div>

        <!-- Total Jobs -->
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

        <!-- Approval Pending Jobs -->
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

        <!-- Open Jobs -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Open Jobs</h7>
                            <h2 class="text-success mb-0">{{ $activeJobs }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocked Jobs -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Blocked Jobs</h7>
                            <h2 class="text-danger mb-0">{{ $blockedJobs }}</h2>
                        </div>
                        <div class="text-danger" style="font-size: 2rem;"><i class="fa fa-ban"></i>
                        </div>
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

        <!-- Total Categories -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Categories</h7>
                            <h2 class="text-secondary mb-0">
                                {{ $collegeCategoryCounts->sum('categories_count') }}
                            </h2>
                        </div>
                        <div class="text-secondary" style="font-size: 2rem;"><i class="fa fa-th-list"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End of row -->

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Employer Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.employers') }}" class="btn btn-sm btn-outline-primary">Review
                    Employers</a>
            </div>
        </div>

        <!-- Total Employers -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Total Employers</h7>
                            <h2 class="text-success mb-0">{{ $totalEmployers }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-briefcase"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!--employers pending approval -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Approval Pending </h7>
                            <h2 class="text-warning mb-0">{{ $pendingEmployers }}</h2>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;"><i class="fa fa-hourglass-half"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Employers -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Active Employers</h7>
                            <h2 class="text-success mb-0">{{ $activeEmployers }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- blocked employers -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Blocked Employers</h7>
                            <h2 class="text-danger mb-0">{{ $blockedEmployers }}</h2>
                        </div>
                        <div class="text-danger" style="font-size: 2rem;"><i class="fa fa-ban"></i></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Organization Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.organizations.index') }}" class="btn btn-sm btn-outline-primary">Review
                    Organizations</a>
            </div>
        </div>

        <!-- Total Organizations -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Total Organizations</h7>
                            <h2 class="text-success mb-0">{{ $totalOrganizations }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-building"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending organization requests -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Approval Pending</h7>
                            <h2 class="text-warning mb-0">{{ $pendingOrganizationRequests }}</h2>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;"><i class="fa fa-hourglass-half"></i></div>
                    </div>
                </div>
            </div>
        </div>
       
        <!-- Organizations with job postings -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">With Job Postings</h7>
                            <h2 class="text-success mb-0">{{ $organizationsWithJobs }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-briefcase"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Student Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.students') }}" class="btn btn-sm btn-outline-primary">Review Students</a>
            </div>
        </div>
        <!-- Total Students -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Total Students</h7>
                            <h2 class="text-success mb-0">{{ $totalStudents }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-briefcase"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student pending approval -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Approval Pending </h7>
                            <h2 class="text-warning mb-0">{{ $pendingApprovalStudents }}</h2>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;"><i class="fa fa-hourglass-half"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Students -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Active Students</h7>
                            <h2 class="text-success mb-0">{{ $activeStudents }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- blocked students -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Blocked Students</h7>
                            <h2 class="text-danger mb-0">{{ $blockedStudents }}</h2>
                        </div>
                        <div class="text-danger" style="font-size: 2rem;"><i class="fa fa-ban"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Application Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.jobApplications') }}" class="btn btn-sm btn-outline-primary">Review
                    Applications</a>
            </div>
        </div>
        <!-- Total Applications -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Total Applications</h7>
                            <h2 class="text-success mb-0">{{ $totalApplications }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-briefcase"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active applications -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Active applications</h7>
                            <h2 class="text-warning mb-0">{{ $activeApplications }}</h2>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;"><i class="fa fa-hourglass-half"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placed applications -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Placed </h7>
                            <h2 class="text-success mb-0">{{ $placedApplications }}</h2>
                        </div>
                        <div class="text-success" style="font-size: 2rem;"><i class="fa fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unsuccessful Applications -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h7 class="text-muted mb-1">Unsuccessful</h7>
                            <h2 class="text-danger mb-0">{{ $unsuccessfulApplications }}</h2>
                        </div>
                        <div class="text-danger" style="font-size: 2rem;"><i class="fa fa-ban"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Active Internal Users</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Role</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Management (Read-only)</td>
                                <td class="text-end fw-semibold">{{ $activeManagementUsers }}</td>
                            </tr>
                            <tr>
                                <td>Administrators</td>
                                <td class="text-end fw-semibold">{{ $activeRegularAdmins }}</td>
                            </tr>
                            <tr>
                                <td>Super Administrators</td>
                                <td class="text-end fw-semibold">{{ $activeSuperAdmins }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>