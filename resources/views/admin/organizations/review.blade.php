@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-3">@include('admin.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <h1 class="h3">Review Organization Request</h1>
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body">
                            <h2 class="h5">{{ $organizationRequest->name }}</h2>
                            <p>{{ $organizationRequest->address }}</p>
                            <p>Organization phone: {{ $organizationRequest->phone }}</p>
                            <p>Contact: {{ $organizationRequest->user?->name ?? 'Deleted contact' }} ({{ $organizationRequest->user?->email }})</p>
                            <p>Status: {{ ucfirst($organizationRequest->status) }}</p>
                            @if ($organizationRequest->organization)<p>Organization: {{ $organizationRequest->organization->name }}</p>@endif
                            @if ($organizationRequest->review_notes)<p>Review notes: {{ $organizationRequest->review_notes }}</p>@endif
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <h2 class="h5">Search existing organizations before approving</h2>
                    <form method="GET" class="d-flex gap-2 mb-3">
                        <label for="search" class="visually-hidden">Organization name</label>
                        <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Search organization names">
                        <button class="btn btn-primary">Search</button>
                        <a href="{{ route('admin.organizations.review', [$organizationRequest, 'search' => '']) }}" class="btn btn-outline-secondary">Show all</a>
                    </form>
                    @foreach ($organizations as $organization)
                        <div class="card mb-2">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><strong>{{ $organization->name }}</strong><div>{{ $organization->address }}</div></div>
                                @if ($organizationRequest->status === 'pending')
                                    <form method="POST" action="{{ route('admin.organizations.resolve', $organizationRequest) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="decision" value="link">
                                        <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                                        <button class="btn btn-outline-primary">Link to this organization</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if ($organizations->isEmpty())<p class="text-muted">No existing organizations match this search.</p>@endif
                    {{ $organizations->links() }}
                    @if ($organizationRequest->status === 'pending')
                        <form class="card card-body mt-4" method="POST" action="{{ route('admin.organizations.resolve', $organizationRequest) }}">
                            @csrf @method('PATCH')
                            <label for="review_notes" class="form-label">Review notes (required for rejection)</label>
                            <textarea id="review_notes" name="review_notes" class="form-control mb-3" maxlength="2000">{{ old('review_notes') }}</textarea>
                            <p class="text-muted">Approving creates a shared organization. The contact stays pending until separately approved in Manage Employers.</p>
                            <div class="d-flex gap-2">
                                <button name="decision" value="approve" class="btn btn-success">Approve new organization</button>
                                <button name="decision" value="reject" class="btn btn-outline-danger">Reject request</button>
                            </div>
                        </form>
                    @endif
                    <a class="btn btn-link mt-3" href="{{ route('admin.organizations.index') }}">Back to Organizations</a>
                </div>
            </div>
        </div>
    </section>
@endsection
