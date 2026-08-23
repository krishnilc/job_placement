@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">
                                @php
                                    $listType = $list_type ?? 'all';
                                    $title =
                                        $listType === 'students'
                                            ? 'Students'
                                            : ($listType === 'employers'
                                                ? 'Employers'
                                                : 'Users');
                                @endphp
                                {{ $title }}
                            </li>
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
                                    <h3 class="fs-4 mb-1">{{ $title }}</h3>
                                </div>

                            </div>
                            <div class="table-responsive">
                                @php
                                    $currentSort = request()->query('sort', 'created_at');
                                    $currentDirection = strtolower(request()->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
                                    $listType = $list_type ?? 'all';
                                    $baseRoute = match ($listType) {
                                        'students' => route('admin.users.students'),
                                        'employers' => route('admin.users.employers'),
                                        default => route('admin.users'),
                                    };
                                    $buildSortUrl = function ($column) use ($baseRoute, $currentSort, $currentDirection) {
                                        $nextDirection = ($currentSort === $column && $currentDirection === 'asc') ? 'desc' : 'asc';

                                        return $baseRoute . '?' . http_build_query([
                                            'sort' => $column,
                                            'direction' => $nextDirection,
                                            'page' => 1,
                                        ]);
                                    };
                                @endphp

                                <table class="table table-hover border-0 align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col"><a href="{{ $buildSortUrl('id') }}" class="text-decoration-none text-dark">ID @if ($currentSort === 'id')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>
                                            <th scope="col"><a href="{{ $buildSortUrl('name') }}" class="text-decoration-none text-dark">Name @if ($currentSort === 'name')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>
                                            @if ($listType === 'employers')
                                                <th scope="col"><a href="{{ $buildSortUrl('designation') }}" class="text-decoration-none text-dark">Designation @if ($currentSort === 'designation')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>
                                            @endif
                                             @if ($listType === 'students')
                                                 <th scope="col"><a href="{{ $buildSortUrl('student_id') }}" class="text-decoration-none text-dark">Student ID @if ($currentSort === 'student_id')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>
                                            @endif
                                            <th scope="col"><a href="{{ $buildSortUrl('email') }}" class="text-decoration-none text-dark">Email @if ($currentSort === 'email')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>
                                            <th scope="col"><a href="{{ $buildSortUrl('mobile') }}" class="text-decoration-none text-dark">Mobile @if ($currentSort === 'mobile')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>                                           
                                            @if ($listType === 'employers')
                                                <th scope="col"><a href="{{ $buildSortUrl('status') }}" class="text-decoration-none text-dark">Status @if ($currentSort === 'status')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>
                                            @endif
                                            @if ($listType === 'students')
                                                <th scope="col"><a href="{{ $buildSortUrl('status') }}" class="text-decoration-none text-dark">Status @if ($currentSort === 'status')<i class="fa fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>@else<i class="fa fa-sort text-muted ms-1"></i>@endif</a></th>
                                            @endif
                                            {{-- <th scope="col">Role</th> --}}
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        @if ($users->isNotEmpty())
                                            @foreach ($users as $user)
                                                <tr class="active">
                                                    <td> {{ $user->id }} </td>
                                                    <td>{{ $user->name }}</td>
                                                                                                        @if (($list_type ?? 'all') === 'employers')
                                                                                                                <td>{{ $user->designation }}</td>
                                                                                                        @endif
                                                      @if (($list_type ?? 'all') === 'students')
                                                        <td>{{ $user->student_id }}</td>
                                                    @endif
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->mobile }}</td>
                                                  
                                                    @if (($list_type ?? 'all') === 'employers')
                                                        <td>
                                                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'blocked' ? 'danger' : 'warning text-dark') }}">
                                                                {{ $user->status === 'pending' ? 'Pending Approval' : ucfirst($user->status) }}
                                                            </span>
                                                        </td>
                                                    @endif
                                                    @if (($list_type ?? 'all') === 'students')
                                                        <td>
                                                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'blocked' ? 'danger' : 'warning text-dark') }}">
                                                                {{ $user->status === 'pending' ? 'Pending Approval' : ucfirst($user->status) }}
                                                            </span>
                                                        </td>
                                                    @endif
                                                    {{-- <td>{{ $user->role }}</td> --}}

                                                    <td>
                                                        <div class="action-dots">
                                                            <button href="#" class="btn" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                @if (($list_type ?? 'all') === 'students')
                                                                    <li><a class="dropdown-item"
                                                                            href="{{ route('admin.users.profile', $user->id) }}"><i
                                                                                class="fa fa-user" aria-hidden="true"></i>
                                                                            View Profile</a></li>
                                                                @endif
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('admin.users.edit', $user->id) }}?list_type={{ $list_type ?? 'all' }}"><i
                                                                            class="fa fa-edit" aria-hidden="true"></i>
                                                                        Edit</a></li>
                                                                <li><a class="dropdown-item" href="javascript:void(0);"
                                                                        onclick="deleteUser({{ $user->id }})"><i
                                                                            class="fa fa-trash" aria-hidden="true"></i>
                                                                        Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                           @else
                                            <tr>
                                                <td colspan="6" class="text-center">No users found.</td>
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
            if (confirm('Are you sure you want to delete this user?')) {
                $.ajax({
                    url: "{{ route('admin.users.destroy') }}",
                    type: "DELETE",
                    dataType: "json",
                    data: {
                        id: id
                    },

                    success: function(response) {
                        var listType = "{{ $list_type ?? 'all' }}";
                        var redirect = "{{ route('admin.users') }}";
                        if (listType === 'students') {
                            redirect = "{{ route('admin.users.students') }}";
                        } else if (listType === 'employers') {
                            redirect = "{{ route('admin.users.employers') }}";
                        }
                        window.location.href = redirect; // Redirect after deletion
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting the user. Please try again.');
                    }
                });
            }
        }
    </script>
@endsection
