<h2>New placement feedback</h2>
<p>Hello Placement Officer,</p>
<p>New feedback has been submitted for a placed application.</p>
<p><strong>Type:</strong> {{ $feedback->feedback_type === \App\Models\Feedback::TYPE_EMPLOYER_TO_STUDENT ? 'Employer about student' : 'Student about company/employer' }}</p>
<p><strong>Job:</strong> {{ $feedback->jobApplication->job->title }}</p>
<p><strong>Company:</strong> {{ $feedback->jobApplication->job->company_name }}</p>
<p><strong>From:</strong> {{ $feedback->givenBy->name }} ({{ $feedback->givenBy->email }})</p>
<p><strong>About:</strong> {{ $feedback->givenTo->name }}</p>
<p><strong>Rating:</strong> {{ $feedback->rating }}/5</p>
<p><strong>Comments:</strong></p>
<div style="white-space: pre-wrap;">{{ $feedback->comments ?? 'No comments provided.' }}</div>
<p><a href="{{ route('admin.feedback', ['search' => $feedback->jobApplication->job->title, 'type' => $feedback->feedback_type]) }}">Review feedback</a> after logging in as an administrator.</p>
<p>This feedback is for administrative review only. It has not been emailed to the person the feedback is about.</p>
<p>Reply to this email to contact the person who submitted the feedback.</p>
<p>FNU Job Placement Team</p>
