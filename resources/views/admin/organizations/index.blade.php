@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Organizations</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">@include('admin.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3">Organizations</h1>
                        <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">Add Organization</a>
                    </div>
                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-md-6 col-lg-4">
                            <label for="search" class="visually-hidden">Search existing organizations</label>
                            <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Search existing organization names">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary">Search</button>
                            <!-- <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary ms-1">Reset</a> -->
                            <a href="{{ route('admin.organizations.index') }}"
                                        class="btn btn-secondary ms-1"><i class="fa fa-times"></i> Clear</a>
                        </div>
                    </form>
                    <div class="card border-0 shadow mb-4">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead><tr><th>Organization</th><th>Address</th><th>Contacts</th><th>Action</th></tr></thead>
                                <tbody>
                                    @forelse ($organizations as $organization)
                                        <tr>
                                            <td>{{ $organization->name }}</td>
                                            <td>{{ $organization->address }}</td>
                                            <td><a href="{{ route('admin.users.employers', ['organization_id' => $organization->id]) }}">{{ $organization->employer_profiles_count }}</a></td>
                                            <td>
                                                <div class="action-dots">
                                                    <button href="#" class="btn" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('admin.organizations.show', $organization) }}"><i
                                                                    class="fa fa-eye" aria-hidden="true"></i>
                                                                View</a></li>
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('admin.organizations.edit', $organization) }}"><i
                                                                    class="fa fa-edit" aria-hidden="true"></i>
                                                                Edit details</a></li>
                                                        @if (auth()->user()->role === 'super_admin')
                                                            @if ($organization->employer_profiles_count > 0 || $organization->jobs_count > 0)
                                                                <li><span class="dropdown-item disabled"
                                                                        title="Cannot delete: organization still has linked employees or job postings."><i
                                                                            class="fa fa-trash" aria-hidden="true"></i>
                                                                        Delete</span></li>
                                                            @else
                                                                <li>
                                                                    <form action="{{ route('admin.organizations.destroy', $organization) }}"
                                                                        method="POST"
                                                                        onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="dropdown-item"><i
                                                                                class="fa fa-trash" aria-hidden="true"></i>
                                                                            Delete</button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No organizations found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{ $organizations->links() }}
                    <h2 class="h5 mt-4">Pending Organization Requests</h2>
                    <div class="card border-0 shadow">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead><tr><th>Requested name</th><th>Contact</th><th>Requested</th><th>Action</th></tr></thead>
                                <tbody>
                                    @forelse ($requests as $pending)
                                        <tr>
                                            <td>{{ $pending->name }}</td>
                                            <td>{{ $pending->user?->name ?? 'Deleted contact' }}</td>
                                            <td>{{ $pending->created_at->format('d M Y') }}</td>
                                            <td><a href="{{ route('admin.organizations.review', $pending) }}">Search and review</a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No pending organization requests.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
