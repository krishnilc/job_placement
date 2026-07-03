@extends('front.layouts.app')

@section('main')
    <section class="section-5">
        <div class="container my-5">
            <div class="py-lg-2">&nbsp;</div>
            <div class="row d-flex justify-content-center">
                <div class="col-md-5">
                    <div class="card shadow border-0 p-5">
                        <h1 class="h3">Register</h1>
                        <form name="registrationForm" id="registrationForm">
                            @csrf
                            <div class="mb-3">
                                <label for="" class="mb-2">Name*</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Enter Name" required>
                                <p class="text-danger" id="nameError"></p>
                            </div>

                            <div class="mb-3">
                                <label for="" class="mb-2">Email*</label>
                                <input type="text" name="email" id="email" class="form-control"
                                    placeholder="Enter Email" required>
                                <p class="text-danger" id="emailError"></p>
                            </div>

                            <div class="mb-3">
                                <label for="" class="mb-2">Password*</label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Enter Password" required>
                                <p class="text-danger" id="passwordError"></p>
                            </div>

                            <div class="mb-3">
                                <label for="" class="mb-2">Confirm Password*</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                    placeholder="Please confirm Password" required>
                                <p class="text-danger" id="confirmPasswordError"></p>
                            </div>

                            <div class="mb-4 col-md-6">
                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" value="student" id="student_role"
                                        name="role" checked>
                                    <label class="form-check-label">
                                        Student
                                    </label>
                                </div>

                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" value="employer" id="employer_role"
                                        name="role">
                                    <label class="form-check-label">
                                        Employer
                                    </label>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-2">Register</button>
                        </form>
                    </div>
                    <div class="mt-4 text-center">
                        <p>Have an account? <a href="{{ route('account.login') }}">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script>
        $("#registrationForm").submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('account.processRegistration') }}",
                type: "POST",
                data: $("#registrationForm").serialize(),
                dataType: "json",

                success: function(response) {
                    // Always clear all error messages first
                    $("#nameError").text('');
                    $("#emailError").text('');
                    $("#passwordError").text('');
                    $("#confirmPasswordError").text('');

                    if (response.status == false) {
                        var errors = response.errors;
                        if (errors.name) {
                            $("#nameError").text(errors.name[0]);
                        }
                        if (errors.email) {
                            $("#emailError").text(errors.email[0]);
                        }
                        if (errors.password) {
                            $("#passwordError").text(errors.password[0]);
                        }
                        if (errors.confirm_password) {
                            $("#confirmPasswordError").text(errors.confirm_password[0]);
                        }
                    } else {
                        window.location.href = "{{ route('account.login') }}";
                        $("#registrationForm")[0].reset();
                    }
                }
            });
        });
    </script>
@endsection
