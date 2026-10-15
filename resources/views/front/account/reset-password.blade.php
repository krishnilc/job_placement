@extends('front.layouts.app')

@section('main')
    <section class="section-5">
        <div class="container my-5">
            <div class="row d-flex justify-content-center">
                <div class="col-md-5">
                    <div class="card shadow border-0 p-5">
                        <h1 class="h3">Reset Password</h1>
                        <form action="{{ route('password.update') }}" method="post">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            @error('token')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                            <div class="mb-3">
                                <label for="email" class="mb-2">Email<span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" required autocomplete="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $email) }}">
                                @error('email')
                                    <p class="invalid-feedback">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="mb-2">New Password<span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" required minlength="8"
                                    autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
                                <p class="small">Use at least 8 characters.</p>
                                @error('password')
                                    <p class="invalid-feedback">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="mb-2">Confirm Password<span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    required minlength="8" autocomplete="new-password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Reset password</button>
                            <a href="{{ route('account.forgotPassword') }}" class="d-block mt-3">Request a new reset link</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
