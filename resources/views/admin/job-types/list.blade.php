@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Job Types</li>
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
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="fs-4 mb-1">Job Types</h3>
                                <a href="{{ route('admin.jobTypes.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Job Type
                                </a>
                            </div>
                            <form method="GET" action="{{ route('admin.jobTypes') }}" class="row g-2 my-3">
                                <input type="hidden" name="sort" value="{{ request()->query('sort') }}">
                                <input type="hidden" name="direction" value="{{ request()->query('direction') }}">
                                <div class="col-md-6 col-lg-4">
                                    <input type="text" name="search" value="{{ request()->query('search') }}"
                                        class="form-control" placeholder="Search job types...">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                                    <a href="{{ route('admin.jobTypes') }}" class="btn btn-secondary ms-1">
                                        <i class="fa fa-times"></i> Clear
                                    </a>
                                </div>
                            </form>
                            @php
                                $currentSort = request()->query('sort', 'name');
                                $currentDirection = strtolower(request()->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
                                $buildSortUrl = function ($column) use ($currentSort, $currentDirection) {
                                    $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

                                    return route('admin.jobTypes') . '?' . http_build_query([
                                        'sort' => $column,
                                        'direction' => $nextDirection,
                                        'page' => 1,
                                        'search' => request()->query('search'),
                                    ]);
                                };
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-hover border-0 align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th><a href="{{ $buildSortUrl('id') }}" class="text-decoration-none text-dark">ID</a></th>
                                            <th><a href="{{ $buildSortUrl('name') }}" class="text-decoration-none text-dark">Name</a></th>
                                            <th>Jobs</th>
                                            <th scope="col">
                                                <a href="{{ $buildSortUrl('status') }}" class="text-decoration-none text-dark">
                                                    Status
                                                    @if ($currentSort === 'status')
                                                        <i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                                    @else
                                                        <i class="fa fa-sort text-muted ms-1"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jobTypes as $jobType)
                                            <tr>
                                                <td>{{ $jobType->id }}</td>
                                                <td>{{ $jobType->name }}</td>
                                                <td>{{ $jobType->jobs_count }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $jobType->status ? 'success' : 'danger' }}">
                                                        {{ $jobType->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-dots">
                                                        <button type="button" class="btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="{{ route('admin.jobTypes.edit', $jobType->id) }}">
                                                                <i class="fa fa-edit" aria-hidden="true"></i> Edit</a></li>
                                                            @if (auth()->user()->role === 'super_admin')
                                                                <li><a class="dropdown-item" href="javascript:void(0);"
                                                                    onclick="deleteJobType({{ $jobType->id }})">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i> Delete</a></li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center">No job types found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div>{{ $jobTypes->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script>
        function deleteJobType(id) {
            if (!confirm('Are you sure you want to delete this job type?')) {
                return;
            }

            $.ajax({
                url: "{{ route('admin.jobTypes.destroy') }}",
                type: 'DELETE',
                dataType: 'json',
                data: { id: id },
                success: function(response) {
                    if (response.status) {
                        window.location.href = "{{ route('admin.jobTypes') }}";
                    } else {
                        alert('Unable to delete this job type. It may be assigned to jobs, or you may not have permission.');
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the job type. Please try again.');
                }
            });
        }
    </script>
@endsection
