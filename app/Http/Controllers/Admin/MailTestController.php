<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MailTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailTestController extends Controller
{
    public function index(): View
    {
        return view('admin.email-test', [
            'mailer' => config('mail.default'),
            'fromAddress' => config('mail.from.address'),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
        ]);

        $redirect = redirect()->route('admin.emailTest')->withInput($data);
        $mailer = config('mail.default');
        $transport = config("mail.mailers.{$mailer}.transport");

        if (in_array($transport, ['log', 'array'], true)) {
            return $redirect->with('error', 'The configured mailer only stores messages locally and does not deliver email. Configure a delivery mailer such as SMTP before testing.');
        }

        try {
            $sent = Mail::to($data['email'])->send(new MailTest(now()->toIso8601String()));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return $redirect->with('error', 'The test email could not be sent. Check the mail server settings and application logs, then try again.');
        }

        if ($sent === null) {
            return $redirect->with('error', 'The test email was not sent. Check the application logs and mail configuration.');
        }

        if (in_array($transport, ['failover', 'roundrobin'], true)) {
            return $redirect->with('mail_test_warning', "The test was processed by the {$mailer} mailer for {$data['email']}. Multiple transports are configured and a local-only fallback may have been used. This does not confirm delivery. Check the recipient's inbox and spam folder to confirm receipt.");
        }

        return $redirect->with('success', "Test email submitted to {$data['email']} using the {$mailer} mailer. Check the recipient's inbox and spam folder to confirm delivery.");
    }
}
