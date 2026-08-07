@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            @if (auth()->user()->role == 'user')
                                <li class="breadcrumb-item"><a href="{{ route('account.dashboard') }}">Home</a></li>
                            @elseif (auth()->user()->role == 'admin')
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            @endif
                            {{-- <li class="breadcrumb-item"><a href="{{ route('account.profile') }}">Home</a></li> --}}
                            <li class="breadcrumb-item active">My Jobs</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @if (auth()->user()->role == 'admin')
                        @include('admin.sidebar')
                    @elseif (auth()->user()->role == 'employer')
                        @include('employer.sidebar')
                    @else
                        @include('front.account.sidebar')
                    @endif
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4 p-3">
                        <div class="card-body card-form">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="fs-4 mb-1">My Jobs</h3>
                                </div>
                                <div style="margin-top: -10px;">
                                    <a href="{{ route('account.createJob') }}" class="btn btn-primary">Post a Job</a>
                                </div>

                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover border-0 align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Job Created</th>
                                            <th scope="col">Closing Date</th>
                                            <th scope="col">Applicants</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        @if ($jobs->isNotEmpty())
                                            @foreach ($jobs as $job)
                                                <tr class="active">
                                                    <td>
                                                        <div class="job-name fw-500">{{ $job->title }}</div>
                                                        <div class="info1">{{ $job->jobType->name }}. {{ $job->location }}
                                                        </div>
                                                    </td>
                                                    <td>{{ $job->created_at->format('d M, Y') }}</td>
                                                    <td>{{ !empty($job->closing_date) ? \Carbon\Carbon::parse($job->closing_date)->format('d M, Y') : 'Not set' }}
                                                    </td>
                                                    <td>{{ $job->applications->count() }}
                                                        Application{{ $job->applications->count() == 1 ? '' : 's' }}</td>
                                                    <td>
                                                        <div class="job-status text-capitalize">
                                                            {{ $job->status == 1 ? 'active' : 'inactive' }}
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="action-dots">
                                                            <button href="#" class="btn" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('jobDetail', $job->id) }}"> <i
                                                                            class="fa fa-eye" aria-hidden="true"></i>
                                                                        View</a></li>
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('account.editJob', $job->id) }}"><i
                                                                            class="fa fa-edit" aria-hidden="true"></i>
                                                                        Edit</a></li>
                                                                @if (auth()->user()->role == 'admin')
                                                                    <li><a class="dropdown-item" href="#"
                                                                            onclick="deleteJob({{ $job->id }})"><i
                                                                                class="fa fa-trash" aria-hidden="true"></i>
                                                                            Delete</a></li>
                                                                @elseif (auth()->user()->role == 'employer')
                                                                    @if ($job->status == 1)
                                                                        <li><a class="dropdown-item" href="#"
                                                                                onclick="blockJob({{ $job->id }})"><i
                                                                                    class="fa fa-ban" aria-hidden="true"></i>
                                                                                Block</a></li>
                                                                    @else
                                                                        <li><a class="dropdown-item" href="#"
                                                                                onclick="unblockJob({{ $job->id }})"><i
                                                                                    class="fa fa-check-circle" aria-hidden="true"></i>
                                                                                Unblock</a></li>
                                                                    @endif
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center">No jobs found.</td>
                                            </tr>
                                        @endif                                     
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $jobs->links() }}
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
        function deleteJob(jobId) {
            if (confirm('Are you sure you want to delete this job?')) {
                $.ajax({
                    url: "{{ route('account.deleteJob') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        jobId: jobId
                    },

                    success: function(response) {
                        window.location.href =
                        "{{ route('account.myJobs') }}"; // Redirect to the My Jobs page after deletion
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting the job. Please try again.');
                    }
                });
            }
        }

        function blockJob(jobId) {
            if (confirm('Are you sure you want to block this job?')) {
                $.ajax({
                    url: "{{ route('account.blockJob') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        jobId: jobId
                    },

                    success: function(response) {
                        window.location.href = "{{ route('account.myJobs') }}";
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while blocking the job. Please try again.');
                    }
                });
            }
        }

        function unblockJob(jobId) {
            if (confirm('Are you sure you want to unblock this job?')) {
                $.ajax({
                    url: "{{ route('account.unblockJob') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        jobId: jobId
                    },

                    success: function(response) {
                        window.location.href = "{{ route('account.myJobs') }}";
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while unblocking the job. Please try again.');
                    }
                });
            }
        }
    </script>
@endsection
