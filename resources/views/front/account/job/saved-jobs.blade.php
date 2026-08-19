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
                    @include('front.account.student-sidebar')
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
                                <table class="table ">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col"><a href="{{ $sortUrl('title') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by title">Title <i class="fa {{ $sortIcon('title') }}"
                                                        aria-hidden="true"></i></a></th>
                                            <!-- <th scope="col">Saved Date</th> -->
                                            <th scope="col"><a href="{{ $sortUrl('company_name') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by employer">Employer <i
                                                        class="fa {{ $sortIcon('company_name') }}"
                                                        aria-hidden="true"></i></a></th>
                                            <th scope="col">Applicants</th>
                                            <th scope="col"><a href="{{ $sortUrl('closing_date') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by closing date">Closing Date <i
                                                        class="fa {{ $sortIcon('closing_date') }}"
                                                        aria-hidden="true"></i></a></th>
                                            <th scope="col"><a href="{{ $sortUrl('status') }}"
                                                    class="d-inline-flex align-items-center gap-1 text-decoration-none text-dark text-nowrap"
                                                    aria-label="Sort by status">Status <i
                                                        class="fa {{ $sortIcon('status') }}"
                                                        aria-hidden="true"></i></a></th>
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
                                                        <div class="info1 job-name fw-500">{{ $savedJob->job->title }}</div>
                                                        <div class="">{{ $savedJob->job->jobType->name }}.
                                                            {{ $savedJob->job->location }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="job-status text-capitalize">
                                                            {{ $savedJob->job->company_name }}
                                                        </div>
                                                    </td>
                                                    <!-- <td>{{ $savedJob->created_at->format('d M, Y') }}</td> -->
                                                    <td>{{ $savedJob->job->applications->count() }} </td>
                                                    <td>{{ !empty($savedJob->job->closing_date) ? \Carbon\Carbon::parse($savedJob->job->closing_date)->format('d M, Y') : 'Not set' }}</td>
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
