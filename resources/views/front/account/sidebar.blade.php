<div class="card border-0 shadow mb-4 p-3">
    <div class="s-body text-center mt-3">

        @if (Auth::user()->image != '')
            <img src="{{ asset('profile_pic/thumb/' . Auth::user()->image) }}" alt="avatar"
                class="rounded-circle img-fluid" style="width: 150px;">
        @else
            <img src="assets/images/avatar7.png" alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
        @endif

        <h5 class="mt-3 pb-0">{{ Auth::user()->name }}</h5>
        <p class="text-muted mb-1 fs-6">{{ Auth::user()->role }}</p>
        <div class="d-flex justify-content-center mb-2">
            <button data-bs-toggle="modal" data-bs-target="#exampleModal" type="button" class="btn btn-primary">Change
                Profile Picture</button>
        </div>
    </div>
</div>
<div class="card account-nav border-0 shadow mb-4 mb-lg-0">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush ">
            <li class="list-group-item d-flex justify-content-between p-3">
                <a href="{{ route('account.dashboard') }}"> <i class="fa fa-arrow-right"></i> Home</a>
            </li>
            <li class="list-group-item d-flex justify-content-between p-3">
                <a href="{{ route('account.profile') }}"> <i class="fa fa-arrow-right"></i> Account Settings</a>
            </li>
            @if (Auth::user()->role == 'employer' || Auth::user()->role == 'admin')
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <a href="{{ route('account.createJob') }}"> <i class="fa fa-arrow-right"></i> Post a Job</a>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <a href="{{ route('account.myJobs') }}"> <i class="fa fa-arrow-right"></i> My Jobs</a>
                </li>
            @endif
            @if (Auth::user()->role == 'user')
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <a href="{{ route('account.myJobApplications') }}"> <i class="fa fa-arrow-right"></i> Jobs Applied</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <a href="{{ route('account.savedJobs') }}"> <i class="fa fa-arrow-right"></i> Saved Jobs</a>
                </li>
            @endif
            {{-- <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <a href="{{ route('account.logout') }}">Logout</a>
            </li> --}}
        </ul>
    </div>
</div>
