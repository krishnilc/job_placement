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
                    <div id="statusAlert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
                        <span id="statusAlertText"></span>
                        <button type="button" class="btn-close" aria-label="Close"
                            onclick="document.getElementById('statusAlert').classList.add('d-none')"></button>
                    </div>
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
                            <form method="GET" action="{{ route('admin.users.students') }}" class="row g-2 my-3">
                                <input type="hidden" name="sort" value="{{ request()->query('sort') }}">
                                <input type="hidden" name="direction" value="{{ request()->query('direction') }}">
                                <div class="col-md-6 col-lg-4">
                                    <input type="text" name="search" value="{{ request()->query('search') }}"
                                        class="form-control" placeholder="Search name, email, mobile, student ID...">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                                        Search</button>
                                    <a href="{{ route('admin.users.students') }}"
                                        class="btn btn-secondary ms-1"><i class="fa fa-times"></i> Clear</a>
                                </div>
                            </form>
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
                                                'search' => request()->query('search'),
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
                                            <th scope="col">Designation</th>
                                            <th scope="col">Date of Birth</th>
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
                                                    <td>{{ $user->designation ?: '-' }}</td>
                                                    <td>{{ $user->date_of_birth ? \Illuminate\Support\Carbon::parse($user->date_of_birth)->format('d M Y') : '-' }}</td>
                                                    <td>{{ $user->mobile }}</td>
                                                    <td>
                                                        @if (in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                                                            <select class="form-select form-select-sm status-select status-{{ $user->status }}"
                                                                style="min-width: 150px"
                                                                data-id="{{ $user->id }}"
                                                                data-original="{{ $user->status }}">
                                                                <option value="pending" @selected($user->status === 'pending')>Pending Approval</option>
                                                                <option value="active" @selected($user->status === 'active')>Active</option>
                                                                <option value="blocked" @selected($user->status === 'blocked')>Blocked</option>
                                                            </select>
                                                        @else
                                                            <span
                                                                class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'blocked' ? 'danger' : 'warning text-dark') }}">
                                                                {{ $user->status === 'pending' ? 'Pending Approval' : ucfirst($user->status) }}
                                                            </span>
                                                        @endif
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
                                                                @unless (auth()->user()->isReadOnlyManagement())
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('admin.users.edit', $user->id) }}"><i
                                                                            class="fa fa-edit" aria-hidden="true"></i>
                                                                        Update Profile</a></li>
                                                                @endunless
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
                                                <td colspan="9" class="text-center">No students found.</td>
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
    <style>
        .status-select { font-weight: 600; border-width: 2px; }
        .status-select.status-active { background-color: #d1e7dd; border-color: #198754; color: #0f5132; }
        .status-select.status-blocked { background-color: #f8d7da; border-color: #dc3545; color: #842029; }
        .status-select.status-pending { background-color: #fff3cd; border-color: #ffc107; color: #664d03; }
    </style>
    <script type="text/javascript">
        function applyStatusColor(select) {
            select.removeClass('status-active status-blocked status-pending').addClass('status-' + select.val());
        }

        $('.status-select').on('change', function() {
            const select = $(this);
            const previous = select.data('original');
            applyStatusColor(select);
            select.prop('disabled', true);

            $.ajax({
                url: "{{ url('admin/users/students') }}/" + select.data('id') + "/status",
                type: "PATCH",
                dataType: "json",
                data: {
                    status: select.val(),
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    select.data('original', select.val());
                    const label = select.find('option:selected').text();
                    const name = select.closest('tr').find('td').eq(1).text().trim();
                    $('#statusAlertText').text(name + "'s status is " + label + '. ' + response.message);
                    $('#statusAlert').removeClass('d-none alert-success alert-warning')
                        .addClass(response.notification_sent === false ? 'alert-warning' : 'alert-success');
                },
                error: function() {
                    select.val(previous);
                    applyStatusColor(select);
                    alert('Could not update the status. Please try again.');
                },
                complete: function() {
                    select.prop('disabled', false);
                }
            });
        });

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
