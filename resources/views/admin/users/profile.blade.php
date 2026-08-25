@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.students') }}">Students</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Profile</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    @include('admin.sidebar')
                </div>
                <div class="col-lg-9">
                    <div class="admin-student-profile card border-0 shadow mb-4">
                        <div class="student-profile-header">
                            <div class="student-profile-avatar">
                                @if ($user->image)
                                    <button type="button" class="student-profile-avatar-button" data-bs-toggle="modal"
                                        data-bs-target="#studentProfileImageModal" aria-label="View {{ $user->name }}'s profile picture">
                                        <img src="{{ asset('profile_pic/thumb/' . $user->image) }}" alt="{{ $user->name }}">
                                    </button>
                                @else
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                @endif
                            </div>
                            <div>
                                <p class="student-profile-eyebrow mb-1">Student profile</p>
                                <h1 class="student-profile-name mb-1">{{ $user->name }}</h1>
                                <p class="student-profile-meta mb-0">Student ID: {{ $user->student_id ?: 'Not provided' }}</p>
                                <p class="profile-role mb-0">{{ $user->designation}}</p>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                                <div>
                                    <h2 class="fs-4 mb-1">Profile details</h2>
                                    <p class="text-muted mb-0">Student information submitted to the placement portal.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.users.students') }}" class="btn btn-outline-secondary">Back to Students</a>
                                    <a href="{{ route('admin.users.edit', [$user->id, 'list_type' => 'students']) }}" class="btn btn-primary">Edit Student</a>
                                </div>
                            </div>

                            <div class="row g-3">
                                @include('admin.users.profile-field', ['label' => 'Status', 'value' => $user->status === 'pending' ? 'Pending Approval' : ucfirst($user->status)])
                                @include('admin.users.profile-field', ['label' => 'Email', 'value' => $user->email])
                                @include('admin.users.profile-field', ['label' => 'Additional Email', 'value' => $user->email_2])
                                @include('admin.users.profile-field', ['label' => 'Mobile', 'value' => $user->mobile])
                                @include('admin.users.profile-field', ['label' => 'Additional Mobile', 'value' => $user->mobile_2])
                                @include('admin.users.profile-field', ['label' => 'Student Status', 'value' => $user->designation])
                            </div>

                            <div class="profile-divider"><h3>Personal details</h3></div>
                            <div class="row g-3">
                                @include('admin.users.profile-field', ['label' => 'Date of Birth', 'value' => $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F j, Y') : 'Not provided'])
                                @include('admin.users.profile-field', ['label' => 'Gender', 'value' => $user->gender])
                                @include('admin.users.profile-field', ['label' => 'Residential Address', 'value' => $user->residential_address, 'wide' => true])
                                @include('admin.users.profile-field', ['label' => 'Postal Address', 'value' => $user->postal_address, 'wide' => true])
                                @include('admin.users.profile-field', ['label' => 'City', 'value' => $user->city])
                                @include('admin.users.profile-field', ['label' => 'Country', 'value' => $user->country])
                            </div>

                            <div class="profile-divider"><h3>Education</h3></div>
                            <div class="row g-3">
                                @include('admin.users.profile-field', ['label' => 'High School', 'value' => $user->high_school])
                                @include('admin.users.profile-field', ['label' => 'High School Graduation Year', 'value' => $user->high_school_graduation_year])
                                @include('admin.users.profile-field', ['label' => 'University', 'value' => $user->university])
                                @include('admin.users.profile-field', ['label' => 'Degree / Program', 'value' => $user->degree])
                                @include('admin.users.profile-field', ['label' => 'Major / Specialization', 'value' => $user->major])
                                @include('admin.users.profile-field', ['label' => 'Graduation Year', 'value' => $user->graduation_year])
                            </div>

                            <div class="profile-divider"><h3>About and links</h3></div>
                            <div class="row g-3">
                                @include('admin.users.profile-field', ['label' => 'Skills', 'value' => $user->skills, 'wide' => true])
                                @include('admin.users.profile-field', ['label' => 'Short Bio', 'value' => $user->bio, 'wide' => true])
                                @include('admin.users.profile-field', ['label' => 'LinkedIn', 'value' => $user->linkedin_url, 'link' => true])
                                @include('admin.users.profile-field', ['label' => 'Facebook', 'value' => $user->facebook_url, 'link' => true])
                                @include('admin.users.profile-field', ['label' => 'Availability', 'value' => $user->availability])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($user->image)
        <div class="modal fade" id="studentProfileImageModal" tabindex="-1"
            aria-labelledby="studentProfileImageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content student-profile-image-modal-content">
                    <div class="modal-header border-0">
                        <h2 class="modal-title fs-5" id="studentProfileImageModalLabel">{{ $user->name }}'s Profile Picture</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-3 p-md-4">
                        <img src="{{ asset('profile_pic/' . $user->image) }}" alt="{{ $user->name }}" class="student-profile-image-large">
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('customJS')
    <style>
        .student-profile-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; color: #fff; background: linear-gradient(120deg, #174a68, #237d83); }
        .student-profile-avatar { display: flex; align-items: center; justify-content: center; width: 72px; height: 72px; flex: 0 0 72px; overflow: hidden; color: #237d83; font-size: 1.75rem; background: #fff; border: 3px solid rgba(255, 255, 255, .35); border-radius: 50%; }
        .student-profile-avatar-button { width: 100%; height: 100%; padding: 0; border: 0; background: transparent; cursor: zoom-in; }
        .student-profile-avatar-button:focus-visible { outline: 3px solid #ffc107; outline-offset: 3px; border-radius: 50%; }
        .student-profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .student-profile-eyebrow { color: rgba(255, 255, 255, .72); font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .student-profile-name { color: #fff; font-size: 1.65rem; font-weight: 700; }
        .student-profile-meta { color: rgba(255, 255, 255, .85); }
        .profile-divider { margin: 1.75rem 0 1rem; padding-top: 1.25rem; border-top: 1px solid #e5edf1; }
        .profile-divider h3 { margin: 0; color: #183b56; font-size: 1.05rem; font-weight: 700; }
        .admin-profile-field { min-height: 68px; padding: .85rem 1rem; background: #f5f9fa; border: 1px solid #edf3f5; border-radius: .4rem; }
        .admin-profile-label { display: block; margin-bottom: .25rem; color: #6f8291; font-size: .72rem; font-weight: 700; text-transform: uppercase; }
        .admin-profile-value { display: block; overflow-wrap: anywhere; color: #183b56; line-height: 1.45; }
        .admin-profile-empty { color: #9aaab3; font-style: italic; }
        .admin-profile-link { color: #16727c; text-decoration: none; }
        .admin-profile-link:hover { color: #174a68; text-decoration: underline; }
        .student-profile-image-modal-content { border: 0; border-radius: .75rem; overflow: hidden; }
        .student-profile-image-large { display: block; max-width: 100%; max-height: 75vh; margin: 0 auto; object-fit: contain; }
        @media (max-width: 576px) { .student-profile-header { align-items: flex-start; flex-direction: column; } }
    </style>
@endsection
