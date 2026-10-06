@extends('front.layouts.app')

@section('main')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-tabs.css') }}">
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('admin.sidebar')
                </div>
                <div class="col-lg-9">
                    @include('front.message')

                    <div class="dashboard-report-navigation mb-4">
                        <p class="dashboard-navigation-label" id="dashboard-navigation-label">Dashboard Reports</p>
                        <ul class="nav nav-pills row g-2 mx-0 mb-0" id="dashboardMainTabs" role="tablist"
                            aria-labelledby="dashboard-navigation-label">
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link {{ collect(['college', 'programme', 'employer', 'year', 'opportunity_type', 'category'])->contains(fn($key) => request($key) !== null && request($key) !== '') ? '' : 'active' }} w-100 h-100 text-center"
                                    id="tab-overview-btn" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button"
                                    role="tab"><i class="fa fa-th-large"
                                        aria-hidden="true"></i><span>Overview</span></button></li>
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link w-100 h-100 text-center" id="tab-applications-btn" data-bs-toggle="tab"
                                    data-bs-target="#tab-applications" type="button" role="tab"><i
                                        class="fa fa-file-text-o" aria-hidden="true"></i><span>Applications</span></button>
                            </li>
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link {{ collect(['college', 'programme', 'employer', 'year', 'opportunity_type', 'category'])->contains(fn($key) => request($key) !== null && request($key) !== '') ? 'active' : '' }} w-100 h-100 text-center"
                                    id="tab-placement-btn" data-bs-toggle="tab" data-bs-target="#tab-placement"
                                    type="button" role="tab"><i class="fa fa-graduation-cap"
                                        aria-hidden="true"></i><span>Placement</span></button></li>
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link w-100 h-100 text-center" id="tab-job-types-btn" data-bs-toggle="tab"
                                    data-bs-target="#tab-job-types" type="button" role="tab"><i class="fa fa-briefcase"
                                        aria-hidden="true"></i><span>Job Types</span></button></li>
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link w-100 h-100 text-center" id="tab-categories-btn" data-bs-toggle="tab"
                                    data-bs-target="#tab-categories" type="button" role="tab"><i class="fa fa-tags"
                                        aria-hidden="true"></i><span>Categories</span></button></li>
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link w-100 h-100 text-center" id="tab-rejection-btn" data-bs-toggle="tab"
                                    data-bs-target="#tab-rejection" type="button" role="tab"><i
                                        class="fa fa-line-chart" aria-hidden="true"></i><span>Rejection
                                        Trends</span></button></li>
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link w-100 h-100 text-center" id="tab-employers-btn" data-bs-toggle="tab"
                                    data-bs-target="#tab-employers" type="button" role="tab"><i
                                        class="fa fa-building-o" aria-hidden="true"></i><span>Employers &amp;
                                        Funnel</span></button></li>
                            <li class="nav-item col-6 col-md-3" role="presentation"><button
                                    class="nav-link w-100 h-100 text-center" id="tab-metrics-btn" data-bs-toggle="tab"
                                    data-bs-target="#tab-metrics" type="button" role="tab"><i class="fa fa-bar-chart"
                                        aria-hidden="true"></i><span>Reporting Metrics</span></button></li>

                        </ul>
                    </div>

                    <div class="tab-content" id="dashboardMainTabsContent">
                        @include('admin.reports.job-types')
                        @include('admin.reports.overview')
                        @include('admin.reports.placement')
                        @include('admin.reports.categories')
                        @include('admin.reports.rejection')
                        @include('admin.reports.employers')
                        @include('admin.reports.metrics')
                        @include('admin.reports.applications')
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script src="{{ asset('assets/js/dashboard-tabs.js') }}"></script>
@endsection
