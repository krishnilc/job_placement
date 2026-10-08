@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.employers') }}">Employers</a></li>
                            <li class="breadcrumb-item active">Edit Employer</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('admin.sidebar')
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body card-form">
                            <div class="card border-0 shadow mb-4">
                                <form action="" method="POST" id="userForm" name="userForm">
                                    @csrf
                                    <div class="card-body  p-4">
                                        <h3 class="fs-4 mb-4">Update Employer Profile</h3>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="name" class="mb-2">Name<span class="text-danger">*</span></label>
                                                <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                                                <p class="text-danger" id="nameError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="designation" class="mb-2">Designation<span class="text-danger">*</span></label>
                                                <input type="text" name="designation" id="designation" class="form-control" value="{{ $user->designation }}" placeholder="e.g. HR Manager">
                                                <p class="text-danger" id="designationError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="email" class="mb-2">Email<span class="text-danger">*</span></label>
                                                <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
                                                <p class="text-danger" id="emailError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="email_2" class="mb-2">Additional Email</label>
                                                <input type="email" name="email_2" id="email_2" class="form-control" value="{{ $user->email_2 }}">
                                                <p class="text-danger" id="email_2Error"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="mobile" class="mb-2">Mobile<span class="text-danger">*</span></label>
                                                <input type="text" name="mobile" id="mobile" class="form-control" value="{{ $user->mobile }}" maxlength="7">
                                                <p class="text-danger" id="mobileError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="mobile_2" class="mb-2">Additional Mobile</label>
                                                <input type="text" name="mobile_2" id="mobile_2" class="form-control" value="{{ $user->mobile_2 }}" maxlength="7">
                                                <p class="text-danger" id="mobile_2Error"></p>
                                            </div>
                                        </div>
                                        <div class="border-top mt-4 pt-4">
                                            <h4 class="fs-5 mb-3">Organization</h4>
                                            @include('organizations.picker')
                                            @if ($user->organizationRequest?->status === 'pending')
                                                <p><a href="{{ route('admin.organizations.review', $user->organizationRequest) }}">Review this contact's organization request</a> before activation.</p>
                                            @endif
                                            <p class="text-muted">Edit company details in <a href="{{ route('admin.organizations.index') }}">Manage Organizations</a>.</p>
                                        </div>
                                    </div>
                                    <div class="card-footer  p-4">
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                        <a href="{{ route('admin.users.employers') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script src="{{ asset('assets/js/organization-picker.js') }}"></script>
    <script>
        $('#userForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.users.update', $user->id) }}",
                type: "PUT",
                dataType: "json",
                data: $("#userForm").serializeArray(),

                success: function(response) {
                    $('#userForm .text-danger').text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('admin.users.employers') }}";
                    } else {
                        $.each(response.errors, function(field, messages) {
                            $('#' + field + 'Error').text(messages[0]);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });
    </script>
@endsection
