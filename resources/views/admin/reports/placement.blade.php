<!-- Placement Tab -->
<div class="tab-pane fade {{ collect(['college', 'programme', 'employer', 'year', 'opportunity_type', 'category'])->contains(fn($key) => request($key) !== null && request($key) !== '') ? 'show active' : '' }}"
    id="tab-placement" role="tabpanel">
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Placement Rate Report</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.export', ['report' => 'placement', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                        class="btn btn-sm btn-outline-danger"> Download PDF</a>
                    <a href="{{ route('admin.reports.export', ['report' => 'placement', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                        class="btn btn-sm btn-outline-success"> Download Excel</a>
                    <a href="{{ route('admin.dashboard') }}"
                        class="btn btn-sm btn-outline-secondary">Clear filters</a>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3">
                <input type="hidden" name="tab" value="placement">
                <div class="col-md-6 col-lg-4">
                    <label for="college" class="form-label">College/Center</label>
                    <select name="college" id="college" class="form-select">
                        <option value="">All Colleges/Centers</option>
                        @foreach ($collegeOptions as $college)
                            <option value="{{ $college->id }}"
                                {{ (string) request('college') === (string) $college->id ? 'selected' : '' }}>
                                {{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="programme" class="form-label">Programme</label>
                    <select name="programme" id="programme" class="form-select">
                        <option value="">All programmes</option>
                        @foreach ($programmeOptions as $programme)
                            <option value="{{ $programme }}"
                                {{ request('programme') === $programme ? 'selected' : '' }}>
                                {{ $programme }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="employer" class="form-label">Employer</label>
                    <select name="employer" id="employer" class="form-select">
                        <option value="">All employers</option>
                        @foreach ($employerOptions as $employer)
                            <option value="{{ $employer->id }}"
                                {{ (string) request('employer') === (string) $employer->id ? 'selected' : '' }}>
                                {{ $employer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select">
                        <option value="">All years</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}"
                                {{ (string) request('year') === (string) $year ? 'selected' : '' }}>
                                {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="opportunity_type" class="form-label">Opportunity Type</label>
                    <select name="opportunity_type" id="opportunity_type" class="form-select">
                        <option value="">All opportunity types</option>
                        @foreach ($opportunityTypeOptions as $type)
                            <option value="{{ $type->id }}"
                                {{ (string) request('opportunity_type') === (string) $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="category" class="form-label">Job Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All job categories</option>
                        @foreach ($categoryOptions as $category)
                            <option value="{{ $category->id }}"
                                {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
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
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Applications</p>
                    <h2 class="mb-0">{{ number_format($placementTotalApplications) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Students Placed</p>
                    <h2 class="text-success mb-0">{{ number_format($placedApplications) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Placement Rate</p>
                    <h2 class="text-primary mb-0">{{ number_format($placementRate, 2) }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Placement Rate by College/Center</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>College/Center</th>
                        <th class="text-end">Placed</th>
                        <th class="text-end">Applications</th>
                        <th class="text-end">Placement Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collegePlacementReports as $report)
                        <tr>
                            <td>{{ $report->college_name }}</td>
                            <td class="text-end">{{ $report->placed_count }}</td>
                            <td class="text-end">{{ $report->application_count }}</td>
                            <td class="text-end fw-semibold">
                                {{ number_format($report->placement_rate, 2) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No placement
                                data
                                for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0">Interview Conversion Rate</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Interviewed</p>
                            <h3 class="mb-0">{{ number_format($interviewedApplications) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Placed</p>
                            <h3 class="text-success mb-0">
                                {{ number_format($interviewPlacedApplications) }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Interview &rarr; Placement</p>
                            <h3 class="text-primary mb-0">
                                {{ number_format($interviewConversionRate, 2) }}%
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-muted small mb-3">Of students who reached the interview stage,
                {{ number_format($interviewConversionRate, 2) }}% eventually secured placement.
            </p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Programme</th>
                            <th class="text-end">Interviewed</th>
                            <th class="text-end">Placed</th>
                            <th class="text-end">Interview &rarr; Placement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($interviewConversionByProgramme as $report)
                            <tr>
                                <td>{{ $report->degree }}</td>
                                <td class="text-end">{{ $report->interviewed_count }}</td>
                                <td class="text-end">{{ $report->placed_count }}</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($report->conversion_rate, 2) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No
                                    interview
                                    data for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

