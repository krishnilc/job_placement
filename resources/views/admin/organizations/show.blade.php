@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.organizations.index') }}">Manage Organizations</a></li>
                            <li class="breadcrumb-item active">{{ $organization->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">@include('admin.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h1 class="h3 mb-0">{{ $organization->name }}</h1>
                                <div>
                                    @unless (auth()->user()->isReadOnlyManagement())
                                        <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn-primary">Edit details</a>
                                    @endunless
                                    <a href="{{ route('admin.organizations.index') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Address</dt>
                                <dd class="col-sm-9">{{ $organization->address }}</dd>

                                <dt class="col-sm-3">Phone</dt>
                                <dd class="col-sm-9">{{ $organization->phone ?: '-' }}</dd>

                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9">{{ $organization->email ?: '-' }}</dd>

                                <dt class="col-sm-3">Website</dt>
                                <dd class="col-sm-9">
                                    @if ($organization->website_url)
                                        <a href="{{ $organization->website_url }}" target="_blank" rel="noopener">{{ $organization->website_url }}</a>
                                    @else
                                        -
                                    @endif
                                </dd>

                                <dt class="col-sm-3">LinkedIn</dt>
                                <dd class="col-sm-9">
                                    @if ($organization->linkedin_url)
                                        <a href="{{ $organization->linkedin_url }}" target="_blank" rel="noopener">{{ $organization->linkedin_url }}</a>
                                    @else
                                        -
                                    @endif
                                </dd>

                                <dt class="col-sm-3">Facebook</dt>
                                <dd class="col-sm-9">
                                    @if ($organization->facebook_url)
                                        <a href="{{ $organization->facebook_url }}" target="_blank" rel="noopener">{{ $organization->facebook_url }}</a>
                                    @else
                                        -
                                    @endif
                                </dd>

                                <dt class="col-sm-3">Description</dt>
                                <dd class="col-sm-9">{{ $organization->description ?: '-' }}</dd>

                                <dt class="col-sm-3">Linked employees</dt>
                                <dd class="col-sm-9"><a href="{{ route('admin.users.employers', ['organization_id' => $organization->id]) }}">{{ $organization->employer_profiles_count }}</a></dd>

                                <dt class="col-sm-3">Job postings</dt>
                                <dd class="col-sm-9">{{ $organization->jobs_count }}</dd>
                            </dl>

                            @if (auth()->user()->role === 'super_admin')
                                <hr>
                                @if ($organization->employer_profiles_count > 0 || $organization->jobs_count > 0)
                                    <p class="text-muted mb-0">This organization cannot be deleted while it still has linked employees or job postings.</p>
                                @else
                                    <form action="{{ route('admin.organizations.destroy', $organization) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete organization</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
