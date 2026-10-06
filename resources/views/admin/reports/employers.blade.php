<!-- Employers & Funnel Tab -->
<div class="tab-pane fade" id="tab-employers" role="tabpanel">
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Organization-Level Reporting</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.export', ['report' => 'employer', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-danger"> Download PDF</a>
                <a href="{{ route('admin.reports.export', ['report' => 'employer', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-success"> Download Excel</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th class="text-end">Applications</th>
                        <th class="text-end">Interviewed</th>
                        <th class="text-end">Placed</th>
                        <th class="text-end">Rejected</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employerPerformanceReports as $report)
                        <tr>
                            <td>{{ $report->organization_name }}</td>
                            <td class="text-end">{{ number_format($report->application_count) }}
                            </td>
                            <td class="text-end">{{ number_format($report->interviewed_count) }}
                            </td>
                            <td class="text-end text-success">
                                {{ number_format($report->placed_count) }}
                            </td>
                            <td class="text-end text-danger">
                                {{ number_format($report->rejected_count) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No organization
                                data for
                                the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.reports.jobs')

    <div class="card border-0 shadow">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recruitment Funnel</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.export', ['report' => 'funnel', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-danger"> Download PDF</a>
                <a href="{{ route('admin.reports.export', ['report' => 'funnel', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-success"> Download Excel</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 mb-4">
                <input type="hidden" name="tab" value="employers">
                @foreach (request()->except(['tab', 'funnel_job', 'page']) as $key => $value)
                    @if (is_scalar($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <div class="col-md-8">
                    <label for="funnel_job" class="form-label">Job title for recruitment funnel</label>
                    <select name="funnel_job" id="funnel_job" class="form-select">
                        <option value="">All job postings{{ $selectedFunnelOrganization ? ' for '.$selectedFunnelOrganization->name : '' }}</option>
                        @foreach ($funnelJobOptions as $job)
                            <option value="{{ $job->id }}" @selected((string) request('funnel_job') === (string) $job->id)>
                                {{ $job->title }} (#{{ $job->id }}) - {{ $job->organization?->name ?? $job->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">View Funnel</button>
                    <a href="{{ route('admin.dashboard', array_merge(request()->except(['funnel_job', 'page']), ['tab' => 'employers'])) }}" class="btn btn-outline-secondary">Reset job</a>
                </div>
            </form>
            <p class="text-muted">Funnel for {{ $selectedFunnelJob ? $selectedFunnelJob->title.' (#'.$selectedFunnelJob->id.')' : 'all job postings' }}
                {{ $selectedFunnelOrganization ? ' at '.$selectedFunnelOrganization->name : '' }}.
                Dashboard applicant and application-year filters also apply.</p>
            @forelse($funnelReports as $index => $stage)
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">{{ $stage['name'] }}</span>
                        <span>{{ number_format($stage['count']) }} <small
                                class="text-muted">({{ number_format($stage['conversion_from_start'], 2) }}%
                                of
                                {{ $funnelReports[0]['name'] }})</small></span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                            style="width: {{ $stage['conversion_from_start'] }}%;"
                            aria-valuenow="{{ $stage['conversion_from_start'] }}"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                @if (!$loop->last)
                    @php $nextStage = $funnelReports[$index + 1]; @endphp
                    <div class="text-center text-muted small mb-2">
                        &darr; dropped off: {{ number_format($nextStage['drop_off']) }}
                        ({{ number_format($nextStage['drop_off_rate'], 2) }}%)
                    </div>
                @endif
            @empty
                <p class="text-muted text-center py-4 mb-0">No funnel data for the selected
                    filters.
                </p>
            @endforelse
        </div>
    </div>
</div>
