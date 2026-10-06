<div class="card account-nav border-0 shadow mb-4 mb-lg-0">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush ">
            @if (request()->routeIs('admin.account.*'))
                <li @class(['list-group-item d-flex justify-content-between align-items-center p-3', 'account-nav-active' => request()->routeIs('admin.account.profile')])>
                    <a href="{{ route('admin.account.profile') }}"> <i class="fa fa-arrow-right"></i> View Profile</a>
                </li>
                <li @class(['list-group-item d-flex justify-content-between align-items-center p-3', 'account-nav-active' => request()->routeIs('admin.account.editProfile')])>
                    <a href="{{ route('admin.account.editProfile') }}"> <i class="fa fa-arrow-right"></i> Profile
                        Update</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="fa fa-camera"></i> Change Profile Picture
                    </button>
                </li>
                <li @class(['list-group-item d-flex justify-content-between align-items-center p-3', 'account-nav-active' => request()->routeIs('admin.account.editPassword')])>
                    <a href="{{ route('admin.account.editPassword') }}"> <i class="fa fa-arrow-right"></i> Change Password</a>
                </li>
            @else
                @php
                    $profileRoutes = request()->routeIs('admin.users.profile', 'admin.users.edit');
                    $itemClass = 'list-group-item d-flex justify-content-between align-items-center p-3';
                    $headingClass = 'list-group-item bg-light text-uppercase text-muted small fw-bold px-3 py-2';
                @endphp

                <li class="{{ $headingClass }}">User Management</li>
                <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.users.students', 'admin.users.students.create') || ($profileRoutes && isset($user) && in_array($user->role, ['user', 'student'], true))])>
                    <a href="{{ route('admin.users.students') }}">
                        <i class="fa fa-arrow-right"></i> Students
                    </a>
                </li>
                <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.users.employers', 'admin.users.employers.create') || ($profileRoutes && isset($user) && $user->role === 'employer')])>
                    <a href="{{ route('admin.users.employers') }}">
                        <i class="fa fa-arrow-right"></i> Employers
                    </a>
                </li>
                <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.organizations.*')])>
                        <a href="{{ route('admin.organizations.index') }}">
                            <i class="fa fa-arrow-right"></i> Organizations
                        </a>
                    </li>
                @if (Auth::user()->role === 'super_admin')
                    <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.users.admins', 'admin.users.admins.create') || ($profileRoutes && isset($user) && in_array($user->role, ['admin', 'super_admin'], true))])>
                        <a href="{{ route('admin.users.admins') }}">
                            <i class="fa fa-arrow-right"></i> Admins
                        </a>
                    </li>
                @endif

                <li class="{{ $headingClass }}">Recruitment</li>
                <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.jobs', 'admin.jobs.edit', 'admin.jobs.create')])>
                    <a href="{{ route('admin.jobs') }}">
                        <i class="fa fa-arrow-right"></i> Jobs
                    </a>
                </li>
                <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.jobApplications')])>
                    <a href="{{ route('admin.jobApplications') }}">
                        <i class="fa fa-arrow-right"></i> Applications
                    </a>
                </li>
                <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.feedback')])>
                    <a href="{{ route('admin.feedback') }}">
                        <i class="fa fa-arrow-right"></i> Feedback
                    </a>
                </li>

                @if (in_array(Auth::user()->role, ['admin', 'super_admin'], true))
                    <li class="{{ $headingClass }}"> System Setup</li>                    
                    <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.colleges', 'admin.colleges.create', 'admin.colleges.edit')])>
                        <a href="{{ route('admin.colleges') }}">
                            <i class="fa fa-arrow-right"></i> Colleges/Centers
                        </a>
                    </li>
                    <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.categories', 'admin.categories.create', 'admin.categories.edit')])>
                        <a href="{{ route('admin.categories') }}">
                            <i class="fa fa-arrow-right"></i> Categories
                        </a>
                    </li>
                    <li @class([$itemClass, 'account-nav-active' => request()->routeIs('admin.jobTypes', 'admin.jobTypes.create', 'admin.jobTypes.edit')])>
                        <a href="{{ route('admin.jobTypes') }}">
                            <i class="fa fa-arrow-right"></i> Job Types
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
</div>

@unless (request()->routeIs('admin.account.*'))
<!-- <div class="card account-nav border-0 shadow mb-4 mb-lg-0"></div> -->
<div class="card border-0 shadow mt-4 p-3">
    <div class="s-body text-center mt-3">

        @if (Auth::user()->image != '')
            <img src="{{ asset('profile_pic/thumb/' . Auth::user()->image) }}" alt="avatar" class="rounded-circle img-fluid"
                style="width: 150px;">
        @else
            <img src="assets/images/avatar7.png" alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
        @endif

        <h5 class="mt-3 pb-0">{{ Auth::user()->name }}</h5>
        <p class="text-muted mb-1 fs-6">{{ Auth::user()->designation }}</p>
        <p class="text-muted mb-1 fs-6">Role: {{ Auth::user()->role }}</p>
    </div>
</div>
@endunless