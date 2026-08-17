@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            @if (auth()->user()->role == 'user')
                                <li class="breadcrumb-item"><a href="{{ route('account.dashboard') }}">Home</a></li>
                            @elseif (auth()->user()->role == 'admin')
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            @endif

                            <li class="breadcrumb-item active"><a href="{{ route('account.editProfile') }}">Account Settings</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    @if (auth()->user()->role == 'admin')
                        @include('admin.sidebar')
                    @elseif (auth()->user()->role == 'employer')
                        @include('employer.sidebar')
                    @else
                        @include('front.account.student-sidebar')
                    @endif
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <form action="" method="POST" id="userForm" name="userForm">
                            @csrf
                            <div class="card-body  p-4">
                                <h3 class="fs-4 mb-1">My Profile</h3>
                                <div class="mb-4">
                                    <label for="name" class="mb-2">Name*</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        value="{{ $user->name }}">
                                    <p class="text-danger" id="nameError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="email" class="mb-2">Email*</label>
                                    <input type="text" name="email" id="email" class="form-control"
                                        value="{{ $user->email }}">
                                    <p class="text-danger" id="emailError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="mobile" class="mb-2">Mobile</label>
                                    <input type="text" name="mobile" id="mobile" class="form-control"
                                        value="{{ $user->mobile }}">
                                    <p class="text-danger" id="mobileError"></p>
                                </div>

                                @if (in_array(auth()->user()->role, ['admin', 'employer'], true))
                                    <div class="mb-4">
                                        <label for="designation" class="mb-2">Designation*</label>
                                        <input type="text" name="designation" id="designation" class="form-control"
                                            value="{{ $user->designation }}" placeholder="e.g. CEO, Manager, HR Specialist">
                                        <p class="text-danger" id="designationError"></p>
                                    </div>
                                @elseif (auth()->user()->role === 'student')
                                    <div class="mb-4">
                                        <label for="designation" class="mb-2">Student Status*</label>
                                        <select name="designation" id="designation" class="form-control">
                                            <option value="">Select your status</option>
                                            <option value="Full-time Student" {{ $user->designation == 'Full-time Student' ? 'selected' : '' }}>Full-time Student</option>
                                            <option value="Part-time Student" {{ $user->designation == 'Part-time Student' ? 'selected' : '' }}>Part-time Student</option>
                                            <option value="Alumni" {{ $user->designation == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                        </select>
                                        <p class="text-danger" id="designationError"></p>
                                    </div>
                                @endif

                            </div>
                            <div class="card-footer  p-4">
                                <button type="submit" class="btn btn-primary">Update</button>
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
        $('#userForm').submit(function(e) {
            e.preventDefault();
            // var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('account.updateProfile') }}",
                type: "PUT",
                dataType: "json",
                data: $("#userForm").serializeArray(),

                success: function(response) {
                    // Always clear all error messages first
                    $("#nameError").text('');
                    $("#emailError").text('');
                    $("#passwordError").text('');
                    $("#confirmPasswordError").text('');
                    $("#mobileError").text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('account.editProfile') }}";
                    } else {
                        var errors = response.errors;

                        if (errors.name) {
                            $("#nameError").text(errors.name[0]);
                        }
                        if (errors.email) {
                            $("#emailError").text(errors.email[0]);
                        }
                        if (errors.designation) {
                            $("#designationError").text(errors.designation[0]);
                        }
                        if (errors.mobile) {
                            $("#mobileError").text(errors.mobile[0]);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });      
    </script>
@endsection
