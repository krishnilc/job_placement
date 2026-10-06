@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Categories</li>
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
                                    <h3 class="fs-4 mb-1">Categories</h3>
                                </div>
                                <div>
                                    @unless (auth()->user()->isReadOnlyManagement())
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Add Category
                                    </a>
                                    @endunless
                                </div>
                            </div>
                            <form method="GET" action="{{ route('admin.categories') }}" class="row g-2 my-3">
                                <input type="hidden" name="sort" value="{{ request()->query('sort') }}">
                                <input type="hidden" name="direction" value="{{ request()->query('direction') }}">
                                <div class="col-md-6 col-lg-4">
                                    <input type="text" name="search" value="{{ request()->query('search') }}"
                                        class="form-control" placeholder="Search category or college/center...">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                                        Search</button>
                                    <a href="{{ route('admin.categories') }}" class="btn btn-secondary ms-1"><i
                                            class="fa fa-times"></i> Clear</a>
                                </div>
                            </form>
                            <div class="table-responsive">
                                @php
                                    $currentSort = request()->query('sort', 'name');
                                    $currentDirection =
                                        strtolower(request()->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
                                    $baseRoute = route('admin.categories');
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
                                            <th scope="col">College/Center</th>
                                            <th scope="col">Jobs</th>
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
                                        @if ($categories->isNotEmpty())
                                            @foreach ($categories as $category)
                                                <tr class="active">
                                                    <td>{{ $category->id }}</td>
                                                    <td>{{ $category->name }}</td>
                                                    <td>{{ $category->college?->display_name ?? '-' }}</td>
                                                    <td>{{ $category->jobs_count }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $category->status == 1 ? 'success' : 'danger' }}">
                                                            {{ $category->status == 1 ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @unless (auth()->user()->isReadOnlyManagement())
                                                        <div class="action-dots">
                                                            <button href="#" class="btn" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('admin.categories.edit', $category->id) }}"><i
                                                                            class="fa fa-edit" aria-hidden="true"></i>
                                                                        Edit</a></li>
                                                                @if (auth()->user()->role === 'super_admin')
                                                                    <li><a class="dropdown-item" href="javascript:void(0);"
                                                                            onclick="deleteCategory({{ $category->id }})"><i
                                                                                class="fa fa-trash" aria-hidden="true"></i>
                                                                            Delete</a></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                        @endunless
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center">No categories found.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $categories->links() }}
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
        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category?')) {
                $.ajax({
                    url: "{{ route('admin.categories.destroy') }}",
                    type: "DELETE",
                    dataType: "json",
                    data: {
                        id: id
                    },

                    success: function(response) {
                        if (response.status == true) {
                            window.location.href = "{{ route('admin.categories') }}"; // Redirect after deletion
                        } else {
                            alert('Unable to delete this category. It may be assigned to jobs, or you may not have permission.');
                        }
                    },

                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting the category. Please try again.');
                    }
                });
            }
        }
    </script>
@endsection
