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
                            <li class="breadcrumb-item active">{{ $organization->exists ? 'Edit Organization' : 'Add Organization' }}</li>
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
                            <h1 class="h3">{{ $organization->exists ? 'Edit Organization' : 'Add Organization' }}</h1>
                            <!-- <p class="text-muted">Shared details apply to every linked contact. Search existing records before adding an organization.</p> -->
                            <form method="POST" action="{{ $organization->exists ? route('admin.organizations.update', $organization) : route('admin.organizations.store') }}">
                                @csrf
                                @if ($organization->exists) @method('PUT') @endif
                                @foreach (['name' => 'Organization name', 'address' => 'Address', 'phone' => 'Phone', 'head_office_address' => 'Head office address', 'website_url' => 'Website', 'linkedin_url' => 'LinkedIn page', 'facebook_url' => 'Facebook page', 'description' => 'Description'] as $field => $label)
                                    <div class="mb-3">
                                        <label for="{{ $field }}" class="form-label">{{ $label }}@if (in_array($field, ['name', 'address'])) <span class="text-danger">*</span>@endif</label>
                                        @if (in_array($field, ['address', 'head_office_address', 'description']))
                                            <textarea id="{{ $field }}" name="{{ $field }}" class="form-control" rows="3">{{ old($field, $organization->{$field}) }}</textarea>
                                        @else
                                            <input id="{{ $field }}" name="{{ $field }}" type="{{ str_ends_with($field, '_url') ? 'url' : ($field === 'phone' ? 'tel' : 'text') }}" class="form-control" value="{{ old($field, $organization->{$field}) }}">
                                        @endif
                                        @error($field)<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                                <button class="btn btn-primary">Save Organization</button>
                                <!-- <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">Cancel</a> -->
                                <a href="{{ route('admin.organizations.index') }}" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
