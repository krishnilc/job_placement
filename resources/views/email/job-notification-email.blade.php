<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New job application</title>
</head>
<body>
    <h1>New job application</h1>
    <p>Hello {{ $employer->name }},</p>
    <p>You have received a new application for {{ $job->title }} at {{ $job->company_name }}.</p>
    <p><strong>Applicant:</strong> {{ $applicant->name }}</p>
    <p><strong>Email:</strong> {{ $applicant->email }}</p>
    <p><strong>Phone:</strong> {{ $applicant->mobile }}</p>
    <p><a href="{{ route('admin.jobApplications', ['search' => $job->title]) }}">Review applications and submitted documents</a> after logging in.</p>
    <p>Reply to this email to contact the applicant directly.</p>
    <p>FNU Job Placement Team</p>
</body>
</html>