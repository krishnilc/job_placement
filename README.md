<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Organizations and employer contacts

Organizations are shared records, separate from employer contact accounts.
One organization can have several contacts, each with their own login, personal
details, approval status, and existing job/application permissions.

During employer registration, search for an organization and select an existing
record. If it is missing, use **Can't find your organization? Request a new
organization.** Enter its name and address. Registration creates a pending
contact and an organization request, not an automatically approved organization.

Admins and super admins use **Manage Organizations** to search existing records
and review pending requests. They can approve a new organization, link a request
to an existing organization, or reject it with a reason. The reviewer, review
time, decision, and notes are recorded. Organization approval does not activate
the contact: approve the contact separately in **Manage Employers**. Contacts
without a linked organization or with an unresolved request cannot be activated.
Admins can also add organizations directly and select them when creating contacts.

Only admins and super admins edit shared organization names, addresses, website,
description, and social pages. Contacts can update their own personal details
but cannot edit shared details or change their organization themselves.

The organization migration groups existing employer company names after
trimming, collapsing whitespace, and ignoring case. It keeps the first
non-empty shared value in contact-ID order, fills missing fields from later
contacts, and retains original employer-profile company values for auditing
conflicts and rollback. Existing job company names remain historical snapshots;
jobs are linked to organizations without changing their contact ownership.
Existing employers without a company name require an administrator to link an
organization before any new activation.

Apply the new schema with `php artisan migrate`. Review conflicting legacy
company details before deployment and back up the database first.

## Dashboard job type reporting

All eight dashboard report tabs have Blade partials in
`resources/views/admin/reports/`: `overview`, `applications`, `placement`,
`job-types`, `categories`, `rejection`, `employers`, and `metrics`.
`resources/views/admin/dashboard.blade.php` owns the shared layout, tab navigation,
and tab-persistence script and includes each report partial.

Dashboard tabs use a wrapping grid: four per row on medium and larger screens,
and two per row on small screens, without a horizontal tab scrollbar.
The report navigation uses compact icon labels, a navy active state, and visible
keyboard-focus outlines.
Overview metrics are grouped into Jobs, Employers, Students, and Applications
panels with compact metric cards; System Information remains a separate table.
The selected tab persists on refresh. An explicit tab hash takes precedence over
the query-string tab and filter-based defaults.

Administrators can open **Dashboard > Job Types** for jobs, applications, placed
and rejected applications, and placement rates in **Summary by Job Type**.
The summary supports PDF and Excel-compatible CSV downloads.
The only filter dropdown is **Job category's College/Center**. Clicking **Apply**
filters the summary and its downloads by college. **Reset** restores the
all-types summary, including historical inactive types.
The summary heading names the selected college/center (or **All colleges/centers**).
Filtered PDF reports also name the college in their title, and filtered CSV/PDF
tables include a **College/Center** column.

The college is the **job category's college**, not the student's college. The
college filter applies to the summary and its exports. These reports use their
own filters, independently of Placement tab filters. All job statuses and active
or inactive job types/colleges are included, with zero counts where applicable.
Jobs without an assigned category college are included when all colleges/centers
are selected.
Placements and rejections use current application statuses; placement rate is
placed applications divided by total applications (not distinct students).
All dashboard percentages and their PDF/CSV report exports display two decimal
places, including zero and whole-number percentages (for example, `0.00%` and
`100.00%`).

## Feedback list sorting

In **Dashboard > Feedback**, click a table heading to sort all matching feedback,
then click again to reverse the direction. Job and Company sort independently.
Search and type filters are retained when sorting; pagination and filter
submissions retain the selected order. The default is newest submissions first.
**Download PDF** and **Download Excel** export all matching feedback across all
pages in the selected sort order. Excel downloads use UTF-8, Excel-compatible CSV
with formula-like text treated as plain text. Downloads are restricted to admins
and super admins, just like the feedback list.

## Report timestamps

PDF download/generated timestamps and feedback submission timestamps in the
list and PDF/CSV exports use `Pacific/Fiji`, with the timezone shown next to the
time. Set `REPORT_TIMEZONE` to another IANA timezone if needed. Stored timestamps
and the application's UTC timezone are unchanged. After changing configuration
on a deployment with cached configuration, rebuild it with `php artisan config:cache`.

## Contact form email

The public Contact Us form emails the Placement Officer and then sends a
confirmation email to the address entered in the form. The officer's email
includes the sender's name, email, subject, and message, with Reply-To set to the
sender. Confirmation replies go to the Placement Officer.

