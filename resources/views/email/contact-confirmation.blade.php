<p>Hello {{ $senderName }},</p>
<p>Thank you for contacting the FNU Job Placement team. Your message has been sent to the Placement Officer.</p>
<p><strong>Subject:</strong> {{ $contactSubject }}</p>
<p>The Placement Officer will get back to you as soon as possible. You do not need to submit your message again.</p>
<p>If you need to follow up, reply to this email or contact <a href="mailto:{{ config('mail.contact.address') }}">{{ config('mail.contact.address') }}</a>.</p>
<p>Kind regards,<br>FNU Job Placement Team</p>
