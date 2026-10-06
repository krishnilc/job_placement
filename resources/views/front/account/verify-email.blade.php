@extends('front.layouts.app')

@section('main')
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h1 class="h3 mb-3">Verify your email address</h1>
                            @include('front.message')
                            <p>Check your inbox and spam folder, then click the verification link to confirm your email address. The link expires after {{ config('auth.verification.expire', 60) }} minutes.</p>
                            <p>Email verification and administrator approval are both required before a newly registered account can log in. You do not need to log in to verify your email.</p>
                            <form action="{{ route('verification.send') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Registration email address</label>
                                    <input type="email" id="email" name="email" required maxlength="255"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', session('verification_email')) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Resend verification email</button>
                            </form>
                            <p class="mt-3 mb-0"><a href="{{ route('account.login') }}">Back to login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
