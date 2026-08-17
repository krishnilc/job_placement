@extends('front.layouts.app')

@section('main')
 <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Job Applications</li>
                    </ol>
                </nav>
            </div>
        </div>

            <div class="row">
                <div class="col-lg-3">
                    @if (auth()->user()->role === 'admin')
                        @include('admin.sidebar')
                    @elseif (auth()->user()->role === 'employer')
                        @include('employer.sidebar')
                    @endif
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body card-form">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="fs-4 mb-1">Job Applications</h3>
                                </div>

                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover border-0 align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Applicant</th>
                                            <th scope="col">Company</th>
                                            <th scope="col">Application</th>
                                            <th scope="col">Resume</th>
                                            <th scope="col">Certificates</th>
                                            <th scope="col">Application Date</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        @if ($applications->isNotEmpty())
                                            @foreach ($applications as $application)
                                                <tr>
                                                    <td>
                                                        <p>{{ $application->job->title }}</p>
                                                        {{-- <p>Applicants: {{ $application->job->applications->count() }}</p> --}}
                                                    </td>
                                                    <td>{{ $application->user->name }}</td>
                                                    <td>{{ $application->job->company_name }}</td>
                                                    <td>
                                                        @if(!empty($application->application_file))
                                                            @php
                                                                $path = $application->application_file;
                                                                $fileLabel = $application->application_file_label ?? basename($path);
                                                            @endphp
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'application']) }}" download="{{ $fileLabel }}" class="d-inline-flex align-items-center gap-2 text-decoration-none text-primary border border-primary-subtle rounded-pill px-2 py-1 bg-primary-subtle shadow-sm" style="width: fit-content; max-width: 100%;">
                                                                <i class="fa fa-file bg-primary"></i>
                                                                <span class="text-truncate">{{ $fileLabel }}</span>
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($application->resume_file))
                                                            @php
                                                                $path = $application->resume_file;
                                                                $fileLabel = $application->resume_file_label ?? basename($path);
                                                            @endphp
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'resume']) }}" download="{{ $fileLabel }}" class="d-inline-flex align-items-center gap-2 text-decoration-none text-success border border-success-subtle rounded-pill px-2 py-1 bg-success-subtle shadow-sm" style="width: fit-content; max-width: 100%;">
                                                                <i class="fa fa-file text-success"></i>
                                                                <span class="text-truncate">{{ $fileLabel }}</span>
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($application->certificates_file))
                                                            @php $certs = json_decode($application->certificates_file, true) ?? []; $certLabels = $application->certificate_file_labels; @endphp
                                                            @if(!empty($certs))
                                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                                    @foreach($certs as $cert)
                                                                        @php
                                                                            $certLabel = $certLabels[$loop->index] ?? basename($cert);
                                                                        @endphp
                                                                        <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'certificate']) . '?file=' . urlencode(base64_encode($cert)) }}" download="{{ $certLabel }}" class="d-inline-flex align-items-center gap-2 text-decoration-none text-warning border border-warning-subtle rounded-pill px-2 py-1 bg-warning-subtle shadow-sm" style="width: fit-content; max-width: 100%;">
                                                                            <i class="fa fa-file text-warning"></i>
                                                                            <span class="text-truncate">{{ $certLabel }}</span>
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                N/A
                                                            @endif
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ $application->applied_at->format('Y-m-d') }}</td>

                                                    <td>
                                                        <div class="action-dots float-end">
                                                            <button href="#" class="btn" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href=""><i
                                                                            class="fa fa-eye" aria-hidden="true"></i>
                                                                        View</a></li>
                                                                <li><a class="dropdown-item" href="javascript:void(0);"
                                                                        onclick="deleteApplication({{ $application->id }})"><i
                                                                            class="fa fa-trash" aria-hidden="true"></i>
                                                                        Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">No job applications found.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $applications->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script type="text/javascript">
        function deleteApplication(id) {
            if (confirm('Are you sure you want to delete this application?')) {
                $.ajax({
                    url: "{{ route('admin.jobApplications.destroy') }}",
                    type: "DELETE",
                    dataType: "json",
                    data: {
                        id: id
                    },

                    success: function (response) {
                        window.location.href =
                            "{{ route('admin.jobApplications') }}"; // Redirect to the Job Applications page after deletion
                    },

                    error: function (xhr, status, error) {
                        alert('An error occurred while deleting the application. Please try again.');
                    }
                });
            }
        }
    </script>
@endsection