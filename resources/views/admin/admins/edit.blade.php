@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.admins') }}">Admins</a></li>
                            <li class="breadcrumb-item active">Edit Admin</li>
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
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="userForm" name="userForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="card-body  p-4">
                                        <h3 class="fs-4 mb-1">Admin/Edit</h3>
                                        <div class="mb-4">
                                            <label for="name" class="mb-2">Name<span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                value="{{ $user->name }}">
                                            <p class="text-danger" id="nameError"></p>
                                        </div>
                                         <div class="mb-4">
                                            <label for="designation" class="mb-2">Designation</label>
                                            <input type="text" name="designation" id="designation" class="form-control"
                                                value="{{ $user->designation }}">
                                            <p class="text-danger" id="designationError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="email" class="mb-2">Email<span class="text-danger">*</span></label>
                                            <input type="text" name="email" id="email" class="form-control"
                                                value="{{ $user->email }}">
                                            <p class="text-danger" id="emailError"></p>
                                        </div>                                       
                                        <div class="mb-4">
                                            <label for="mobile" class="mb-2">Mobile<span class="text-danger">*</span></label>
                                            <input type="text" name="mobile" id="mobile" class="form-control"
                                                value="{{ $user->mobile }}">
                                            <p class="text-danger" id="mobileError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="role" class="mb-2">Role<span class="text-danger">*</span></label>
                                            <select name="role" id="role" class="form-control">
                                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                                <option value="management" {{ $user->role == 'management' ? 'selected' : '' }}>Management (Read-only)</option>
                                            </select>
                                            <p class="text-danger" id="roleError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="status" class="mb-2">Account Status<span class="text-danger">*</span></label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="pending" class="text-warning" {{ $user->status == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                                <option value="active" class="text-success" {{ $user->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="blocked" class="text-danger" {{ $user->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                            </select>
                                            <p class="text-danger" id="statusError"></p>
                                        </div>
                                    </div>
                                    <div class="card-footer  p-4">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('admin.users.admins') }}" class="btn btn-secondary">Cancel</a>
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
            var $form = $(this);
            var $submitButton = $form.find("button[type='submit']");
            var fields = ['name', 'email', 'designation', 'mobile', 'role', 'status'];
            $submitButton.prop('disabled', true);

            function showErrors(errors) {
                fields.forEach(function(field) {
                    $("#" + field + "Error").text(errors[field] ? errors[field][0] : '');
                });
            }
            showErrors({});

            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                dataType: "json",
                headers: { 'Accept': 'application/json' },
                data: $form.serializeArray(),

                success: function(response) {
                    if (response && response.status === true) {
                        window.location.href = "{{ route('admin.users.admins') }}";
                    } else if (response && response.status === false && response.errors) {
                        showErrors(response.errors);
                    } else {
                        alert('The server returned an unexpected response. Reload the admins list to check whether the update was saved before retrying.');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        showErrors(xhr.responseJSON.errors);
                        return;
                    }
                    var message;
                    if (xhr.status === 401 || xhr.status === 419) {
                        message = 'Your session has expired. Reload this page and sign in again before retrying.';
                    } else if (xhr.status === 403) {
                        message = 'The update was denied. Only super admins can edit staff accounts. If you have permission, contact the administrator to check the server security rules.';
                    } else if (xhr.status === 0) {
                        message = 'Unable to reach the server. Check your connection before retrying.';
                    } else {
                        message = 'The server could not confirm the update (HTTP ' + xhr.status + '). Reload the admins list to check whether it was saved before retrying. If the problem continues, contact the administrator.';
                    }
                    alert(message);
                },
                complete: function() {
                    $submitButton.prop('disabled', false);
                }
            });
        });
    </script>
@endsection
