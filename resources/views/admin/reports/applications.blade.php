<!-- Applications Tab -->
<div class="tab-pane fade" id="tab-applications" role="tabpanel">
    <!-- Application Status Reports -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Application Status Reports</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.export', ['report' => 'applications', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf-o"></i>
                    Download PDF</a>
                <a href="{{ route('admin.reports.export', ['report' => 'applications', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel-o"></i>
                    Download Excel</a>
                <a href="{{ route('admin.jobApplications') }}"
                    class="btn btn-sm btn-outline-primary">Review Applications</a>
            </div>
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
                        @foreach ($applicationStatusReports as $report)
                            @php
                                $percentage =
                                    $applicationStatusReportTotal > 0
                                        ? round(
                                            ($report->application_count /
                                                $applicationStatusReportTotal) *
                                                100,
                                            2,
                                        )
                                        : 0;
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $report->name }}</td>
                                <td><span
                                        class="badge bg-light text-dark border">{{ $report->category }}</span>
                                </td>
                                <td class="text-end">{{ $report->application_count }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-primary"
                                                role="progressbar"
                                                style="width: {{ $percentage }}%;"
                                                aria-valuenow="{{ $percentage }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small
                                            class="text-muted text-nowrap">{{ number_format($percentage, 2) }}%</small>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold table-light">
                            <td>Total</td>
                            <td></td>
                            <td class="text-end">{{ $applicationStatusReportTotal }}</td>
                            <td class="text-end">
                                <small class="text-muted text-nowrap">100.00%</small>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- <div class="card border-0 shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0">Recent Job Applications</h5>
        </div>
        <div class="card-body">
            @if ($recentApplications->count() > 0)
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
                            @foreach ($recentApplications as $application)
                                <tr>
                                    <td><strong>{{ $application->job->title ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $application->user->name ?? 'N/A' }}</td>
                                    <td>{{ $application->job->company_name ?? 'N/A' }}</td>
                                    <td>{{ $application->applicationStatus?->name ?? 'Submitted' }}
                                    </td>
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
            <a href="{{ route('admin.jobApplications') }}" class="btn btn-primary">View All
                Applications</a>
        </div>
    </div> --}}
</div>

