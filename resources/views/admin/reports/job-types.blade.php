<div class="tab-pane fade" id="tab-job-types" role="tabpanel" aria-labelledby="tab-job-types-btn">
    <h5 class="mb-3">Job Type Reports</h5>
    <p class="text-muted small">All jobs are counted, including pending, blocked, and closed jobs.
        Colleges are determined by the job's category, not the applicant's college.
        Placement rate is placed applications divided by total applications.</p>

    <form method="GET" action="{{ route('admin.dashboard') }}" class="card border-0 shadow mb-4">
        <div class="card-body row g-3">
            <input type="hidden" name="tab" value="job-types">
            <div class="col-md-5">
                <label for="report_job_type" class="form-label">College drill-down job type</label>
                <select name="report_job_type" id="report_job_type" class="form-select">
                    @if (!$selectedReportJobType)
                        <option value="">Select a job type</option>
                    @endif
                    @foreach ($jobTypeReportOptions as $type)
                        <option value="{{ $type->id }}" @selected($selectedReportJobType?->id === $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label for="report_college" class="form-label">Job category's College/Center</label>
                <select name="report_college" id="report_college" class="form-select">
                    <option value="">All colleges/centers</option>
                    @foreach ($jobTypeCollegeOptions as $college)
                        <option value="{{ $college->id }}" @selected((string) request('report_college') === (string) $college->id)>
                            {{ $college->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('admin.dashboard', ['tab' => 'job-types']) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    @php
        $reportFilters = request()->only(['report_college']);
        if ($selectedReportJobType) {
            $reportFilters['report_job_type'] = $selectedReportJobType->id;
        }
    @endphp
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h6 class="mb-0">Summary by Job Type</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.export', array_merge($reportFilters, ['report' => 'job-types', 'format' => 'pdf'])) }}"
                class="btn btn-sm btn-outline-danger">Download PDF</a>
            <a href="{{ route('admin.reports.export', array_merge($reportFilters, ['report' => 'job-types', 'format' => 'excel'])) }}"
                class="btn btn-sm btn-outline-success">Download Excel</a>
        </div>
    </div>
    <div class="card border-0 shadow mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Job Type</th>
                        <th class="text-end">Jobs</th>
                        <th class="text-end">Applications</th>
                        <th class="text-end">Placed</th>
                        <th class="text-end">Rejected</th>
                        <th class="text-end">Placement Rate</th>
                        <th>Drill-down</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jobTypeReports as $report)
                        <tr>
                            <td class="fw-semibold">{{ $report->name }}</td>
                            <td class="text-end">{{ $report->job_count }}</td>
                            <td class="text-end">{{ $report->application_count }}</td>
                            <td class="text-end text-success">{{ $report->placed_count }}</td>
                            <td class="text-end">{{ $report->rejected_count }}</td>
                            <td class="text-end">{{ number_format($report->placement_rate, 2) }}%</td>
                            <td><a href="{{ route('admin.dashboard', array_merge($reportFilters, ['tab' => 'job-types', 'report_job_type' => $report->id])) }}#job-type-colleges">
                                By College</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No job types configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="job-type-colleges" class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h6 class="mb-0">{{ $selectedReportJobType?->name ?? 'Job Type' }} by College/Center</h6>
        @if ($selectedReportJobType)
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.export', array_merge($reportFilters, ['report' => 'job-type-colleges', 'format' => 'pdf'])) }}"
                    class="btn btn-sm btn-outline-danger">Download PDF</a>
                <a href="{{ route('admin.reports.export', array_merge($reportFilters, ['report' => 'job-type-colleges', 'format' => 'excel'])) }}"
                    class="btn btn-sm btn-outline-success">Download Excel</a>
            </div>
        @endif
    </div>
    @if (!$selectedReportJobType)
        <div class="alert alert-info">Select a job type above to view its college breakdown.</div>
    @else
        <div class="card border-0 shadow">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>College/Center</th>
                            <th class="text-end">Jobs</th>
                            <th class="text-end">Applications</th>
                            <th class="text-end">Placed</th>
                            <th class="text-end">Rejected</th>
                            <th class="text-end">Placement Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobTypeCollegeReports as $report)
                            <tr>
                                <td class="fw-semibold">{{ $report->name }}</td>
                                <td class="text-end">{{ $report->job_count }}</td>
                                <td class="text-end">{{ $report->application_count }}</td>
                                <td class="text-end text-success">{{ $report->placed_count }}</td>
                                <td class="text-end">{{ $report->rejected_count }}</td>
                                <td class="text-end">{{ number_format($report->placement_rate, 2) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No colleges or jobs to report.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
