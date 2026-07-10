@extends('front.layouts.app')

@section('main')
    <section class="section-4 bg-2">
        <div class="container pt-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('front.jobs') }}"><i class="fa fa-arrow-left"
                                        aria-hidden="true"></i> &nbsp;Back to Jobs</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="container job_details_area">
            <div class="row pb-5">
                <div class="col-md-8">
                    @include('front.message')
                    <div class="card shadow border-0">
                        <div class="job_details_header">
                            <div class="single_jobs white-bg d-flex justify-content-between">
                                <div class="jobs_left d-flex align-items-center">

                                    <div class="jobs_conetent">
                                        <a href="#">
                                            <h4>{{ $job->title }}</h4>
                                        </a>
                                        <div class="links_locat d-flex align-items-center">
                                            <div class="location">
                                                <p> <i class="fa fa-map-marker"></i> {{ $job->location }}</p>
                                            </div>
                                            <div class="location">
                                                <p> <i class="fa fa-clock-o"></i> {{ $job->jobType->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if (Auth::check())
                                    <div class="jobs_right">
                                        <div class="apply_now {{ $count == 1 ? 'saved-job' : '' }}">
                                            <a class="heart_mark" href="#" onclick="saveJob({{ $job->id }})"> <i
                                                    class="fa fa-heart-o" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="descript_wrap white-bg">
                            @if (!empty($job->description))
                                <div class="single_wrap">
                                    <h4>Job description</h4>
                                    <p>{!! nl2br($job->description) !!}</p>
                                </div>
                            @endif

                            @if (!empty($job->responsibilities))
                                <div class="single_wrap">
                                    <h4>Responsibility</h4>
                                    <p>{!! nl2br($job->responsibilities) !!}</p>
                                </div>
                            @endif

                            @if (!empty($job->qualifications))
                                <div class="single_wrap">
                                    <h4>Qualifications</h4>
                                    <p>{!! nl2br($job->qualifications) !!}</p>
                                </div>
                            @endif

                            @if (!empty($job->benefits))
                                <div class="single_wrap">
                                    <h4>Benefits</h4>
                                    <p>{!! nl2br($job->benefits) !!}</p>
                                </div>
                            @endif

                            <div class="border-bottom"></div>
                            <div class="pt-3 text-end">
                                @if (Auth::check())
                                    <a href="#" onclick="saveJob({{ $job->id }})"
                                        class="btn btn-secondary">Save</a>
                                @else
                                    <a href="{{ route('account.login') }}" class="btn btn-secondary">Login to Save</a>
                                @endif
                                <!-- apply if user is logged in -->
                                @if (Auth::check())
                                    <a href="#" class="btn btn-primary" onclick="openApplyModal({{ $job->id }})">Apply</a>
                                @else
                                    <a href="{{ route('account.login') }}" class="btn btn-primary">Login to Apply</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if (Auth::user() && Auth::user()->id == $job->user_id)
                        <div class="card shadow border-0 mt-4">
                            <div class="job_details_header">
                                <div class="single_jobs white-bg d-flex justify-content-between">
                                    <div class="jobs_left d-flex align-items-center">

                                        <div class="jobs_conetent">
                                            <h4>Applicants</h4>
                                        </div>
                                    </div>
                                    <div class="jobs_right"> </div>
                                </div>
                            </div>

                            <div class="descript_wrap white-bg">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Application</th>
                                            <th>Resume</th>
                                            <th>Certificates</th>
                                            <th>Applied Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($applications->isNotEmpty())
                                            @foreach ($applications as $application)
                                                <tr>
                                                    <td>{{ $application->user->name }}</td>
                                                    <td>{{ $application->user->email }}</td>
                                                    <td>{{ $application->user->mobile ?? 'N/A' }}</td>
                                                    <td>
                                                        @if(!empty($application->application_file))
                                                            @php
                                                                $path = $application->application_file;
                                                                $disk = Storage::disk('applications')->exists($path)
                                                                    ? 'applications'
                                                                    : (Storage::disk('public')->exists($path) ? 'public' : 'applications');
                                                                $ext = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
                                                                $size = 0;
                                                                try {
                                                                    $size = Storage::disk($disk)->size($path);
                                                                } catch (Exception $e) {
                                                                    $size = 0;
                                                                }
                                                                if ($size >= 1048576) {
                                                                    $sizeText = round($size / 1048576, 2) . ' MB';
                                                                } elseif ($size >= 1024) {
                                                                    $sizeText = round($size / 1024, 2) . ' KB';
                                                                } else {
                                                                    $sizeText = $size . ' B';
                                                                }
                                                            @endphp
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'application']) }}" target="_blank" download>
                                                                <i class="fa fa-file" aria-hidden="true"></i> {{ $ext }} ({{ $sizeText }})
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($application->resume_file))
                                                            @php
                                                                $path = $application->resume_file;
                                                                $disk = Storage::disk('applications')->exists($path)
                                                                    ? 'applications'
                                                                    : (Storage::disk('public')->exists($path) ? 'public' : 'applications');
                                                                $ext = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
                                                                $size = 0;
                                                                try {
                                                                    $size = Storage::disk($disk)->size($path);
                                                                } catch (Exception $e) {
                                                                    $size = 0;
                                                                }
                                                                if ($size >= 1048576) {
                                                                    $sizeText = round($size / 1048576, 2) . ' MB';
                                                                } elseif ($size >= 1024) {
                                                                    $sizeText = round($size / 1024, 2) . ' KB';
                                                                } else {
                                                                    $sizeText = $size . ' B';
                                                                }
                                                            @endphp
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'resume']) }}" target="_blank" download>
                                                                <i class="fa fa-file" aria-hidden="true"></i> {{ $ext }} ({{ $sizeText }})
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($application->certificates_file))
                                                            @php $certs = json_decode($application->certificates_file, true) ?? []; @endphp
                                                            @if(!empty($certs))
                                                                @foreach($certs as $cert)
                                                                    @php
                                                                        $certExt = strtoupper(pathinfo($cert, PATHINFO_EXTENSION));
                                                                        $disk = Storage::disk('applications')->exists($cert)
                                                                            ? 'applications'
                                                                            : (Storage::disk('public')->exists($cert) ? 'public' : 'applications');
                                                                        $certSize = 0;
                                                                        try { $certSize = Storage::disk($disk)->size($cert); } catch (Exception $e) { $certSize = 0; }
                                                                        if ($certSize >= 1048576) { $certSizeText = round($certSize / 1048576, 2) . ' MB'; }
                                                                        elseif ($certSize >= 1024) { $certSizeText = round($certSize / 1024, 2) . ' KB'; }
                                                                        else { $certSizeText = $certSize . ' B'; }
                                                                    @endphp
                                                                    <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'certificate']) . '?file=' . urlencode(base64_encode($cert)) }}" target="_blank" download>
                                                                        <i class="fa fa-file" aria-hidden="true"></i> {{ $certExt }} ({{ $certSizeText }})
                                                                    </a>@if(!$loop->last), @endif
                                                                @endforeach
                                                            @else
                                                                N/A
                                                            @endif
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ optional($application->applied_at)->format('d M, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center">No applicants found.</td>
                                            </tr>
                                        @endif

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="card shadow border-0">
                        <div class="job_sumary">
                            <div class="summery_header pb-1 pt-4">
                                <h3>Job Summery</h3>
                            </div>
                            <div class="job_content pt-3">
                                <ul>
                                    <li>Published on: <span>{{ $job->created_at->format('d M, Y') }}</span></li>
                                    <li>Vacancy: <span>{{ $job->vacancy }}</span></li>
                                    @if (!empty($job->salary))
                                        <li>Salary: <span>{{ $job->salary }}</span></li>
                                    @endif
                                    @if (!empty($job->location))
                                        <li>Location: <span>{{ $job->location }}</span></li>
                                    @endif
                                    <li>Job Nature: <span>{{ $job->jobType->name ?? 'N/A' }}</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow border-0 my-4">
                        <div class="job_sumary">
                            <div class="summery_header pb-1 pt-4">
                                <h3>Company Details</h3>
                            </div>
                            <div class="job_content pt-3">
                                <ul>
                                    @if (!empty($job->company_name))
                                        <li>Name: <span>{{ $job->company_name }}</span></li>
                                    @endif
                                    @if (!empty($job->company_location))
                                        <li>Location: <span>{{ $job->company_location }}</span></li>
                                    @endif
                                    @if (!empty($job->company_website))
                                        <li>Company Website: <span> <a href="{{ $job->company_website }}"
                                                    target="_blank">{{ $job->company_website }}</a></span></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Apply Modal -->
    <div class="modal fade" id="applyModal" tabindex="-1" aria-labelledby="applyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="applyForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="applyModalLabel">Apply for Job</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="job_id" id="applyJobId" value="">
                        <div class="mb-3">
                            <label class="form-label">Application Letter (PDF/DOC)</label>
                            <input type="file" name="application" class="form-control" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resume (PDF/DOC)</label>
                            <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Certificates (you can select multiple)</label>
                            <input type="file" name="certificates[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('customJS')
    <script type="text/javascript">
        function openApplyModal(jobId) {
            $('#applyJobId').val(jobId);
            $('#applyModal').modal('show');
        }

        $(document).on('submit', '#applyForm', function(e) {
            e.preventDefault();
            var form = document.getElementById('applyForm');
            var formData = new FormData(form);

            $.ajax({
                url: '{{ route('applyJob') }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#applyModal').modal('hide');
                    let alertClass = response.status ? 'alert-success' : 'alert-danger';
                    let alertBox = `<div class="alert ${alertClass} alert-dismissible fade show mt-3" role="alert">${response.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    $(".col-md-8").prepend(alertBox);
                },
                error: function(xhr) {
                    let msg = 'An error occurred while applying for the job.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    let alertBox = `<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    $(".col-md-8").prepend(alertBox);
                }
            });
        });

        function saveJob(id) {
            // Send AJAX request to save the job
            $.ajax({
                url: '{{ route('saveJob') }}',
                type: 'POST',
                data: {
                    id: id
                }, // Fix key to match controller
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    // window.location.href = '{{ url()->current() }}'; // Redirect to current page after successful application
                    let alertClass = response.status ? 'alert-success' : 'alert-danger';
                    let alertBox = `<div class="alert ${alertClass} alert-dismissible fade show mt-3" role="alert">
                                                        ${response.message}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    </div>`;
                    // Insert message at the top of the main column
                    $(".col-md-8").prepend(alertBox);
                },
                error: function(xhr) {
                    let alertBox =
                        `<div class=\"alert alert-danger alert-dismissible fade show mt-3\" role=\"alert\">An error occurred while applying for the job.<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>`;
                    $(".col-md-8").prepend(alertBox);
                }
            });
        }
    </script>
@endsection
