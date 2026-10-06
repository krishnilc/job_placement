@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                              <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">My Job Applications</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('student.sidebar')
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4 p-3">
                        <div class="card-body card-form">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="fs-4 mb-1">My Job Applications</h3>
                                </div>

                            </div>
                            <form method="GET" action="{{ route('account.myJobApplications') }}" class="row g-2 my-3">
                                <div class="col-md-6 col-lg-4">
                                    <input type="text" name="search" value="{{ request()->query('search') }}"
                                        class="form-control" placeholder="Search title, company, location...">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                                        Search</button>
                                    <a href="{{ route('account.myJobApplications') }}"
                                        class="btn btn-secondary ms-1"><i class="fa fa-times"></i> Clear</a>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table ">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Applied Date</th>
                                            <th scope="col">Closing Date</th>
                                            {{-- <th scope="col">Applicants</th> --}}
                                            {{-- <th scope="col">Job Status</th> --}}
                                            <th scope="col">Application Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        @if ($jobApplications->isNotEmpty())
                                            @foreach ($jobApplications as $jobApplication)
                                                <tr class="active">
                                                    <td>
                                                        <div class="info1 job-name fw-500">{{ $jobApplication->job->title }}</div>
                                                        <div class="">{{ $jobApplication->job->jobType->name }}.
                                                            {{ $jobApplication->job->location }}
                                                        </div>
                                                    </td>
                                                    <td>{{ $jobApplication->created_at->format('d M, Y') }}</td>
                                                    <td>{{ !empty($jobApplication->job->closing_date) ? \Carbon\Carbon::parse($jobApplication->job->closing_date)->format('d M, Y') : 'Not set' }}</td>
                                                    {{-- <td>{{ $jobApplication->job->applications->count() }} Application(s)   </td> --}}
                                                    {{-- <td>
                                                        <div class="job-status text-capitalize">
                                                            {{ $jobApplication->job->status == 1 ? 'active' : 'inactive' }}
                                                        </div>
                                                    </td> --}}
                                                    <td>
                                                        <div class="application-status text-capitalize">
                                                            {{ $jobApplication->applicationStatus?->name ?? 'Submitted' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="action-dots float-end">
                                                            <button href="#" class="btn" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('jobDetail', $jobApplication->job->id) }}">
                                                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                                                        View Job</a></li>
                                                                @if(!empty($jobApplication->application_file) || !empty($jobApplication->resume_file) || !empty($jobApplication->certificates_file))
                                                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#filesModal{{ $jobApplication->id }}"><i class="fa fa-file" aria-hidden="true"></i> View Files Submitted</a></li>
                                                                @else
                                                                    <li><a class="dropdown-item disabled" href="#" onclick="event.preventDefault();" aria-disabled="true"><i class="fa fa-file" aria-hidden="true"></i> No Files Submitted</a></li>
                                                                @endif
                                                                @if ($jobApplication->isPlaced())
                                                                    <li>
                                                                        @if ($jobApplication->studentFeedback)
                                                                            <a class="dropdown-item disabled" href="#" onclick="event.preventDefault();" aria-disabled="true"><i class="fa fa-check" aria-hidden="true"></i> Feedback Submitted</a>
                                                                        @else
                                                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $jobApplication->id }}"><i class="fa fa-comment" aria-hidden="true"></i> Give Feedback</a>
                                                                        @endif
                                                                    </li>
                                                                @endif
                                                                @if(!empty($jobApplication->job->closing_date) && \Carbon\Carbon::parse($jobApplication->job->closing_date)->lt(\Carbon\Carbon::today()))
                                                                    <li>
                                                                        <a class="dropdown-item disabled" href="#" onclick="event.preventDefault();" aria-disabled="true">
                                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                                            Remove
                                                                        </a>
                                                                    </li>
                                                                @else
                                                                    <li>
                                                                        <a class="dropdown-item" href="#" onclick="removeApplication({{ $jobApplication->id }})">
                                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                                            Remove
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if(!empty($jobApplication->application_file) || !empty($jobApplication->resume_file) || !empty($jobApplication->certificates_file))
                                                    <div class="modal fade" id="filesModal{{ $jobApplication->id }}" tabindex="-1"
                                                        aria-labelledby="filesModalLabel{{ $jobApplication->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="filesModalLabel{{ $jobApplication->id }}">
                                                                        Submitted Files
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="text-muted mb-3">{{ $jobApplication->job->title }} - {{ $jobApplication->job->company_name }}</p>
                                                                    <div class="d-flex flex-column gap-2">
                                                                        @if(!empty($jobApplication->application_file))
                                                                            @php
                                                                                $fileLabel = $jobApplication->application_file_label ?? basename($jobApplication->application_file);
                                                                            @endphp
                                                                            <a href="{{ route('application.download', ['application' => $jobApplication->id, 'type' => 'application']) }}"
                                                                                download="{{ $fileLabel }}" title="{{ $fileLabel }}"
                                                                                class="d-inline-flex align-items-center gap-2 text-decoration-none text-primary border border-primary-subtle rounded-pill px-3 py-2 bg-primary-subtle shadow-sm">
                                                                                <i class="fa fa-file bg-primary"></i>
                                                                                <span>{{ $fileLabel }}</span>
                                                                                <small class="text-muted ms-auto">Application</small>
                                                                            </a>
                                                                        @endif

                                                                        @if(!empty($jobApplication->resume_file))
                                                                            @php
                                                                                $fileLabel = $jobApplication->resume_file_label ?? basename($jobApplication->resume_file);
                                                                            @endphp
                                                                            <a href="{{ route('application.download', ['application' => $jobApplication->id, 'type' => 'resume']) }}"
                                                                                download="{{ $fileLabel }}" title="{{ $fileLabel }}"
                                                                                class="d-inline-flex align-items-center gap-2 text-decoration-none text-success border border-success-subtle rounded-pill px-3 py-2 bg-success-subtle shadow-sm">
                                                                                <i class="fa fa-file text-success"></i>
                                                                                <span>{{ $fileLabel }}</span>
                                                                                <small class="text-muted ms-auto">Resume</small>
                                                                            </a>
                                                                        @endif

                                                                        @if(!empty($jobApplication->certificates_file))
                                                                            @php
                                                                                $certs = json_decode($jobApplication->certificates_file, true) ?? [];
                                                                                $certLabels = $jobApplication->certificate_file_labels;
                                                                            @endphp
                                                                            @foreach($certs as $cert)
                                                                                @php $certLabel = $certLabels[$loop->index] ?? basename($cert); @endphp
                                                                                <a href="{{ route('application.download', ['application' => $jobApplication->id, 'type' => 'certificate']) . '?file=' . urlencode(base64_encode($cert)) }}"
                                                                                    download="{{ $certLabel }}" title="{{ $certLabel }}"
                                                                                    class="d-inline-flex align-items-center gap-2 text-decoration-none text-warning border border-warning-subtle rounded-pill px-3 py-2 bg-warning-subtle shadow-sm">
                                                                                    <i class="fa fa-file text-warning"></i>
                                                                                    <span>{{ $certLabel }}</span>
                                                                                    <small class="text-muted ms-auto">Certificate</small>
                                                                                </a>
                                                                            @endforeach
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($jobApplication->isPlaced() && !$jobApplication->studentFeedback)
                                                    <div class="modal fade" id="feedbackModal{{ $jobApplication->id }}" tabindex="-1"
                                                        aria-labelledby="feedbackModalLabel{{ $jobApplication->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <form action="{{ route('account.feedback.store', $jobApplication) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="feedbackModalLabel{{ $jobApplication->id }}">
                                                                            Feedback on {{ $jobApplication->job->company_name }}
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p class="text-muted mb-3">{{ $jobApplication->job->title }} - {{ $jobApplication->job->company_name }}</p>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Your Experience Rating</label>
                                                                            <select name="rating" class="form-select" required>
                                                                                <option value="">Select a rating</option>
                                                                                <option value="5">5 - Excellent</option>
                                                                                <option value="4">4 - Good</option>
                                                                                <option value="3">3 - Satisfactory</option>
                                                                                <option value="2">2 - Needs Improvement</option>
                                                                                <option value="1">1 - Poor</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Comments</label>
                                                                            <textarea name="comments" class="form-control" rows="4" maxlength="2000" placeholder="Share your feedback on this company..."></textarea>
                                                                        </div>
                                                                        <p class="small text-muted mb-0">This feedback is only visible to administrators and is not shared directly with the employer.</p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            @else
                                            <tr>
                                                <td colspan="5"> No job applications found. </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $jobApplications->links() }}
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
        function removeApplication(id) {
            if (confirm('Are you sure you want to remove this application?')) {
                $.ajax({
                    url: "{{ route('account.removeJobApplication') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        id: id
                    },

                    success: function(response) {
                        window.location.href =
                        "{{ route('account.myJobApplications') }}"; // Redirect to the My Job Applications page after removal
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while removing the application. Please try again.');
                    }
                });
            }
        }
    </script>
@endsection
