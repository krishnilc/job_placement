@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                              <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">My Job Applications</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('front.account.student-sidebar')
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
                                                        @if(!empty($jobApplication->application_file) || !empty($jobApplication->resume_file) || !empty($jobApplication->certificates_file))
                                                            <div class="mt-2 small">
                                                                @if(!empty($jobApplication->application_file))
                                                                    @php
                                                                        $path = $jobApplication->application_file;
                                                                        $disk = Storage::disk('applications')->exists($path)
                                                                            ? 'applications'
                                                                            : (Storage::disk('public')->exists($path) ? 'public' : 'applications');
                                                                        $ext = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
                                                                        $size = 0;
                                                                        try { $size = Storage::disk($disk)->size($path); } catch (Exception $e) { $size = 0; }
                                                                        if ($size >= 1048576) { $sizeText = round($size / 1048576, 2) . ' MB'; }
                                                                        elseif ($size >= 1024) { $sizeText = round($size / 1024, 2) . ' KB'; }
                                                                        else { $sizeText = $size . ' B'; }
                                                                    @endphp
                                                                    <a href="{{ route('application.download', ['application' => $jobApplication->id, 'type' => 'application']) }}" download class="me-2"><i class="fa fa-file"></i> {{ $ext }} ({{ $sizeText }})</a>
                                                                @endif

                                                                @if(!empty($jobApplication->resume_file))
                                                                    @php
                                                                        $path = $jobApplication->resume_file;
                                                                        $disk = Storage::disk('applications')->exists($path)
                                                                            ? 'applications'
                                                                            : (Storage::disk('public')->exists($path) ? 'public' : 'applications');
                                                                        $ext = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
                                                                        $size = 0;
                                                                        try { $size = Storage::disk($disk)->size($path); } catch (Exception $e) { $size = 0; }
                                                                        if ($size >= 1048576) { $sizeText = round($size / 1048576, 2) . ' MB'; }
                                                                        elseif ($size >= 1024) { $sizeText = round($size / 1024, 2) . ' KB'; }
                                                                        else { $sizeText = $size . ' B'; }
                                                                    @endphp
                                                                    <a href="{{ route('application.download', ['application' => $jobApplication->id, 'type' => 'resume']) }}" download class="me-2"><i class="fa fa-file"></i> {{ $ext }} ({{ $sizeText }})</a>
                                                                @endif

                                                                @if(!empty($jobApplication->certificates_file))
                                                                    @php $certs = json_decode($jobApplication->certificates_file, true) ?? []; @endphp
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
                                                                            <a href="{{ route('application.download', ['application' => $jobApplication->id, 'type' => 'certificate']) . '?file=' . urlencode(base64_encode($cert)) }}" download class="me-2"><i class="fa fa-file"></i> {{ $certExt }} ({{ $certSizeText }})</a>@if(!$loop->last), @endif
                                                                        @endforeach
                                                                    @else
                                                                        <span>N/A</span>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        @endif
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
                                                            {{ $jobApplication->status == 1 ? 'shortlisted' : 'in progress' }}
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
                                                                        View</a></li>
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
