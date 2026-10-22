@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Email Test</li>
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
                    @if (session('mail_test_warning'))
                        <div class="alert alert-warning" role="alert">{{ session('mail_test_warning') }}</div>
                    @endif
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body p-4">
                            <h3 class="fs-4 mb-3">Email Test</h3>
                            <p>Send a test email to check the site's current email configuration. Confirm receipt in
                                the recipient's inbox or spam folder; a successful send request alone does not guarantee delivery.</p>
                            <dl class="row">
                                <dt class="col-sm-4">Configured mailer</dt>
                                <dd class="col-sm-8">{{ $mailer }}</dd>
                                <dt class="col-sm-4">Sender address</dt>
                                <dd class="col-sm-8">{{ $fromAddress }}</dd>
                            </dl>
                            <form action="{{ route('admin.emailTest.send') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="email" class="mb-2">Recipient email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" required maxlength="254"
                                        value="{{ old('email', Auth::user()->email) }}"
                                        @class(['form-control', 'is-invalid' => $errors->has('email')])
                                        aria-describedby="emailHelp @error('email') emailError @enderror"
                                        @error('email') aria-invalid="true" @enderror>
                                    <div id="emailHelp" class="form-text">Defaults to your account email. You may use another mailbox you can check.</div>
                                    @error('email')
                                        <div id="emailError" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Send Test Email</button>
                                <p class="text-muted small mt-3 mb-0">Sends immediately, without a queue worker. Limited to 3 attempts per minute.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
