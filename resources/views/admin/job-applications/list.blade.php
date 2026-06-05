@extends('front.layouts.app')

@section('main')
 <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item active"><a href="{{ route('account.profile') }}">Home</a></li>
                        <!-- <li class="breadcrumb-item active">Account Settings</li> -->
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
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body card-form">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="fs-4 mb-1">Job Applications</h3>
                                </div>

                            </div>
                            <div class="table-responsive">
                                <table class="table ">
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
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'application']) }}" target="_blank" download><i class="fa fa-file"></i> {{ $ext }} ({{ $sizeText }})</a>
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
                                                                try { $size = Storage::disk($disk)->size($path); } catch (Exception $e) { $size = 0; }
                                                                if ($size >= 1048576) { $sizeText = round($size / 1048576, 2) . ' MB'; }
                                                                elseif ($size >= 1024) { $sizeText = round($size / 1024, 2) . ' KB'; }
                                                                else { $sizeText = $size . ' B'; }
                                                            @endphp
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'resume']) }}" target="_blank" download><i class="fa fa-file"></i> {{ $ext }} ({{ $sizeText }})</a>
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
                                                                    <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'certificate']) . '?file=' . urlencode(base64_encode($cert)) }}" target="_blank" download><i class="fa fa-file"></i> {{ $certExt }} ({{ $certSizeText }})</a>@if(!$loop->last), @endif
                                                                @endforeach
                                                            @else
                                                                N/A
                                                            @endif
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ $application->applied_at->format('Y-m-d') }}</td>

                                                    <td>
                                                        <div class="action-dots">
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
                                                <td colspan="8" class="text-center">No jobs found.</td>
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