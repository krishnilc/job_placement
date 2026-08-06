<div class="card border-0 shadow mb-4 p-3">
    <div class="s-body text-center mt-3">

        @if (Auth::user()->image != '')
            <img src="{{ asset('profile_pic/thumb/' . Auth::user()->image) }}" alt="avatar"
                class="rounded-circle img-fluid" style="width: 150px;">
        @else
            <img src="assets/images/avatar7.png" alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
        @endif

        <h5 class="mt-3 pb-0">{{ Auth::user()->name }}</h5>
        <p class="text-muted mb-1 fs-6">{{ Auth::user()->designation }}</p>
        <p class="text-muted mb-1 fs-6">Role: {{ Auth::user()->role }}</p>
        @if (request()->routeIs('account.profile'))
            <div class="d-flex justify-content-center mb-2">
                <button data-bs-toggle="modal" data-bs-target="#exampleModal" type="button"
                    class="btn btn-primary">Change
                    Profile Picture</button>
            </div>
        @endif
    </div>
</div>
<div class="card account-nav border-0 shadow mb-4 mb-lg-0">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush ">
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                @if(in_array(auth()->user()->role, ['admin', 'employer'], true))
                    <a href="{{ route('account.createJob') }}">
                        <i class="fa fa-arrow-right"></i> Create Job
                    </a>
                @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <a href="{{ route('account.myJobs') }}">
                    <i class="fa fa-arrow-right"></i> My Jobs
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <a href="{{ route('admin.jobApplications') }}">
                    <i class="fa fa-arrow-right"></i> Review Applications
                </a>
            </li>
        </ul>
    </div>
</div>
