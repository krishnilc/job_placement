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

        <x-dashboard.metric-card label="Total Jobs" :value="$totalJobs" color="success" icon="fa-briefcase" />

        <x-dashboard.metric-card label="Approval Pending" :value="$pendingJobs" color="warning" icon="fa-hourglass-half" />

        <x-dashboard.metric-card label="Open Jobs" :value="$activeJobs" color="success" icon="fa-check-circle" />

        <x-dashboard.metric-card label="Blocked Jobs" :value="$blockedJobs" color="danger" icon="fa-ban" />
        <x-dashboard.metric-card label="Featured Jobs" :value="$featuredJobs" color="primary" icon="fa-star" />

        <x-dashboard.metric-card label="Categories" :value="$collegeCategoryCounts->sum('categories_count')" color="secondary" icon="fa-th-list" />
    </div>

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Employer Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.employers') }}" class="btn btn-sm btn-outline-primary">Review
                    Employers</a>
            </div>
        </div>

        <x-dashboard.metric-card label="Total Employers" :value="$totalEmployers" color="success" icon="fa-briefcase" />

        <x-dashboard.metric-card label="Approval Pending" :value="$pendingEmployers" color="warning" icon="fa-hourglass-half" />

        <x-dashboard.metric-card label="Active Employers" :value="$activeEmployers" color="success" icon="fa-check-circle" />

        <x-dashboard.metric-card label="Blocked Employers" :value="$blockedEmployers" color="danger" icon="fa-ban" />

    </div>

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Organization Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.organizations.index') }}" class="btn btn-sm btn-outline-primary">Review
                    Organizations</a>
            </div>
        </div>

        <x-dashboard.metric-card label="Total Organizations" :value="$totalOrganizations" color="success" icon="fa-building" />

        <x-dashboard.metric-card label="Approval Pending" :value="$pendingOrganizationRequests" color="warning" icon="fa-hourglass-half" />

        <x-dashboard.metric-card label="With Job Postings" :value="$organizationsWithJobs" color="success" icon="fa-briefcase" />
    </div>

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Student Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.students') }}" class="btn btn-sm btn-outline-primary">Review Students</a>
            </div>
        </div>

        <x-dashboard.metric-card label="Total Students" :value="$totalStudents" color="success" icon="fa-briefcase" />

        <x-dashboard.metric-card label="Approval Pending" :value="$pendingApprovalStudents" color="warning" icon="fa-hourglass-half" />

        <x-dashboard.metric-card label="Active Students" :value="$activeStudents" color="success" icon="fa-check-circle" />

        <x-dashboard.metric-card label="Blocked Students" :value="$blockedStudents" color="danger" icon="fa-ban" />
    </div>

    <div class="row mx-0 p-3 mb-4 bg-white rounded-3 shadow-sm overview-report-group">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Application Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.jobApplications') }}" class="btn btn-sm btn-outline-primary">Review
                    Applications</a>
            </div>
        </div>

        <x-dashboard.metric-card label="Total Applications" :value="$totalApplications" color="success" icon="fa-briefcase" />

        <x-dashboard.metric-card label="Active applications" :value="$activeApplications" color="warning" icon="fa-hourglass-half" />

        <x-dashboard.metric-card label="Placed" :value="$placedApplications" color="success" icon="fa-check-circle" />

        <x-dashboard.metric-card label="Unsuccessful" :value="$unsuccessfulApplications" color="danger" icon="fa-ban" />
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