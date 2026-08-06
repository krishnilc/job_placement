@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Saved Jobs</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('front.account.sidebar')
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4 p-3">
                        <div class="card-body card-form">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="fs-4 mb-1">Saved Jobs</h3>
                                </div>

                            </div>

                            <div class="table-responsive">
                                <table class="table ">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <!-- <th scope="col">Saved Date</th> -->
                                            <th scope="col">Employer</th>
                                            <th scope="col">Applicants</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        @php $hasSavedJobs = false; @endphp
                                        @foreach ($savedJobs as $savedJob)
                                            @if ($savedJob->job)
                                                @php $hasSavedJobs = true; @endphp
                                                <tr class="active">
                                                    <td>
                                                        <div class="job-name fw-500">{{ $savedJob->job->title }}</div>
                                                        <div class="info1">{{ $savedJob->job->jobType->name }}.
                                                            {{ $savedJob->job->location }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="job-status text-capitalize">
                                                            {{ $savedJob->job->company_name }}
                                                        </div>
                                                    </td>
                                                    <!-- <td>{{ $savedJob->created_at->format('d M, Y') }}</td> -->
                                                    <td>{{ $savedJob->job->applications->count() }} Applications</td>
                                                    <td>
                                                        <div class="job-status text-capitalize">
                                                            {{ $savedJob->job->status == 1 ? 'active' : 'inactive' }}
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
                                                                        href="{{ route('jobDetail', $savedJob->job->id) }}">
                                                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                                                        View</a></li>
                                                                <li><a class="dropdown-item" href="#"
                                                                        onclick="removeSavedJob({{ $savedJob->id }})"><i
                                                                            class="fa fa-trash" aria-hidden="true"></i>
                                                                        Remove</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        @unless ($hasSavedJobs)
                                            <tr>
                                                <td colspan="5"> No saved jobs found. </td>
                                            </tr>
                                        @endunless
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $savedJobs->links() }}
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
        function removeSavedJob(id) {
            if (confirm('Are you sure you want to remove this saved job?')) {
                $.ajax({
                    url: "{{ route('account.removeSavedJob') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        id: id
                    },

                    success: function(response) {
                        window.location.href =
                            "{{ route('account.savedJobs') }}"; // Redirect to the Saved Jobs page after removal
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while removing the saved job. Please try again.');
                    }
                });
            }
        }
    </script>
@endsection
