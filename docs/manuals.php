<?php

$account = [
    'Account settings and security' => [
        '1. Open your name menu > Account Settings. View Profile shows your saved information; Profile Update opens the editable fields.',
        '2. Update your own details, select the save/update button, and check the confirmation. Correct any errors shown beside fields.',
        '3. Use Change Profile Picture to upload a clear picture. Use Change Password to enter your current password, new password, and confirmation.',
        'If your account requires email verification, changing your primary email requires verification of the new address before continuing.',
        'Use a unique password, keep applicant documents confidential, and select your name > Logout on shared devices. Never share a login.',
    ],
    'Troubleshooting and support' => [
        'Pending approval: contact the Placement Officer if approval is delayed. Email verification and administrator approval are separate requirements.',
        'Blocked account: contact an administrator; registering again is not a solution. Unverified email: use the verification/resend link on Login and check your spam folder.',
        'Forgotten password: select Forgot Password on Login, enter your account email, and select Send reset link. Check your inbox and spam folder. Open the single-use link within 60 minutes and enter a new password of at least 8 characters twice. Wait at least one minute between link requests. Resetting a password does not approve or unblock an account or verify its email. If email recovery is unavailable, contact an administrator for student/employer accounts or a super administrator for staff accounts.',
        'Read submission messages carefully. A notification-email warning may mean the record was saved but the email failed; check the saved record before submitting again.',
        'Use Contact Us to reach the Placement Officer. Include the page, action, time, and error message, but never send your password or unnecessary personal documents.',
    ],
];

$reports = [
    'Dashboard reports and exports' => [
        '1. Open Admin Dashboard. Choose Overview, Applications, Placement, Job Types, Categories, Rejection, Employers, or Metrics to view the relevant report.',
        '2. Where filters are shown, choose the date range, college/center, or other criteria, then select Apply. Use Reset to clear that report\'s filters.',
        '3. Use the report\'s PDF or Excel/CSV download control. Excel-compatible downloads are CSV files, not XLSX workbooks. Open CSV files with Excel or another spreadsheet application.',
        'The Job Types tab filters by the job category\'s College/Center, not the applicant\'s college. Its filters are independent of Placement filters. All colleges/centers includes jobs without an assigned category college.',
        'Placement rates are placed applications divided by total applications, not distinct students. Current application statuses drive placement and rejection counts; percentages display two decimal places.',
        'Check the selected filters and report heading before sharing a download. Reports may include private data. Export timestamps display the configured report timezone (Pacific/Fiji by default).',
    ],
    'Feedback review' => [
        '1. Open Feedback from the administration sidebar. Search and use the type filter to locate student or employer feedback.',
        '2. Click a sortable heading to change order, then click again to reverse it. Job and Company sort separately; pagination preserves your selected order.',
        '3. Download PDF or Download Excel exports all matching feedback across all pages, not only the visible page, in the selected order.',
        'Placement feedback is for administrative review. Student feedback is not shared directly with employers, and employer feedback is not shared directly with students.',
    ],
];

$administration = [
    'Daily administrator workflow' => [
        '1. Login and open Admin Dashboard from your name menu. Review dashboard totals, then use the sidebar to open Students, Employers, Jobs, Applications, or Feedback.',
        '2. Resolve organization requests before activating employer contacts. Review pending student/employer accounts and pending jobs.',
        '3. Review applications and update statuses only after a confirmed recruitment decision. Use reports to monitor progress.',
        'Search, filters, sortable headings, and pagination help locate records. Always verify the person, organization, job, and current status before making a change.',
    ],
    'Students and employer contacts' => [
        '1. Open Students or Employers. Search for the person, open their profile, and review their details.',
        '2. Use the available add/edit controls to create or correct a student or employer record. Use the account-status controls to approve (Active) or block an account.',
        '3. For employers, confirm that the correct organization is linked and any organization request is resolved before activation. Approving an organization alone does not activate its contacts.',
        '4. Use the password reset action when needed and communicate any replacement password privately. Status-change emails may be sent; read any delivery warning.',
        'Administrators cannot manage staff accounts or delete user accounts. Those actions belong to super administrators. Management cannot make these changes.',
    ],
    'Organizations and requests' => [
        '1. Open Organizations under System Setup. Search existing organizations before adding another record.',
        '2. Open a pending request and review the proposed name, address, phone, email, and requesting contact.',
        '3. Approve a new organization, link the request to an existing organization, or reject it with a reason. Check the recorded review decision and notes.',
        '4. Activate the employer contact separately in Employers once its organization link and request are resolved.',
        'Admins and super admins maintain shared organization details. Employer contacts cannot change these themselves. Multiple contacts may belong to one organization but retain separate job ownership.',
        'Only a super administrator can delete organizations. Prefer correcting or linking records over deleting them.',
    ],
    'Jobs and application decisions' => [
        '1. Open Jobs. Search and review job details, employer ownership, category, vacancies, location, and closing date.',
        '2. Review employer submissions and use the job status controls to publish approved jobs or block unsuitable jobs. Only active jobs are public.',
        '3. Use Create/Edit to maintain administrative job postings. Supply the required fields, save, and confirm the resulting status.',
        '4. Open Applications. Search by job, company, applicant name/email, or filter by application status. Open permitted application documents and status history.',
        '5. Select the appropriate application status from the available dropdown, confirm the change, then check the result. The change and reviewer are recorded in history; an email is attempted to the applicant.',
        'An unchanged status sends no new notification. If the email fails, the status/history remain saved; contact the applicant directly instead of repeating the status change.',
        'Only super administrators can delete applications. Keep records unless deletion is genuinely necessary.',
    ],
    'System setup' => [
        '1. Open Job Types, Categories, or Colleges/Centers under System Setup.',
        '2. Search existing entries first. Use the available add/edit controls to maintain labels, college associations, and active/inactive status.',
        '3. Confirm the saved entry appears in the intended lists. Active setup entries are used in posting/search choices; historical reports may still include inactive records.',
        'Avoid deleting setup records without reviewing their existing job and reporting relationships.',
    ],
];