Set `PLACEMENT_OFFICER_EMAIL` to override the default recipient
`krishnil.chand@fnu.ac.fj`; the contact page displays the same configured address.
Configure a working delivery mailer (for example, `MAIL_MAILER=smtp` with
`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, and
`MAIL_FROM_ADDRESS`). The `log` and `array` mailers do not deliver real emails.
Rebuild cached configuration with `php artisan config:cache` after deployment
configuration changes.

The page shows a success message after both emails are sent. If sending to the
officer fails, it preserves form input and displays an error. If only the
confirmation fails, it warns that the message was sent and must not be
resubmitted. Delivery failures are reported through Laravel's exception handler.
Emails are sent synchronously; no queue worker is required.

Run the contact flow tests with `php artisan test --filter=ContactFormTest`.

## Registration email verification

New public student, alumni, and employer registrations receive Laravel's signed
email verification link and are directed to a verification/resend page.
Links expire after 60 minutes and can be used without logging in, including
while administrator approval is pending. Verification confirms email ownership
only; it does not activate an account or bypass approval or blocked status.
New accounts must be both verified and approved before login.

Run `php artisan migrate` to add `email_verification_required`. It defaults to
false, preserving access for existing and administrator-created accounts without
pretending that their emails have been verified. Public registration sets it to
true. Changing the primary email of an account requiring verification clears its
verification timestamp; it must verify the new address before continuing.
Signed links for the old address no longer work.

The login page links to the public resend page. Resending and verification are
rate-limited to six requests per minute per IP. Resend responses do not disclose
whether an account exists or has already been verified. Mail transport failures
are reported and displayed explicitly; an initial delivery failure keeps the
created account and directs the user to resend rather than register again.

Configure a working mailer as described above; `MAIL_MAILER=log` does not deliver
verification emails. Set `APP_URL` to the public HTTPS application URL for links
generated outside web requests, and rebuild cached configuration when changing
deployment settings. Verification mail is sent synchronously.

Run verification tests with `php artisan test --filter=EmailVerificationTest`.

## Student and employer account status notifications

Changing a student's or employer's account status from their admin list or the
admin profile editor sends an email to their primary email address. This includes
alumni stored as students. Emails describe the previous and new statuses and
the next steps for Active, Pending, or Blocked accounts. An Active account that
still needs email verification is directed to verify before logging in.
Replies go to the configured Placement Officer.

Saving an unchanged status or creating a student does not send a status-change
email; the same applies to employers. Employer activation still requires a linked
organization and a resolved organization request. Rejected updates send no email.
Notifications are synchronous and use the configured mailer; a working
delivery mailer is required (`log` does not deliver emails).
If mail delivery fails, the status change remains saved, the exception is
reported, and the admin sees a warning to contact the student directly.

Run status-notification tests with
`php artisan test --filter=StudentStatusNotificationTest`.
Run employer notification tests with
`php artisan test --filter=EmployerStatusNotificationTest`.

## Application status notifications

When an admin, super admin, or an employer managing their own job changes an
application status, the applicant receives an email at their primary email
address. The email names the job, company, previous and new status, provides
status-specific next steps, and links to My Applications. Replies go to the
Placement Officer. Interview notices do not invent scheduling details.

Unchanged statuses and rejected/unauthorized updates send no email. Status and
history are saved together before sending the notification. If delivery fails,
both remain saved and the administrator/employer sees an explicit warning to
contact the applicant directly. Notifications are synchronous and require a
working delivery mailer; the `log` mailer does not deliver actual emails.

Run application notification tests with
`php artisan test --filter=ApplicationStatusNotificationTest`.

## Application submission emails

After an application and its initial status history are saved, the person who
posted the job (`jobs.user_id`) receives a new-application email with the job,
company, applicant name, email, phone, and a link to review applications and
documents after login. Reply-To is the student's primary email. A separate
confirmation is sent to the student's primary email, with a My Applications
link and Placement Officer Reply-To. Documents are not attached to either email.

Both deliveries are attempted independently. A mail transport failure is
reported, but does not undo the application or stop the other email from being
attempted. The application response remains successful, marks failed deliveries,
and displays a warning that the application is saved and must not be resubmitted.
Invalid, duplicate, or unauthorized submissions send no email.
Emails are synchronous and require a working delivery mailer; `log` only logs.

Run submission tests with
`php artisan test --filter=ApplicationSubmissionNotificationTest`.

## Feedback submission email

Feedback submitted by a student about their placement company or by an employer
about a placed student sends an email to `mail.contact.address` (configured by
`PLACEMENT_OFFICER_EMAIL`). The notification includes the feedback type, job,
company, author name/email, person the feedback is about, rating, comments, and
an admin-only feedback review link. Reply-To is the feedback author's email.
It is not sent to the other party; existing feedback visibility rules remain.

Mail is sent only after valid, authorized feedback has been saved. Duplicate,
invalid, and pre-placement submissions send no email. If delivery fails, feedback
remains saved, the exception is reported, and the submitter sees a message not to
submit again. Delivery is synchronous and requires a working mailer; `log` does
not deliver actual email.

Run feedback notification tests with
`php artisan test --filter=FeedbackNotificationTest`.

## Employer job-posting notification

When an employer submits a new job, the job is saved as pending administrator
approval, then an email is sent to `mail.contact.address` (configured by
`PLACEMENT_OFFICER_EMAIL`). It includes the job details, employer contact, and
an admin job-review link. Reply-To is the employer's email. Admin-created jobs
do not send this notification.

If delivery fails, the job remains saved and pending approval, the exception is
reported, and the employer sees a warning to contact the Placement Officer.
Notifications are synchronous and require a working delivery mailer; `log` only
logs emails.

Run job-posting notification tests with
`php artisan test --filter=EmployerJobPostedNotificationTest`.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
