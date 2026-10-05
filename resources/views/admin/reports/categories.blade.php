<!-- Job Categories Tab -->
<div class="tab-pane fade" id="tab-categories" role="tabpanel">
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Categories per College/Center</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>College/Center</th>
                        <th>Code</th>
                        <th class="text-end">Categories</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collegeCategoryCounts as $college)
                        <tr>
                            <td>{{ $college->name }}</td>
                            <td>{{ $college->code }}</td>
                            <td class="text-end">{{ $college->categories_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No
                                Colleges/Centers
                                found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2">Total</td>
                        <td class="text-end">{{ $collegeCategoryCounts->sum('categories_count') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Job Categories Report</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.export', ['report' => 'categories', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-danger"> Download PDF</a>
                <a href="{{ route('admin.reports.export', ['report' => 'categories', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="btn btn-sm btn-outline-success"> Download Excel</a>
            </div>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.dashboard') }}"
                class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="categories">
                <div class="col-md-6 col-lg-4">
                    <label for="category_college" class="form-label">Filter by
                        College/Center</label>
                    <select name="category_college" id="category_college" class="form-select">
                        <option value="">All Colleges/Centers</option>
                        @foreach ($collegeOptions as $college)
                            <option value="{{ $college->id }}"
                                {{ (string) request('category_college') === (string) $college->id ? 'selected' : '' }}>
                                {{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>
                        Apply</button>
                    <a href="{{ route('admin.dashboard') }}?tab=categories"
                        class="btn btn-secondary ms-1"><i class="fa fa-times"></i> Clear</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>College/Center</th>
                        <th class="text-end">Jobs</th>
                        <th class="text-end">Applications</th>
                        <th class="text-end">Placed</th>
                        <th class="text-end">Rejected</th>
                        <th class="text-end">Placement Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedCategories = $categoryReports->groupBy('college_name');
                    @endphp
                    @forelse($groupedCategories as $collegeName => $reports)
                        <tr class="table-light">
                            <td colspan="7"
                                class="fw-bold text-uppercase small text-secondary">
                                <i class="fa fa-university me-1"></i> {{ $collegeName }}
                            </td>
                        </tr>
                        @foreach ($reports as $report)
                            <tr>
                                <td class="fw-semibold ps-4">{{ $report->name }}</td>
                                <td class="text-muted">{{ $report->college_name }}</td>
                                <td class="text-end">{{ number_format($report->job_count) }}</td>
                                <td class="text-end">
                                    {{ number_format($report->application_count) }}
                                </td>
                                <td class="text-end text-success">
                                    {{ number_format($report->placed_count) }}
                                </td>
                                <td class="text-end text-danger">
                                    {{ number_format($report->rejected_count) }}
                                </td>
                                <td class="text-end">
                                    <span
                                        class="badge {{ $report->placement_rate >= 50 ? 'bg-success' : ($report->placement_rate >= 25 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ number_format($report->placement_rate, 2) }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No category
                                data
                                available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

