@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('employer.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">My Organization</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">@include('employer.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <h1 class="h3 mb-3">My Organization</h1>
                            @if ($organization)
                                <p class="text-muted">Organization details are managed by an administrator. Contact the administrator if these details need updating.</p>
                                <dl class="row mb-0">
                                    @foreach (['name' => 'Organization name', 'address' => 'Address', 'phone' => 'Phone', 'email' => 'Email', 'website_url' => 'Website', 'linkedin_url' => 'LinkedIn', 'facebook_url' => 'Facebook', 'description' => 'Description'] as $field => $label)
                                        <dt class="col-sm-3">{{ $label }}</dt>
                                        <dd class="col-sm-9">
                                            @if (str_ends_with($field, '_url') && $organization->{$field})
                                                <a href="{{ $organization->{$field} }}" target="_blank" rel="noopener noreferrer">{{ $organization->{$field} }}</a>
                                            @else
                                                {{ $organization->{$field} ?: 'Not provided' }}
                                            @endif
                                        </dd>
                                    @endforeach
                                </dl>
                            @else
                                <div class="alert alert-info mb-0" role="alert">
                                    No organization is linked to your account yet. If you requested a new organization, it must be reviewed by an administrator first. Contact the administrator for assistance.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
