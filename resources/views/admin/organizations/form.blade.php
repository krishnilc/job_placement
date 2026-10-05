@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-3">@include('admin.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <h1 class="h3">{{ $organization->exists ? 'Edit Organization' : 'Add Organization' }}</h1>
                            <p class="text-muted">Shared details apply to every linked contact. Search existing records before adding an organization.</p>
                            <form method="POST" action="{{ $organization->exists ? route('admin.organizations.update', $organization) : route('admin.organizations.store') }}">
                                @csrf
                                @if ($organization->exists) @method('PUT') @endif
                                @foreach (['name' => 'Organization name', 'address' => 'Address', 'postal_address' => 'Postal address', 'website_url' => 'Website', 'description' => 'Description', 'linkedin_url' => 'LinkedIn page', 'facebook_url' => 'Facebook page'] as $field => $label)
                                    <div class="mb-3">
                                        <label for="{{ $field }}" class="form-label">{{ $label }}{{ in_array($field, ['name', 'address']) ? '*' : '' }}</label>
                                        @if (in_array($field, ['address', 'postal_address', 'description']))
                                            <textarea id="{{ $field }}" name="{{ $field }}" class="form-control" rows="3">{{ old($field, $organization->{$field}) }}</textarea>
                                        @else
                                            <input id="{{ $field }}" name="{{ $field }}" type="{{ str_ends_with($field, '_url') ? 'url' : 'text' }}" class="form-control" value="{{ old($field, $organization->{$field}) }}">
                                        @endif
                                        @error($field)<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                                <button class="btn btn-primary">Save Organization</button>
                                <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
