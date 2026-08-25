@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('employer.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">@include('employer.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <form id="employerPasswordForm" method="POST">
                            @csrf
                            <div class="card-body p-4">
                                <h1 class="h3 mb-4">Change Password</h1>
                                <div class="mb-4">
                                    <label for="old_password" class="mb-2">Old Password*</label>
                                    <input type="password" name="old_password" id="old_password" class="form-control" required>
                                    <p class="text-danger" id="old_passwordError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="new_password" class="mb-2">New Password*</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                                    <p class="text-danger" id="new_passwordError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="confirm_password" class="mb-2">Confirm Password*</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                    <p class="text-danger" id="confirm_passwordError"></p>
                                </div>
                            </div>
                            <div class="card-footer p-4">
                                <button type="submit" class="btn btn-primary">Update Password</button>
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
        $('#employerPasswordForm').submit(function (event) {
            event.preventDefault();
            $.ajax({
                url: "{{ route('account.updatePassword') }}",
                type: 'POST',
                dataType: 'json',
                data: $(this).serializeArray(),
                success: function (response) {
                    if (response.status === true) {
                        window.location.href = "{{ route('employer.account.editProfile') }}";
                        return;
                    }
                    $.each(response.errors || {}, function (field, messages) {
                        $('#' + field + 'Error').text(messages[0]);
                    });
                },
                error: function () {
                    alert('Unable to change your password. Please try again.');
                }
            });
        });
    </script>
@endsection
