@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Feedback</li>
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
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="fs-4 mb-1">Feedback</h3>
                                    <p class="text-muted mb-0">Feedback submitted by employers about placed students, and by
                                        students about the companies they were placed with. Visible only to admins and super
                                        admins.</p>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                                <a href="{{ route('admin.feedback.export', array_merge(request()->only(['search', 'type']), ['sort' => $sort, 'direction' => $direction, 'format' => 'pdf'])) }}"
                                    class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Download PDF</a>
                                <a href="{{ route('admin.feedback.export', array_merge(request()->only(['search', 'type']), ['sort' => $sort, 'direction' => $direction, 'format' => 'excel'])) }}"
                                    class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Excel</a>
                            </div>
                            <form method="GET" action="{{ route('admin.feedback') }}" class="row g-2 my-3">
                                <input type="hidden" name="sort" value="{{ $sort }}">
                                <input type="hidden" name="direction" value="{{ $direction }}">
                                <div class="col-md-6 col-lg-4">
                                    <input type="text" name="search" value="{{ request()->query('search') }}"
                                        class="form-control" placeholder="Search name, job title, company...">
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <select name="type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="employer_to_student" @selected($typeFilter === 'employer_to_student')>Employer &rarr; Student</option>
                                        <option value="student_to_employer" @selected($typeFilter === 'student_to_employer')>Student &rarr; Company</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                                        Search</button>
                                    <a href="{{ route('admin.feedback') }}" class="btn btn-secondary ms-1"><i
                                            class="fa fa-times"></i> Clear</a>
                                </div>
                            </form>
                            @php
                                $sortUrl = function ($column) use ($sort, $direction) {
                                    return route('admin.feedback', array_merge(request()->except('page'), [
                                        'sort' => $column,
                                        'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
                                    ]));
                                };
                                $sortIcon = fn ($column) => $sort === $column
                                    ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down')
                                    : 'fa-sort';
                                $headings = [
                                    'feedback_type' => 'Type',
                                    'given_by' => 'From',
                                    'given_to' => 'About',
                                    'rating' => 'Rating',
                                    'comments' => 'Comments',
                                    'created_at' => 'Submitted',
                                ];
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-hover border-0 align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col" aria-sort="{{ in_array($sort, ['title', 'company_name'], true) ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                                <a href="{{ $sortUrl('title') }}" class="text-decoration-none text-dark" aria-label="Sort by job">
                                                    Job <i class="fa {{ $sortIcon('title') }} ms-1" aria-hidden="true"></i></a>
                                                /
                                                <a href="{{ $sortUrl('company_name') }}" class="text-decoration-none text-dark" aria-label="Sort by company">
                                                    Company <i class="fa {{ $sortIcon('company_name') }} ms-1" aria-hidden="true"></i></a>
                                            </th>
                                            @foreach ($headings as $column => $label)
                                                <th scope="col" aria-sort="{{ $sort === $column ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                                    <a href="{{ $sortUrl($column) }}" class="text-decoration-none text-dark" aria-label="Sort by {{ strtolower($label) }}">
                                                        {{ $label }} <i class="fa {{ $sortIcon($column) }} ms-1" aria-hidden="true"></i>
                                                    </a>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        @forelse ($feedbacks as $feedback)
                                            <tr>
                                                <td>
                                                    <div>{{ $feedback->jobApplication?->job?->title ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $feedback->jobApplication?->job?->company_name ?? 'N/A' }}</small>
                                                </td>
                                                <td>
                                                    @if ($feedback->feedback_type === 'employer_to_student')
                                                        <span class="badge bg-primary-subtle text-primary">Employer &rarr; Student</span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success">Student &rarr; Company</span>
                                                    @endif
                                                </td>
                                                <td>{{ $feedback->givenBy?->name ?? 'Unknown' }}</td>
                                                <td>{{ $feedback->givenTo?->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($feedback->rating)
                                                        <span class="text-warning">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="fa {{ $i <= $feedback->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                            @endfor
                                                        </span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td style="max-width: 280px; white-space: normal;">{{ $feedback->comments ?: '—' }}</td>
                                                <td>{{ $feedback->created_at?->copy()->timezone(config('reporting.timezone'))->format('M d, Y g:i A e') ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No feedback has been submitted yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $feedbacks->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
