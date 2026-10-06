<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return view('front.account.verify-email');
    }

    public function verify(Request $request, string $id, string $hash)
    {
        $user = User::findOrFail($id);
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $message = 'Your email address has been verified.';
        if ($user->status === 'pending') {
            $message .= ' Your account is still pending administrator approval.';
        } elseif ($user->status === 'blocked') {
            $message .= ' Your account is blocked. Please contact the administrator.';
        } else {
            $message .= ' You can now log in.';
        }

        return redirect()->route('account.login')->with('success', $message);
    }

    public function resend(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255']);
        $request->session()->put('verification_email', $data['email']);
        $user = User::where('email', $data['email'])->first();

        if ($user && $user->needsEmailVerification()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (TransportExceptionInterface $exception) {
                report($exception);

                return redirect()->route('verification.notice')
                    ->with('error', 'Unable to send a verification email right now. Please try again later.');
            }
        }

        return redirect()->route('verification.notice')->with('success', 'If this email belongs to an account that needs verification, a new verification link has been sent. Please check your inbox and spam folder.');
    }
}
