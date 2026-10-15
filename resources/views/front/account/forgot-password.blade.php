@extends('front.layouts.app')

@section('main')
    <section class="section-5">
        <div class="container my-5">
            <div class="py-lg-2">&nbsp;</div>
            @if (session()->has('success'))
                <div class="alert alert-success">
                    <p class="mb-0 pb-0">{{ Session::get('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger">
                    <p class="mb-0 pb-0">{{ Session::get('error') }}</p>
                </div>
            @endif

            <div class="row d-flex justify-content-center">
                <div class="col-md-5">
                    <div class="card shadow border-0 p-5">
                        <h1 class="h3">Forgot Password</h1>
                        <p>Enter your account email address to receive a password reset link. The link expires after {{ config('auth.passwords.users.expire') }} minutes.</p>
                        <form action="{{ route('password.email') }}" method="post">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="mb-2">Email<span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" required autocomplete="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="example@example.com" value="{{ old('email') }}">

                                @error('email')
                                    <p class="invalid-feedback">{{ $message }}</p>
                                @enderror
                            </div>                            
                            <div class="justify-content-between d-flex">
                                <button type="submit" class="btn btn-primary mt-2">Send reset link</button>
                                <a href="{{ route('account.login') }}" class="mt-3">Back to Login?</a>
                            </div>
                        </form>
                    </div>
                    <div class="mt-4 text-center">
                        <p>Do not have an account? <a href="{{ route('account.registration') }}">Register</a></p>
                    </div>
                </div>
            </div>
            <div class="py-lg-5">&nbsp;</div>
        </div>
    </section>
@endsection