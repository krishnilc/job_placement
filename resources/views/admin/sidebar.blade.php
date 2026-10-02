@unless (request()->routeIs('admin.account.*'))
<div class="card border-0 shadow mb-4 p-3">
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

                {{-- <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <a href="{{ route('admin.account.profile') }}">
                        <i class="fa fa-arrow-right"></i> Account Settings
                    </a>
                </li> --}}

                <li @class(['list-group-item d-flex justify-content-between align-items-center p-3', 'account-nav-active' => request()->routeIs('admin.jobs', 'admin.jobs.edit')])>
                    <a href="{{ route('admin.jobs') }}">
                        <i class="fa fa-arrow-right"></i> Manage Jobs
                    </a>
                </li>
                <li @class(['list-group-item d-flex justify-content-between align-items-center p-3', 'account-nav-active' => request()->routeIs('account.createJob')])>
                    @if (in_array(auth()->user()->role, ['super_admin', 'admin', 'employer'], true))
                        <a href="{{ route('account.createJob') }}">
                            <i class="fa fa-arrow-right"></i> Create Job
                        </a>
                    @endif
                </li>
                <li @class(['list-group-item d-flex justify-content-between align-items-center p-3', 'account-nav-active' => request()->routeIs('account.myJobs', 'account.editJob')])>
                    <a href="{{ route('account.myJobs') }}">
                        <i class="fa fa-arrow-right"></i> My Jobs
                    </a>
                </li>
                <li @class(['list-group-item d-flex justify-content-between align-items-center p-3', 'account-nav-active' => request()->routeIs('admin.jobApplications')])>
                    <a href="{{ route('admin.jobApplications') }}">
                        <i class="fa fa-arrow-right"></i> Review Applications
                    </a>
                </li>

                @if (in_array(Auth::user()->role, ['admin', 'super_admin'], true))
                    <li @class(['list-group-item d-flex justify-content-between p-3', 'account-nav-active' => request()->routeIs('admin.colleges', 'admin.colleges.create', 'admin.colleges.edit')])>
                        <a href="{{ route('admin.colleges') }}">
                            <i class="fa fa-arrow-right"></i> Manage Colleges
                        </a>
                    </li>
                    <li @class(['list-group-item d-flex justify-content-between p-3', 'account-nav-active' => request()->routeIs('admin.categories', 'admin.categories.create', 'admin.categories.edit')])>
                        <a href="{{ route('admin.categories') }}">
                            <i class="fa fa-arrow-right"></i> Manage Categories
                        </a>
                    </li>
                @endif

                <li @class(['list-group-item d-flex justify-content-between p-3', 'account-nav-active' => request()->routeIs('admin.users.students') || (request()->routeIs('admin.users.profile', 'admin.users.edit') && isset($user) && in_array($user->role, ['user', 'student'], true))])>
                    <a href="{{ route('admin.users.students') }}">
                        <i class="fa fa-arrow-right"></i> Manage Students
                    </a>
                </li>
                <li @class(['list-group-item d-flex justify-content-between p-3', 'account-nav-active' => request()->routeIs('admin.users.employers') || (request()->routeIs('admin.users.profile', 'admin.users.edit') && isset($user) && $user->role === 'employer')])>
                    <a href="{{ route('admin.users.employers') }}">
                        <i class="fa fa-arrow-right"></i> Manage Employers
                    </a>
                </li>
                @if (Auth::user()->role === 'super_admin')
                    <li @class(['list-group-item d-flex justify-content-between p-3', 'account-nav-active' => request()->routeIs('admin.users.admins', 'admin.users.admins.create') || (request()->routeIs('admin.users.profile', 'admin.users.edit') && isset($user) && in_array($user->role, ['admin', 'super_admin'], true))])>
                        <a href="{{ route('admin.users.admins') }}">
                            <i class="fa fa-arrow-right"></i> Manage Admins
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
</div>