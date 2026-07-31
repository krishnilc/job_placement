@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        @php
                            $listType = $list_type ?? request()->query('list_type', 'all');
                            $backRoute = route('admin.users');
                            $backLabel = 'Users';
                            if ($listType === 'students') {
                                $backRoute = route('admin.users.students');
                                $backLabel = 'Students';
                            } elseif ($listType === 'employers') {
                                $backRoute = route('admin.users.employers');
                                $backLabel = 'Employees';
                            }
                        @endphp
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ $backRoute }}">{{ $backLabel }}</a></li>
                            <li class="breadcrumb-item active">Edit User</li>
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
                                        <h3 class="fs-4 mb-1">User/Edit</h3>
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
                                            <label for="mobile" class="mb-2">Mobile*</label>
                                            <input type="text" name="mobile" id="mobile" class="form-control"
                                                value="{{ $user->mobile }}">
                                            <p class="text-danger" id="mobileError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="designation" class="mb-2">Designation*</label>
                                            <input type="text" name="designation" id="designation" class="form-control"
                                                value="{{ $user->designation }}">
                                            <p class="text-danger" id="designationError"></p>
                                        </div>

                                        <div class="mb-4">
                                            <label for="role" class="mb-2">Role*</label>
                                            <select name="role" id="role" class="form-control">
                                                <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                                                <option value="employer" {{ $user->role == 'employer' ? 'selected' : '' }}>Employer</option>
                                            </select>
                                            <p class="text-danger" id="roleError"></p>
                                        </div>

                                        <input type="hidden" name="list_type" value="{{ $list_type ?? request()->query('list_type', 'all') }}">
                                    </div>
                                    <div class="card-footer  p-4">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>

                            <div>
                                {{-- {{ $users->links() }} --}}
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
            var listType = "{{ $list_type ?? request()->query('list_type', 'all') }}";
            var redirectRoute = "{{ route('admin.users') }}";
            if (listType === 'students') {
                redirectRoute = "{{ route('admin.users.students') }}";
            } else if (listType === 'employers') {
                redirectRoute = "{{ route('admin.users.employers') }}";
            }

            $.ajax({
                url: "{{ route('admin.users.update', $user->id) }}",
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
                    $("#designationError").text('');
                    $("#roleError").text('');

                    if (response.status == true) {
                        window.location.href = redirectRoute;
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
                        if (errors.role) {
                            $("#roleError").text(errors.role[0]);
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
