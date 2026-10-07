<p>Hello {{ $user->name }},</p>
<p>An administrator account has been created for you on the FNU Job Placement platform.</p>
<p><strong>Name:</strong> {{ $user->name }}</p>
<p><strong>Email:</strong> {{ $user->email }}</p>
<p><strong>Mobile:</strong> {{ $user->mobile }}</p>
<p><strong>Role:</strong> {{ $user->role === 'super_admin' ? 'Super Admin' : 'Admin' }}</p>
<p><a href="{{ route('account.login') }}">Sign in to your account</a> with your email address and the password provided to you by the Super Admin.</p>
<p>For your security, your password is not included in this email. Contact the Super Admin if you need help changing it.</p>
<p>Kind regards,<br>FNU Job Placement Team</p>
