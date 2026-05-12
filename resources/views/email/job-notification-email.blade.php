<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Notification Email</title>
</head>
<body>
    <h1>Job Notification Email</h1>
    <p>Hello, {{ $mailData['employer']->name }}!</p>
    <p>You have received a new job application for the position of {{ $mailData['job']->title }}.</p>

    <p>Applicant Detail:</p>
    <p>Name: {{ $mailData['user']->name }}</p>
    <p>Email: {{ $mailData['user']->email }}</p>
    <p>Phone: {{ $mailData['user']->mobile }}</p>
    <!-- <p>Application Date: {{ $mailData['application_date'] }}</p> -->
</body>
</html>