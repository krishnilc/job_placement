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
                                        <h3 class="fs-4 mb-1">Employer/Edit</h3>
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
                                            <label for="designation" class="mb-2">Designation*</label>
                                            <input type="text" name="designation" id="designation" class="form-control"
                                                value="{{ $user->designation }}">
                                            <p class="text-danger" id="designationError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="company_name" class="mb-2">Company Name*</label>
                                            <input type="text" name="company_name" id="company_name" class="form-control"
                                                value="{{ $user->company_name }}">
                                            <p class="text-danger" id="company_nameError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="mobile" class="mb-2">Mobile*</label>
                                            <input type="text" name="mobile" id="mobile" class="form-control"
                                                value="{{ $user->mobile }}">
                                            <p class="text-danger" id="mobileError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="role" class="mb-2">Role*</label>
                                            <select name="role" id="role" class="form-control">
                                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="student" {{ in_array($user->role, ['user', 'student'], true) ? 'selected' : '' }}>Student</option>
                                                <option value="employer" {{ $user->role == 'employer' ? 'selected' : '' }}>Employer</option>
                                            </select>
                                            <p class="text-danger" id="roleError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="status" class="mb-2">Account Status*</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="pending" {{ $user->status === 'pending' ? 'selected' : '' }} class="text-warning">Pending Approval</option>
                                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }} class="text-success">Active</option>
                                                <option value="blocked" {{ $user->status === 'blocked' ? 'selected' : '' }} class="text-danger">Blocked</option>
                                            </select>
                                            <p class="text-danger" id="statusError"></p>
                                        </div>
                                    </div>
                                    <div class="card-footer  p-4">
                                        <button type="submit" class="btn btn-primary">Update</button>
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
    <script>
        $('#userForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.users.update', $user->id) }}",
                type: "PUT",
                dataType: "json",
                data: $("#userForm").serializeArray(),

                success: function(response) {
                    $("#nameError").text('');
                    $("#emailError").text('');
                    $("#designationError").text('');
                    $("#company_nameError").text('');
                    $("#mobileError").text('');
                    $("#roleError").text('');
                    $("#statusError").text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('admin.users.employers') }}";
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
                        if (errors.company_name) {
                            $("#company_nameError").text(errors.company_name[0]);
                        }
                        if (errors.mobile) {
                            $("#mobileError").text(errors.mobile[0]);
                        }
                        if (errors.role) {
                            $("#roleError").text(errors.role[0]);
                        }
                        if (errors.status) {
                            $("#statusError").text(errors.status[0]);
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
