@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
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
                                @php
                                    $sortUrl = function ($column) use ($sort, $direction) {
                                        $nextDirection = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

                                        return request()->fullUrlWithQuery([
                                            'sort' => $column,
                                            'direction' => $nextDirection,
                                            'page' => null,
                                        ]);
                                    };
                                    $sortIcon = function ($column) use ($sort, $direction) {
                                        if ($sort !== $column) {
                                            return 'fa-sort';
                                        }

                                        return $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                                    };
                                @endphp
                                <table class="table table-hover border-0 align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col"><a href="{{ $sortUrl('title') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by title">Title <i class="fa {{ $sortIcon('title') }}"
                                                        aria-hidden="true"></i></a></th>
                                            <th scope="col" class="fw-semibold"><a href="{{ $sortUrl('applicant') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by applicant">Applicant <i
                                                        class="fa {{ $sortIcon('applicant') }}" aria-hidden="true"></i></a>
                                            </th>
                                            <th scope="col" class="fw-semibold"><a href="{{ $sortUrl('company_name') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by company">Company <i
                                                        class="fa {{ $sortIcon('company_name') }}"
                                                        aria-hidden="true"></i></a></th>
                                            <th scope="col" class="fw-semibold">Files</th>
                                            <th scope="col" class="fw-semibold"><a href="{{ $sortUrl('applied_at') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by application date">Application Date <i
                                                        class="fa {{ $sortIcon('applied_at') }}" aria-hidden="true"></i></a>
                                            </th>
                                            <th scope="col" class="fw-semibold">Status</th>
                                            <th scope="col" class="fw-semibold">Action</th>
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
                                                    <td style="min-width: 140px; white-space: normal;">
                                                        <div class="d-flex flex-column gap-1">
                                                        @if(!empty($application->application_file))
                                                            @php
                                                                $path = $application->application_file;
                                                                $fileLabel = $application->application_file_label ?? basename($path);
                                                                $shortFileLabel = \Illuminate\Support\Str::limit($fileLabel, 18);
                                                            @endphp
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'application']) }}"
                                                                download="{{ $fileLabel }}" title="{{ $fileLabel }}"
                                                                class="d-inline-flex align-items-center gap-2 text-decoration-none text-primary border border-primary-subtle rounded-pill px-2 py-1 bg-primary-subtle shadow-sm"
                                                                style="max-width: 130px; font-size: 0.76rem;">
                                                                <i class="fa fa-file bg-primary"></i>
                                                                <span class="d-inline-block text-truncate"
                                                                    style="max-width: 80px;">{{ $shortFileLabel }}</span>
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif

                                                        @if(!empty($application->resume_file))
                                                            @php
                                                                $path = $application->resume_file;
                                                                $fileLabel = $application->resume_file_label ?? basename($path);
                                                                $shortFileLabel = \Illuminate\Support\Str::limit($fileLabel, 18);
                                                            @endphp
                                                            <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'resume']) }}"
                                                                download="{{ $fileLabel }}" title="{{ $fileLabel }}"
                                                                class="d-inline-flex align-items-center gap-2 text-decoration-none text-success border border-success-subtle rounded-pill px-2 py-1 bg-success-subtle shadow-sm"
                                                                style="max-width: 130px; font-size: 0.76rem;">
                                                                <i class="fa fa-file text-success"></i>
                                                                <span class="d-inline-block text-truncate"
                                                                    style="max-width: 80px;">{{ $shortFileLabel }}</span>
                                                            </a>
                                                        @else
                                                            @if(empty($application->application_file))
                                                                N/A
                                                            @endif
                                                        @endif

                                                        @if(!empty($application->certificates_file))
                                                            @php $certs = json_decode($application->certificates_file, true) ?? [];
                                                            $certLabels = $application->certificate_file_labels; @endphp
                                                            @if(!empty($certs))
                                                                    @foreach($certs as $cert)
                                                                        @php $certLabel = $certLabels[$loop->index] ?? basename($cert); @endphp
                                                                        <a href="{{ route('application.download', ['application' => $application->id, 'type' => 'certificate']) . '?file=' . urlencode(base64_encode($cert)) }}" download="{{ $certLabel }}" title="{{ $certLabel }}"
                                                                            class="d-inline-flex align-items-center gap-2 text-decoration-none text-warning border border-warning-subtle rounded-pill px-2 py-1 bg-warning-subtle shadow-sm mb-1"
                                                                            style="max-width: 130px; font-size: 0.76rem;">
                                                                            <i class="fa fa-file text-warning"></i>
                                                                            <span class="d-inline-block text-truncate" style="max-width: 80px;">{{ \Illuminate\Support\Str::limit($certLabel, 18) }}</span>
                                                                        </a>
                                                                    @endforeach
                                                            @endif
                                                        @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ optional($application->applied_at)->format('M d, Y') ?? 'N/A' }}</td>
                                                    <td>
                                                        <form action="{{ route('admin.jobApplications.status', $application) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <select name="application_status_id" class="form-select form-select-sm" data-current-status="{{ $application->application_status_id }}" onchange="confirmStatusChange(this)" aria-label="Application status for {{ $application->user->name }}">
                                                                @foreach($applicationStatuses as $status)
                                                                    <option value="{{ $status->id }}" @selected($application->application_status_id === $status->id)>{{ $status->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <div class="action-dots">
                                                            <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="deleteApplication({{ $application->id }})"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center">No job applications found.</td>
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
        function confirmStatusChange(select) {
            const selectedStatus = select.options[select.selectedIndex].text;

            if (confirm(`Change this application status to "${selectedStatus}"?`)) {
                select.form.submit();
                return;
            }

            select.value = select.dataset.currentStatus;
        }

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