return [
    'general' => [
        'title' => 'Quick Start and Navigation',
        'audience' => 'Everyone, including visitors and new users',
        'sections' => [
            'Start here' => [
                'Use the portal address supplied by FNU in a current web browser. You can browse public vacancies without an account; applying requires an approved, verified student/alumni account.',
                'Student/alumni: Register > verify email > wait for approval > Login > complete profile and picture > Find Jobs > Apply > Jobs Applied.',
                'Employer: Register > select/request organization > verify email > organization resolution and contact approval > Login > Create Job > job approval > Review Applications.',
                'Staff: use your assigned account, Login, and open Admin Dashboard. Staff roles are assigned by a super administrator, not through public registration.',
            ],
            'Navigation and finding help' => [
                'Home returns to the landing page. Find Jobs opens vacancies. Contact Us sends a message to the Placement Officer.',
                'On a small screen, select the navigation toggle to reveal the menu. Login and Register appear when signed out.',
                'After login, open your name menu for your dashboard, Account Settings, or Logout. Sidebars provide the tasks available to your role.',
                'User Manuals opens the help page. Everyone sees this guide; signed-in users also see their role guide. View PDF opens the site copy; Download PDF saves it. Editable Word copies are maintained offline and are not available through the site.',
                'Use search/filter controls and submit the search. Use pagination for more results. Click sortable column headings to change order. An asterisk (*) marks a required field.',
            ],
            'Register as a student or alumni' => [
                '1. Select Register and choose Student or Alumni. Enter your name, primary email, seven-digit mobile number, password, and matching confirmation.',
                '2. Students must provide a unique University Student ID (up to nine characters) and date of birth. Alumni provide date of birth and graduation year; the Student ID is optional.',
                '3. Submit and correct validation errors. Registration passwords require at least five characters; use a longer, unique password.',
                '4. Follow the verification link sent to your email and wait for administrator approval. Alumni use the student dashboard and manual after login.',
            ],
            'Register as an employer contact' => [
                '1. Select Register > Employer. Enter your personal contact details, designation, password, and confirmation.',
                '2. Search for and select your existing organization. If it is missing, choose Can\'t find your organization? Request a new organization.',
                '3. For a new organization request, enter its name, address, phone, and email, then submit.',
                '4. Verify your email. Wait for organization resolution/linking and separate contact-account approval. Each contact uses their own login, even when sharing an organization.',
            ],
            'Verification, approval, and login' => [
                '1. Check your inbox and spam folder for the verification email. Links expire after 60 minutes; use the verification/resend link on Login if necessary.',
                '2. Email verification does not approve the account. Pending means awaiting administrator approval; Active means approved; Blocked means access is suspended.',
                '3. Select Login and enter your primary email and password. The site directs you to the student, employer, or admin dashboard based on your role.',
                '4. Read any error message before retrying. Use Contact Us for delayed approval or blocked access. Select your name > Logout when finished.',
                'If registration says your account was created but email failed, do not register again. Use resend and seek support if delivery still fails.',
            ],
            'Browse vacancies' => [
                '1. Select Find Jobs. Enter keywords/location and use the available category/job-type filters, then submit the search.',
                '2. Open a job to read its description, requirements, location, employer, and closing date.',
                '3. Login as a student/alumni to Save or Apply. Saving is a bookmark, not an application. Check Jobs Applied after submitting.',
            ],
        ] + $account,
    ],
    'student' => [
        'title' => 'Student and Alumni User Manual',
        'audience' => 'Approved students and alumni',
        'sections' => [
            'Your first session' => [
                '1. Complete registration, email verification, and approval as described in Quick Start and Navigation.',
                '2. Login to Student Dashboard. Open your name menu > Account Settings > Profile Update.',
                '3. Fill every required field: personal/contact/address details, college, education, skills, and other required profile information. Alumni should check their designation and graduation year.',
                '4. Save and add a profile picture using Change Profile Picture. Follow the incomplete-profile and missing-picture notices until they are cleared.',
                'Use the sidebar for Jobs Applied and Saved Jobs. Alumni share this workflow; they do not need a separate employer or staff account.',
            ],
            'Find and save jobs' => [
                '1. Open Find Jobs, search/filter vacancies, and open the job details.',
                '2. Read requirements and closing date before applying. Select Save to bookmark an active job.',
                '3. Open Saved Jobs from your student sidebar to revisit a vacancy. Use the remove action to delete an unwanted bookmark.',
                'Saving does not submit an application or reserve a vacancy. Recheck job availability before applying.',
            ],
            'Submit an application' => [
                '1. Open the job details and select Apply. Prepare documents that match the advertised requirements.',
                '2. Upload at least one document: application letter, resume, or certificate. Application letters and resumes accept PDF, DOC, or DOCX. Certificates also accept JPG, JPEG, and PNG.',
                '3. Each file must be no larger than 5 MB. Use readable filenames and review each file before submitting.',
                '4. Submit once, read the confirmation, then open Jobs Applied to verify the application exists. Submission starts at Submitted when that configured status is available.',
                'You cannot submit a second application for the same job while your application exists. An email-delivery warning does not necessarily mean submission failed.',
            ],
            'Track applications and give placement feedback' => [
                '1. Open Jobs Applied to review your applications and current recruitment status. The job\'s active/inactive status is separate from your application status.',
                '2. Review any available history/details and check your email for status-change notifications. Ask the Placement Officer about unclear decisions; students cannot change recruitment status.',
                '3. Use the remove action only if you intend to remove that application; confirm the selected job before proceeding.',
                '4. Once an application is marked Placed, use its action menu > Give Feedback. Choose a rating from 1 (Poor) to 5 (Excellent), optionally add comments (up to 2,000 characters), and submit.',
                'Only one student feedback submission is allowed per placed application. Feedback Submitted indicates it already exists. Your company feedback is visible to administrators, not directly to the employer.',
            ],
        ] + $account,
    ],
    'employer' => [
        'title' => 'Employer User Manual',
        'audience' => 'Approved employer contacts',
        'sections' => [
            'Your organization and first session' => [
                '1. Register, verify your email, and wait for contact approval. A new organization request must be resolved and linked first.',
                '2. Login to Employer Dashboard. Use Create Job, My Jobs, Review Applications, and My Organization in the sidebar.',
                '3. Open My Organization to check the shared company details. Ask an administrator to correct them; you cannot edit shared organization information or switch organizations yourself.',
                'Multiple contacts can belong to one organization. Your jobs and application access remain tied to your own contact login, not all contacts in the organization.',
            ],
            'Create a job' => [
                '1. Select Create Job. Choose the appropriate college/category and job type. Enter title, vacancy count, location, description, and experience.',
                '2. Provide closing date, salary, responsibilities, qualifications, and keywords where applicable. Company details come from your linked organization.',
                '3. Review and submit. New employer jobs are saved as pending for administrator approval; they are not immediately public.',
                '4. Check My Jobs and wait for approval. If the Placement Officer email fails, the job is still saved; contact the officer rather than creating a duplicate.',
            ],
            'Maintain your jobs' => [
                '1. Open My Jobs. Search and use sortable headings/pagination to locate a posting.',
                '2. Use Edit to correct your own job, save, and review the result and status.',
                '3. Use Block to hide an available posting when appropriate. Where Unblock is available, it returns your blocked job to active; check the resulting status.',
                '4. Employers cannot delete jobs. Ask an administrator if a posting must be removed; use Block when it should simply stop being public.',
                'You cannot edit or manage another contact\'s jobs, including other contacts in your organization.',
            ],
            'Review applicants and update decisions' => [
                '1. Open Review Applications. Search by job, company, applicant, or email and filter by status. Only applications to jobs you own are available.',
                '2. Open the applicant profile and permitted application letter, resume, or certificate downloads. Treat documents as confidential.',
                '3. Review the current status and history. Select the appropriate status from the dropdown and confirm the change only after a recruitment decision.',
                '4. The change is saved to history and an applicant email is attempted. An unchanged status sends no new email; a delivery warning leaves the change saved.',
                'You cannot delete applications. Contact the Placement Officer for corrections you cannot perform.',
            ],
            'Give feedback on a placed student' => [
                '1. In Review Applications, locate an application marked Placed for your job.',
                '2. Use Give Feedback where available. Select a rating from 1 (Poor) to 5 (Excellent) and optionally add comments (up to 2,000 characters).',
                '3. Submit once. Feedback Submitted means feedback already exists for that application.',
                'Feedback is visible to administrators and is not shared directly with the student. Student feedback about your company is not directly visible to you.',
            ],
        ] + $account,
    ],
    'admin' => [
        'title' => 'Administrator User Manual',
        'audience' => 'Administrators',
        'sections' => $administration + $reports + $account,
    ],
    'management' => [
        'title' => 'Management Read-only User Manual',
        'audience' => 'Management accounts with read-only access',
        'sections' => [
            'Start and understand your permissions' => [
                '1. Login with your assigned active management account. Open Admin Dashboard from your name menu.',
                '2. Use the report tabs and sidebar to view students, employers, jobs, applications, feedback, organizations/requests, job types, categories, and colleges/centers.',
                'Management access is read-only. You cannot create, edit, delete, approve, reject, change recruitment statuses, apply/save jobs, or submit feedback.',
                'Staff-account management is not available. Add/edit/status controls are hidden, and direct write requests are denied. This is expected behavior, not an error.',
                'You may update only your own contact details, picture, and password through Account Settings. Pending or blocked management accounts cannot access the portal.',
            ],
            'Inspect records and documents' => [
                '1. Open a list, enter search criteria, and use available filters, sortable headings, and pagination.',
                '2. Open student/employer profiles or organization details to read the stored information. Organization requests can be reviewed but not resolved.',
                '3. Open Applications to view decisions and status history. Download permitted application documents only for an authorized business purpose.',
                '4. Record any correction needed and send it to an administrator through an approved channel. Do not try to bypass read-only controls.',
                'Reports and document downloads can contain private records. Store them securely and share only with authorized recipients.',
            ],
        ] + $reports + $account,
    ],
    'super_admin' => [
        'title' => 'Super Administrator User Manual',
        'audience' => 'Super administrators',
        'sections' => [
            'Elevated access and responsibilities' => [
                'Super administrators can perform the administrator workflows included below and manage staff accounts and privileged deletions.',
                'Use your own assigned login. Confirm the target record, role, and effect before any elevated action; keep a suitable backup and follow institutional approval procedures.',
            ],
            'Create and maintain staff accounts' => [
                '1. Open Administrators in the admin sidebar and select Add Admin.',
                '2. Enter the required staff details and choose the appropriate role. Choose Management (Read-only) for reporting-only users, Administrator for operational users, or Super Administrator only when elevated access is needed.',
                '3. Submit and check the creation confirmation/email result. Convey credentials only through an approved private channel.',
                '4. Open the staff profile/edit action to maintain details or change role. Review permissions with the person before assigning elevated access.',
                '5. Use the staff password reset action when necessary. Public registration never creates staff accounts.',
            ],
            'Privileged deletion checklist' => [
                '1. Locate the user, application, or organization using search. Open details and confirm identity, ownership, and related records.',
                '2. Prefer blocking an account/job or correcting a record when deletion would discard useful history.',
                '3. Follow institutional retention rules and backup requirements before deleting. Confirm the action only for the intended record.',
                '4. Check the result; if deletion is refused because the record is related to other data, read the error and resolve it through the normal workflow.',
                'User-account deletion, application deletion, and organization deletion are super-admin-only actions. Do not assume a deleted record can be restored through the portal.',
            ],
            'Check email delivery' => [
                '1. Open System Setup > Email Test in the admin sidebar. This option is available only to super administrators.',
                '2. Review the configured mailer and sender address. Keep your account email as the recipient or enter another mailbox you can check.',
                '3. Select Send Test Email. The message is sent immediately, without a queue worker. You can make up to three attempts per minute.',
                '4. Read the result, then check the recipient inbox and spam folder for the FNU Job Placement - Test Email message. Its timestamp identifies the test.',
                'A successful send request does not guarantee delivery. Log/array mailers do not deliver email, and multi-transport mailers may use a local-only fallback. Confirm receipt before concluding that email works.',
                'If sending fails, ask the system maintainer to review the mail server configuration and application logs. Mail server credentials are not shown on this page.',
            ],
        ] + $administration + $reports + $account,
    ],
];
