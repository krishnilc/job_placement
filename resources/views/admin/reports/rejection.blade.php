<!-- Rejection Trends Tab -->
<div class="tab-pane fade" id="tab-rejection" role="tabpanel">
    <div class="card border-0 shadow">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Applications</p>
                            <h3 class="mb-0">{{ number_format($placementTotalApplications) }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Rejected</p>
                            <h3 class="text-danger mb-0">
                                {{ number_format($rejectedApplications) }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Rejection Rate</p>
                            <h3 class="text-primary mb-0">
                                {{ number_format($rejectionRate, 2) }}%
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Yearly Application Funnel</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.export', ['report' => 'rejection', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                        class="btn btn-sm btn-outline-danger"> Download PDF</a>
                    <a href="{{ route('admin.reports.export', ['report' => 'rejection', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                        class="btn btn-sm btn-outline-success"> Download Excel</a>
                </div>
            </div>
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th class="text-end">Submitted</th>
                            <th class="text-end">Shortlisted</th>
                            <th class="text-end">Interviewed</th>
                            <th class="text-end">Placed</th>
                            <th class="text-end">Rejected</th>
                            <th class="text-end">Withdrawn</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($yearlyFunnelReports as $row)
                            <tr>
                                <td>{{ $row['year'] }}</td>
                                <td class="text-end">{{ number_format($row['submitted']) }}</td>
                                <td class="text-end">{{ number_format($row['shortlisted']) }}
                                </td>
                                <td class="text-end">{{ number_format($row['interviewed']) }}
                                </td>
                                <td class="text-end text-success">
                                    {{ number_format($row['placed']) }}
                                </td>
                                <td class="text-end text-danger">
                                    {{ number_format($row['rejected']) }}
                                </td>
                                <td class="text-end">{{ number_format($row['withdrawn']) }}</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($row['total']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No
                                    application
                                    data for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <ul class="nav nav-tabs" id="rejectionTrendsTabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active"
                        id="rej-year-tab" data-bs-toggle="tab" data-bs-target="#rej-year"
                        type="button" role="tab">Year</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link"
                        id="rej-month-tab" data-bs-toggle="tab" data-bs-target="#rej-month"
                        type="button" role="tab">Month</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link"
                        id="rej-college-tab" data-bs-toggle="tab" data-bs-target="#rej-college"
                        type="button" role="tab">College/Center</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link"
                        id="rej-programme-tab" data-bs-toggle="tab"
                        data-bs-target="#rej-programme" type="button"
                        role="tab">Programme</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link"
                        id="rej-category-tab" data-bs-toggle="tab" data-bs-target="#rej-category"
                        type="button" role="tab">Job
                        Category</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link"
                        id="rej-employer-tab" data-bs-toggle="tab" data-bs-target="#rej-employer"
                        type="button" role="tab">Employer</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link"
                        id="rej-type-tab" data-bs-toggle="tab" data-bs-target="#rej-type"
                        type="button" role="tab">Opportunity Type</button></li>
            </ul>
            <div class="tab-content border border-top-0 p-3" id="rejectionTrendsTabsContent">
                <div class="tab-pane fade show active" id="rej-year" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Rejected</th>
                                    <th class="text-end">Rejection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectionByYear as $report)
                                    <tr>
                                        <td>{{ $report->year }}</td>
                                        <td class="text-end">{{ $report->application_count }}
                                        </td>
                                        <td class="text-end">{{ $report->rejected_count }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($report->rejection_rate, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No
                                            data
                                            for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="rej-month" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Rejected</th>
                                    <th class="text-end">Rejection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectionByMonth as $report)
                                    <tr>
                                        <td>{{ $report->month }}</td>
                                        <td class="text-end">{{ $report->application_count }}
                                        </td>
                                        <td class="text-end">{{ $report->rejected_count }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($report->rejection_rate, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No
                                            data
                                            for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="rej-college" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>College/Center</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Rejected</th>
                                    <th class="text-end">Rejection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectionByCollege as $report)
                                    <tr>
                                        <td>{{ $report->college_name }}</td>
                                        <td class="text-end">{{ $report->application_count }}
                                        </td>
                                        <td class="text-end">{{ $report->rejected_count }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($report->rejection_rate, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No
                                            data
                                            for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="rej-programme" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Programme</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Rejected</th>
                                    <th class="text-end">Rejection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectionByProgramme as $report)
                                    <tr>
                                        <td>{{ $report->degree }}</td>
                                        <td class="text-end">{{ $report->application_count }}
                                        </td>
                                        <td class="text-end">{{ $report->rejected_count }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($report->rejection_rate, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No
                                            data
                                            for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="rej-category" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Job Category</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Rejected</th>
                                    <th class="text-end">Rejection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectionByCategory as $report)
                                    <tr>
                                        <td>{{ $report->category }}</td>
                                        <td class="text-end">{{ $report->application_count }}
                                        </td>
                                        <td class="text-end">{{ $report->rejected_count }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($report->rejection_rate, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No
                                            data
                                            for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="rej-employer" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Employer</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Rejected</th>
                                    <th class="text-end">Rejection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectionByEmployer as $report)
                                    <tr>
                                        <td>{{ $report->employer }}</td>
                                        <td class="text-end">{{ $report->application_count }}
                                        </td>
                                        <td class="text-end">{{ $report->rejected_count }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($report->rejection_rate, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No
                                            data
                                            for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="rej-type" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Opportunity Type</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Rejected</th>
                                    <th class="text-end">Rejection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectionByOpportunityType as $report)
                                    <tr>
                                        <td>{{ $report->opportunity_type }}</td>
                                        <td class="text-end">{{ $report->application_count }}
                                        </td>
                                        <td class="text-end">{{ $report->rejected_count }}</td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($report->rejection_rate, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No
                                            data
                                            for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

