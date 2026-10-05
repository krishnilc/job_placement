<!-- Reporting Metrics Tab -->
<div class="tab-pane fade" id="tab-metrics" role="tabpanel">
    <div class="card border-0 shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0">Application-Level vs Student-Level Metrics</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small">Application counts can overstate outcomes since one
                student may submit many applications. Both views are tracked separately for
                accurate institutional reporting.</p>
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6>Application-Level Metrics</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td>Total Applications</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($applicationMetrics['total_applications']) }}
                                </td>
                            </tr>
                            <tr>
                                <td>Shortlisting Rate</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($applicationMetrics['shortlisting_rate'], 2) }}%
                                </td>
                            </tr>
                            <tr>
                                <td>Interview Conversion</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($applicationMetrics['interview_conversion_rate'], 2) }}%
                                </td>
                            </tr>
                            <tr>
                                <td>Rejection Rate</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($applicationMetrics['rejection_rate'], 2) }}%
                                </td>
                            </tr>
                            <tr>
                                <td>Offer Conversion</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($applicationMetrics['offer_conversion_rate'], 2) }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Student-Level Metrics</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td>Students Seeking Employment</td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($studentMetrics['students_seeking_employment']) }}
                                </td>
                            </tr>
                            <tr>
                                <td>Students Successfully Placed</td>
                                <td class="text-end fw-semibold text-success">
                                    {{ number_format($studentMetrics['students_successfully_placed']) }}
                                </td>
                            </tr>
                            <tr>
                                <td>Graduate Employment Rate</td>
                                <td class="text-end fw-semibold text-primary">
                                    {{ number_format($studentMetrics['graduate_employment_rate'], 2) }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mb-0">View Industrial Attachment (IA) jobs,
                        applications, and placements by college in
                        <a href="{{ route('admin.dashboard', ['tab' => 'job-types']) }}">Job Type
                            Reports</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

