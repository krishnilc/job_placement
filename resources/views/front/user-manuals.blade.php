@extends('front.layouts.app')

@section('main')
    <section class="section-5 user-manuals">
        <div class="container my-5">
            <h2 class="mb-4">User Manuals</h2>
            <p>Start with the quick-start guide for registration, login, and navigation.</p>
            @auth
                <p>Your role guide below explains the tasks available to your account.</p>
            @else
                <div class="manual-login-notice mb-4">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <span><a href="{{ route('account.login') }}">Sign in</a> to see your role-specific guide.
                        The quick-start guide below is available to everyone.</span>
                </div>
            @endauth
            <div class="row g-4 justify-content-center">
                @foreach ($manuals as $key => $manual)
                    <div @class(['col-lg-6' => count($manuals) > 1, 'col-lg-10 col-xl-8' => count($manuals) === 1])>
                        <article class="card border-0 shadow h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <h2 class="h4">{{ $manual['title'] }}</h2>
                                <p>{{ $manual['audience'] }}</p>
                                <h3 class="h6 manual-topics-heading">What this guide covers</h3>
                                <ul class="manual-topics mb-4">
                                    @foreach (array_keys($manual['sections']) as $heading)
                                        <li>{{ $heading }}</li>
                                    @endforeach
                                </ul>
                                <div class="manual-actions d-flex flex-wrap gap-2 mt-auto pt-3">
                                    <a class="btn btn-primary" target="_blank" rel="noopener"
                                        href="{{ route('userManual.file', ['manual' => $key, 'format' => 'pdf']) }}"
                                        aria-label="View {{ $manual['title'] }} PDF (opens in a new tab)">View PDF</a>
                                    <a class="btn btn-outline-primary"
                                        href="{{ route('userManual.file', ['manual' => $key, 'format' => 'pdf', 'download' => 1]) }}"
                                        aria-label="Download {{ $manual['title'] }} PDF">Download PDF</a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
