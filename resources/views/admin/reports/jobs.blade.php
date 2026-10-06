<div class="card border-0 shadow mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Job-Level Reporting</h5>
        <div class="d-flex gap-2">
            @foreach (['pdf' => 'danger', 'excel' => 'success'] as $format => $color)
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['report' => 'jobs', 'format' => $format])) }}"
                    class="btn btn-sm btn-outline-{{ $color }}">Download {{ $format === 'pdf' ? 'PDF' : 'Excel' }}</a>
            @endforeach
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small">One row per job posting, ordered by organization. All job statuses and jobs with no applications are included.
            Application counts respect the dashboard's applicant and application-year filters; these filters do not exclude job postings.</p>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2">
            <input type="hidden" name="tab" value="employers">
            @foreach (request()->except(['tab', 'report_organization', 'funnel_job', 'page']) as $key => $value)
                @if (is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <div class="col-md-8">
                <label for="report_organization" class="form-label">Organization</label>
                <select id="report_organization" name="report_organization" class="form-select">
                    <option value="">All organizations</option>
                    @foreach ($jobReportOrganizationOptions as $organization)
                        <option value="{{ $organization->id }}" @selected((string) request('report_organization') === (string) $organization->id)>{{ $organization->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary">Apply</button>
                <a href="{{ route('admin.dashboard', array_merge(request()->except(['report_organization', 'funnel_job', 'page']), ['tab' => 'employers'])) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    @foreach (['Job ID', 'Organization', 'Job Title', 'Job Type', 'Location', 'Vacancies', 'Status', 'Posted', 'Closing Date', 'Applications'] as $heading)
                        <th scope="col">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($jobLevelReports as $report)
                    <tr>
                        @foreach ($report as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No job postings for the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
