@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.students') }}">Students</a></li>
                            <li class="breadcrumb-item active">Add Student</li>
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
                                <form action="" method="POST" id="studentForm" name="studentForm">
                                    @csrf
                                    <div class="card-body p-4">
                                        <h3 class="fs-4 mb-1">Add Student</h3>
                                        <div class="mb-4">
                                            <label for="name" class="mb-2">Name<span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control">
                                            <p class="text-danger" id="nameError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="email" class="mb-2">Email<span class="text-danger">*</span></label>
                                            <input type="text" name="email" id="email" class="form-control">
                                            <p class="text-danger" id="emailError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="mobile" class="mb-2">Mobile<span class="text-danger">*</span></label>
                                            <input type="text" name="mobile" id="mobile" class="form-control">
                                            <p class="text-danger" id="mobileError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="student_id" class="mb-2">Student ID<span class="text-danger">*</span></label>
                                            <input type="text" name="student_id" id="student_id" class="form-control">
                                            <p class="text-danger" id="student_idError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="password" class="mb-2">Password<span class="text-danger">*</span></label>
                                            <input type="password" name="password" id="password" class="form-control">
                                            <p class="text-danger" id="passwordError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="confirm_password" class="mb-2">Confirm Password<span class="text-danger">*</span></label>
                                            <input type="password" name="confirm_password" id="confirm_password"
                                                class="form-control">
                                            <p class="text-danger" id="confirm_passwordError"></p>
                                        </div>
                                    </div>
                                    <div class="card-footer p-4">
                                        <button type="submit" class="btn btn-primary">Create Student</button>
                                        <a href="{{ route('admin.users.students') }}" class="btn btn-secondary">Cancel</a>
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
    <script>
        $('#studentForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.users.students.store') }}",
                type: "POST",
                dataType: "json",
                data: $("#studentForm").serializeArray(),

                success: function(response) {
                    $("#nameError").text('');
                    $("#emailError").text('');
                    $("#mobileError").text('');
                    $("#student_idError").text('');
                    $("#passwordError").text('');
                    $("#confirm_passwordError").text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('admin.users.students') }}";
                    } else {
                        var errors = response.errors;

                        if (errors.name) {
                            $("#nameError").text(errors.name[0]);
                        }
                        if (errors.email) {
                            $("#emailError").text(errors.email[0]);
                        }
                        if (errors.mobile) {
                            $("#mobileError").text(errors.mobile[0]);
                        }
                        if (errors.student_id) {
                            $("#student_idError").text(errors.student_id[0]);
                        }
                        if (errors.password) {
                            $("#passwordError").text(errors.password[0]);
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
