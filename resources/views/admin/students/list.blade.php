@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Students</li>
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
                                    <h3 class="fs-4 mb-1">Students</h3>
                                </div>
                                @if (in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                                    <div>
                                        <a href="{{ route('admin.users.students.create') }}" class="btn btn-primary">
                                            <i class="fa fa-plus"></i> Add Student
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="table-responsive">
                                @php
                                    $currentSort = request()->query('sort', 'created_at');
                                    $currentDirection =
                                        strtolower(request()->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
                                    $baseRoute = route('admin.users.students');
                                    $buildSortUrl = function ($column) use (
                                        $baseRoute,
                                        $currentSort,
                                        $currentDirection,
                                    ) {
                                        $nextDirection =
                                            $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

                                        return $baseRoute .
                                            '?' .
                                            http_build_query([
                                                'sort' => $column,
                                                'direction' => $nextDirection,
                                                'page' => 1,
                                            ]);
                                    };
                                @endphp

                                <table class="table table-hover border-0 align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col"><a href="{{ $buildSortUrl('id') }}"
                                                    class="text-decoration-none text-dark">ID @if ($currentSort === 'id')
                                                        <i
                                                        class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i
                                                            class="fa fa-sort text-muted ms-1"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th scope="col"><a href="{{ $buildSortUrl('name') }}"
                                                    class="text-decoration-none text-dark">Name @if ($currentSort === 'name')
                                                        <i
                                                        class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i
                                                            class="fa fa-sort text-muted ms-1"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th scope="col"><a href="{{ $buildSortUrl('student_id') }}"
                                                    class="text-decoration-none text-dark">Student ID @if ($currentSort === 'student_id')
                                                        <i
                                                        class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i
                                                            class="fa fa-sort text-muted ms-1"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th scope="col"><a href="{{ $buildSortUrl('email') }}"
                                                    class="text-decoration-none text-dark">Email @if ($currentSort === 'email')
                                                        <i
                                                        class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i
                                                            class="fa fa-sort text-muted ms-1"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th scope="col"><a href="{{ $buildSortUrl('mobile') }}"
                                                    class="text-decoration-none text-dark">Mobile @if ($currentSort === 'mobile')
                                                        <i
                                                        class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i
                                                            class="fa fa-sort text-muted ms-1"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th scope="col"><a href="{{ $buildSortUrl('status') }}"
                                                    class="text-decoration-none text-dark">Status @if ($currentSort === 'status')
                                                        <i
                                                        class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i
                                                            class="fa fa-sort text-muted ms-1"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        @if ($users->isNotEmpty())
                                            @foreach ($users as $user)
                                                <tr class="active">
                                                    <td>{{ $user->id }}</td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->student_id }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->mobile }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'blocked' ? 'danger' : 'warning text-dark') }}">
                                                            {{ $user->status === 'pending' ? 'Pending Approval' : ucfirst($user->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-dots">
                                                            <button href="#" class="btn" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('admin.users.profile', $user->id) }}"><i
                                                                            class="fa fa-user" aria-hidden="true"></i>
                                                                        View Profile</a></li>
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('admin.users.edit', $user->id) }}"><i
                                                                            class="fa fa-edit" aria-hidden="true"></i>
                                                                        Edit</a></li>
                                                                @if (auth()->user()->role === 'super_admin')
                                                                    <li><a class="dropdown-item" href="javascript:void(0);"
                                                                            onclick="deleteUser({{ $user->id }})"><i
                                                                                class="fa fa-trash" aria-hidden="true"></i>
                                                                            Delete</a></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center">No students found.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $users->links() }}
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
        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                $.ajax({
                    url: "{{ route('admin.users.destroy') }}",
                    type: "DELETE",
                    dataType: "json",
                    data: {
                        id: id
                    },

                    success: function(response) {
                        if (response.status == true) {
                            window.location.href = "{{ route('admin.users.students') }}"; // Redirect after deletion
                        } else {
                            alert('Only super admins can delete students.');
                        }
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting the student. Please try again.');
                    }
                });
            }
        }
    </script>
@endsection
