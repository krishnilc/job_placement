@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ $listUrl }}">{{ $listLabel }}</a></li>
                    <li class="breadcrumb-item active">Status History</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col-lg-3">
                    @include('admin.sidebar')
                </div>
                <div class="col-lg-9">
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body">
                            <h3 class="fs-4 mb-1">Status History</h3>
                            <p class="text-muted">{{ $subjectName }}</p>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Previous status</th>
                                            <th>New status</th>
                                            <th>Changed by</th>
                                            <th>Changed at</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($histories as $history)
                                            <tr>
                                                <td>{{ match ($history->event) {
                                                    'created' => 'Created',
                                                    'baseline' => 'Existing status (migration)',
                                                    default => 'Status changed',
                                                } }}</td>
                                                <td>{{ $history->statusLabel($history->previous_status) }}</td>
                                                <td>{{ $history->statusLabel($history->current_status) }}</td>
                                                <td>{{ $history->changed_by_name ?? 'System / unauthenticated' }}</td>
                                                <td>{{ $history->created_at->copy()->timezone(config('reporting.timezone'))->format('M d, Y g:i A e') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-muted text-center">No status history available.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $histories->links() }}
                            <a href="{{ $listUrl }}" class="btn btn-secondary">Back to {{ $listLabel }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
