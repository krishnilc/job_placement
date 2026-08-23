@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            @if (auth()->user()->role == 'admin')
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            @elseif (auth()->user()->role == 'employer')
                                <li class="breadcrumb-item"><a href="{{ route('employer.dashboard') }}">Home</a></li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">Change Password</li>
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
                        <form action="" method="POST" id="changePasswordForm" name="changePasswordForm">
                            @csrf
                            <div class="card-body p-4">
                                <h3 class="fs-4 mb-1">Change Password</h3>
                                <div class="mb-4">
                                    <label for="old_password" class="mb-2">Old Password*</label>
                                    <input type="password" name="old_password" id="old_password" class="form-control"
                                        required>
                                    <p class="text-danger" id="old_passwordError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="new_password" class="mb-2">New Password*</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control"
                                        required>
                                    <p class="text-danger" id="new_passwordError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="confirm_password" class="mb-2">Confirm Password*</label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                        class="form-control" required>
                                    <p class="text-danger" id="confirm_passwordError"></p>
                                </div>
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
        $('#changePasswordForm').submit(function(e) {
            e.preventDefault();
            // var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('account.updatePassword') }}",
                type: "POST",
                dataType: "json",
                data: $("#changePasswordForm").serializeArray(),

                success: function(response) {
                    // Always clear all error messages first
                    $("#old_passwordError").text('');
                    $("#new_passwordError").text('');
                    $("#confirm_passwordError").text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('account.editProfile') }}";
                    } else {
                        var errors = response.errors;

                        if (errors.old_password) {
                            $("#old_passwordError").text(errors.old_password[0]);
                        }
                        if (errors.new_password) {
                            $("#new_passwordError").text(errors.new_password[0]);
                        }
                        if (errors.confirm_password) {
                            $("#confirm_passwordError").text(errors.confirm_password[0]);
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
