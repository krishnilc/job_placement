@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row"><div class="col"><nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li><li class="breadcrumb-item active" aria-current="page">Account Settings</li></ol></nav></div></div>
            <div class="row">
                <div class="col-lg-3">@include('admin.sidebar')</div>
                <div class="col-lg-9">
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-muted mb-1">Administrator account</p><h1 class="h3 mb-0">Admin Profile</h1></div><a href="{{ route('admin.account.editProfile') }}" class="btn btn-primary"><i class="fa fa-pencil me-1"></i> Edit Profile</a></div>
                            <div class="row g-3">
                                @include('front.account.profile-field', ['icon' => 'user', 'label' => 'Name', 'value' => $user->name])
                                @include('front.account.profile-field', ['icon' => 'shield', 'label' => 'Role', 'value' => ucfirst($user->role)])
                                @include('front.account.profile-field', ['icon' => 'briefcase', 'label' => 'Designation', 'value' => $user->designation])
                                @include('front.account.profile-field', ['icon' => 'envelope-o', 'label' => 'Email', 'value' => $user->email])
                                @include('front.account.profile-field', ['icon' => 'envelope-o', 'label' => 'Additional Email', 'value' => $user->email_2])
                                @include('front.account.profile-field', ['icon' => 'phone', 'label' => 'Mobile', 'value' => $user->mobile])
                                @include('front.account.profile-field', ['icon' => 'phone', 'label' => 'Additional Mobile', 'value' => $user->mobile_2])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
