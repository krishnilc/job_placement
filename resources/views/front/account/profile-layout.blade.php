@php
    $isAdmin = $user->role === 'admin';
    $isEmployer = $user->role === 'employer';
    $homeRoute = $isAdmin ? 'admin.dashboard' : ($isEmployer ? 'employer.dashboard' : 'student.dashboard');
    $profileRoute = $isAdmin ? 'admin.account.profile' : ($isEmployer ? 'employer.account.profile' : 'account.profile');
    $editRoute = $isAdmin ? 'admin.account.editProfile' : ($isEmployer ? 'employer.account.editProfile' : 'account.editProfile');
@endphp

<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row"><div class="col"><nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route($homeRoute) }}">Home</a></li><li class="breadcrumb-item active" aria-current="page">View Profile</li></ol></nav></div></div>
        <div class="row">
            <div class="col-lg-3">
                @if ($isAdmin) @include('admin.sidebar') @elseif ($isEmployer) @include('employer.sidebar') @else @include('front.account.student-sidebar') @endif
            </div>
            <div class="col-lg-9">
                <div class="profile-page">
                    <div class="profile-hero {{ $isEmployer ? 'profile-hero-employer' : ($isAdmin ? 'profile-hero-admin' : '') }} mb-4">
                        <div class="profile-hero-content">
                            <div class="profile-avatar">
                                @if ($user->image)
                                    <button type="button" class="profile-avatar-button" data-bs-toggle="modal" data-bs-target="#profileImageModal" aria-label="View {{ $user->name }}'s profile picture"><img src="{{ asset('profile_pic/thumb/' . $user->image) }}" alt="{{ $user->name }}"></button>
                                @else
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                @endif
                            </div>
                            <div>
                                <p class="profile-eyebrow mb-1">{{ $isAdmin ? 'Administrator profile' : ($isEmployer ? 'Employer profile' : 'Personal profile') }}</p>
                                <h1 class="profile-name mb-1">{{ $user->name }}</h1>
                                @if ($isEmployer)
                                    <p class="profile-student-id mb-1">{{ $user->company_name ?: 'Company not provided' }}</p>
                                @elseif ($isAdmin)
                                    <p class="profile-student-id mb-1">{{ ucfirst($user->role) }}</p>
                                @else
                                    <p class="profile-student-id mb-1">Student ID: {{ $user->student_id }}</p>
                                @endif
                                <p class="profile-role mb-0">{{ $user->designation ?: 'Status Not Provided' }}</p>
                            </div>
                        </div>
                        <a href="{{ route($editRoute) }}" class="btn btn-light profile-edit-button"><i class="fa fa-pencil me-1" aria-hidden="true"></i> Edit Profile</a>
                    </div>

                    <div class="profile-section mb-4">
                        <div class="profile-section-heading"><span class="profile-section-icon"><i class="fa fa-address-card-o" aria-hidden="true"></i></span><div><h2 class="profile-section-title">Contact information</h2><p class="profile-section-caption">Your account contact details</p></div></div>
                        <div class="row g-3">
                            @include('front.account.profile-field', ['icon' => 'envelope-o', 'label' => 'Email', 'value' => $user->email])
                            @include('front.account.profile-field', ['icon' => 'envelope-o', 'label' => 'Additional Email', 'value' => $user->email_2])
                            @include('front.account.profile-field', ['icon' => 'phone', 'label' => 'Mobile', 'value' => $user->mobile])
                            @include('front.account.profile-field', ['icon' => 'phone', 'label' => 'Additional Mobile', 'value' => $user->mobile_2])
                        </div>
                    </div>

                    @if ($isEmployer)
                        <div class="profile-section mb-4"><div class="profile-section-heading"><span class="profile-section-icon"><i class="fa fa-building-o" aria-hidden="true"></i></span><div><h2 class="profile-section-title">Company information</h2><p class="profile-section-caption">Your employer account details</p></div></div><div class="row g-3">
                            @include('front.account.profile-field', ['icon' => 'building-o', 'label' => 'Company Name', 'value' => $user->company_name])
                            @include('front.account.profile-field', ['icon' => 'briefcase', 'label' => 'Designation', 'value' => $user->designation])
                            @include('front.account.profile-field', ['icon' => 'map-marker', 'label' => 'Company Address', 'value' => $user->company_address, 'wide' => true])
                            @include('front.account.profile-field', ['icon' => 'globe', 'label' => 'Website', 'value' => $user->website_url, 'link' => true])
                            @include('front.account.profile-field', ['icon' => 'linkedin', 'label' => 'LinkedIn Page', 'value' => $user->linkedin_url, 'link' => true])
                            @include('front.account.profile-field', ['icon' => 'facebook', 'label' => 'Facebook Page', 'value' => $user->facebook_url, 'link' => true])
                            @include('front.account.profile-field', ['icon' => 'file-text-o', 'label' => 'Company Description', 'value' => $user->company_description, 'wide' => true])
                        </div></div>
                    @elseif ($isAdmin)
                        <div class="profile-section mb-4"><div class="profile-section-heading"><span class="profile-section-icon"><i class="fa fa-shield" aria-hidden="true"></i></span><div><h2 class="profile-section-title">Account information</h2><p class="profile-section-caption">Your administrator access details</p></div></div><div class="row g-3">
                            @include('front.account.profile-field', ['icon' => 'shield', 'label' => 'Role', 'value' => ucfirst($user->role)])
                            @include('front.account.profile-field', ['icon' => 'briefcase', 'label' => 'Designation', 'value' => $user->designation])
                        </div></div>
                    @else
                        <div class="profile-section mb-4"><div class="profile-section-heading"><span class="profile-section-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></span><div><h2 class="profile-section-title">Personal details</h2><p class="profile-section-caption">Your location and personal information</p></div></div><div class="row g-3">
                            @include('front.account.profile-field', ['icon' => 'calendar', 'label' => 'Date of Birth', 'value' => $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F j, Y') : 'Not provided'])
                            @include('front.account.profile-field', ['icon' => 'venus-mars', 'label' => 'Gender', 'value' => $user->gender])
                            @include('front.account.profile-field', ['icon' => 'home', 'label' => 'Residential Address', 'value' => $user->residential_address, 'wide' => true])
                            @include('front.account.profile-field', ['icon' => 'mail-forward', 'label' => 'Postal Address', 'value' => $user->postal_address, 'wide' => true])
                            @include('front.account.profile-field', ['icon' => 'building-o', 'label' => 'City', 'value' => $user->city])
                            @include('front.account.profile-field', ['icon' => 'globe', 'label' => 'Country', 'value' => $user->country])
                        </div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if ($user->image)
    <div class="modal fade" id="profileImageModal" tabindex="-1" aria-labelledby="profileImageModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content profile-image-modal-content"><div class="modal-header border-0"><h2 class="modal-title fs-5" id="profileImageModalLabel">{{ $user->name }}'s Profile Picture</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body text-center p-3 p-md-4"><img src="{{ asset('profile_pic/' . $user->image) }}" alt="{{ $user->name }}" class="profile-image-large"></div></div></div></div>
@endif

<style>
    .profile-page { --profile-ink: #183b56; --profile-muted: #6f8291; --profile-line: #e5edf1; --profile-soft: #f5f9fa; }
    .profile-hero { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 2rem; color: #fff; background: linear-gradient(120deg, #174a68 0%, #237d83 100%); border-radius: .75rem; box-shadow: 0 .5rem 1.5rem rgba(24, 59, 86, .16); }
    .profile-hero-employer { background: linear-gradient(120deg, #7a4b20 0%, #b8782e 100%); box-shadow: 0 .5rem 1.5rem rgba(122, 75, 32, .18); }
    .profile-hero-admin { background: linear-gradient(120deg, #263d73 0%, #3568a8 100%); box-shadow: 0 .5rem 1.5rem rgba(38, 61, 115, .18); }
    .profile-hero-content { display: flex; align-items: center; gap: 1rem; min-width: 0; }
    .profile-avatar { display: flex; align-items: center; justify-content: center; width: 84px; height: 84px; flex: 0 0 84px; overflow: hidden; color: #237d83; font-size: 2rem; background: #fff; border: 4px solid rgba(255, 255, 255, .35); border-radius: 50%; }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-avatar-button { width: 100%; height: 100%; padding: 0; border: 0; background: transparent; cursor: zoom-in; }
    .profile-eyebrow { color: rgba(255, 255, 255, .72); font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .profile-name { color: #fff; font-size: clamp(1.5rem, 3vw, 2.1rem); font-weight: 700; }
    .profile-student-id, .profile-role { color: rgba(255, 255, 255, .85); }
    .profile-edit-button { white-space: nowrap; color: var(--profile-ink); border: 0; }
    .profile-section { padding: 1.5rem; background: #fff; border: 1px solid var(--profile-line); border-radius: .65rem; box-shadow: 0 .25rem .9rem rgba(24, 59, 86, .05); }
    .profile-section-heading { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
    .profile-section-icon { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; flex: 0 0 38px; color: #237d83; background: #e7f4f2; border-radius: .5rem; }
    .profile-section-title { margin: 0; color: var(--profile-ink); font-size: 1.05rem; font-weight: 700; }
    .profile-section-caption { margin: .2rem 0 0; color: var(--profile-muted); font-size: .82rem; }
    .profile-field { display: flex; align-items: flex-start; gap: .75rem; min-height: 70px; padding: .9rem; background: var(--profile-soft); border: 1px solid #edf3f5; border-radius: .45rem; }
    .profile-field-icon { width: 28px; padding-top: .15rem; flex: 0 0 28px; color: #237d83; text-align: center; }
    .profile-field-content { min-width: 0; }
    .profile-field-label { display: block; margin-bottom: .25rem; color: var(--profile-muted); font-size: .74rem; font-weight: 700; text-transform: uppercase; }
    .profile-field-value { display: block; overflow-wrap: anywhere; color: var(--profile-ink); font-size: .94rem; line-height: 1.45; }
    .profile-empty { color: #9aaab3; font-style: italic; }
    .profile-link { color: #16727c; text-decoration: none; }
    .profile-link:hover { color: #174a68; text-decoration: underline; }
    .profile-image-modal-content { border: 0; border-radius: .75rem; overflow: hidden; }
    .profile-image-large { display: block; max-width: 100%; max-height: 75vh; margin: 0 auto; object-fit: contain; }
    @media (max-width: 576px) { .profile-hero { align-items: flex-start; flex-direction: column; padding: 1.35rem; } .profile-edit-button { width: 100%; } .profile-section { padding: 1rem; } }
</style>
