<p>Hello {{ $applicant->name }},</p>
<p>Your application for {{ $job->title }} at {{ $job->company_name }} has been successfully submitted.</p>
<p>Your application is awaiting review. You will receive an email when its status changes.</p>
<p><a href="{{ route('account.myJobApplications') }}">View my applications</a> after logging in.</p>
<p>You do not need to submit this application again.</p>
<p>For assistance, reply to this email or contact the Placement Officer at {{ config('mail.contact.address') }}.</p>
<p>Kind regards,<br>FNU Job Placement Team</p>
