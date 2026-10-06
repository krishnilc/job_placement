<h2>New employer job awaiting approval</h2>
<p>Hello Placement Officer,</p>
<p>An employer has submitted a job posting for administrator review. It is not yet publicly visible.</p>
<p><strong>Job title:</strong> {{ $job->title }}</p>
<p><strong>Company:</strong> {{ $job->company_name }}</p>
<p><strong>Location:</strong> {{ $job->location }}</p>
<p><strong>Vacancies:</strong> {{ $job->vacancy }}</p>
<p><strong>Closing date:</strong> {{ $job->closing_date ? \Illuminate\Support\Carbon::parse($job->closing_date)->format('d M Y') : 'Not specified' }}</p>
<p><strong>Posted by:</strong> {{ $job->user->name }} ({{ $job->user->email }})</p>
<p><strong>Job description:</strong></p>
<div style="white-space: pre-wrap;">{{ $job->description }}</div>
<p><a href="{{ route('admin.jobs', ['search' => $job->title]) }}">Review job postings</a> after logging in as an administrator.</p>
<p>Reply to this email to contact the employer directly.</p>
<p>FNU Job Placement Team</p>
