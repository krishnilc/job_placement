@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('employer.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Profile Update</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">@include('employer.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <form id="employerProfileForm" method="POST">
                            @csrf
                            <div class="card-body p-4">
                                <h1 class="h3 mb-4">Employer Profile</h1>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="name" class="mb-2">Name*</label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                        <p class="text-danger" id="nameError"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="designation" class="mb-2">Designation*</label>
                                        <input type="text" name="designation" id="designation" class="form-control" value="{{ old('designation', $user->designation) }}" placeholder="e.g. HR Manager" required>
                                        <p class="text-danger" id="designationError"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="mb-2">Email*</label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                        <p class="text-danger" id="emailError"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email_2" class="mb-2">Additional Email</label>
                                        <input type="email" name="email_2" id="email_2" class="form-control" value="{{ old('email_2', $user->email_2) }}">
                                        <p class="text-danger" id="email2Error"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mobile" class="mb-2">Mobile*</label>
                                        <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile', $user->mobile) }}" required>
                                        <p class="text-danger" id="mobileError"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mobile_2" class="mb-2">Additional Mobile</label>
                                        <input type="text" name="mobile_2" id="mobile_2" class="form-control" value="{{ old('mobile_2', $user->mobile_2) }}">
                                        <p class="text-danger" id="mobile2Error"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer p-4">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script>
        $('#employerProfileForm').submit(function (event) {
            event.preventDefault();
            $.ajax({
                url: "{{ route('account.updateProfile') }}",
                type: 'PUT',
                dataType: 'json',
                data: $(this).serializeArray(),
                success: function (response) {
                    if (response.status === true) {
                        window.location.href = "{{ route('employer.account.profile') }}";
                        return;
                    }
                    $.each(response.errors || {}, function (field, messages) {
                        $('#' + field.replace('_', '') + 'Error').text(messages[0]);
                    });
                },
                error: function () {
                    alert('Unable to update your profile. Please try again.');
                }
            });
        });
    </script>
@endsection
