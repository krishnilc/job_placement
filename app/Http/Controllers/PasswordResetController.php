<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetController extends Controller
{
    public function sendLink(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255']);

        try {
            $status = Password::sendResetLink($data);
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withInput($request->only('email'))
                ->with('error', 'Unable to send a password reset email right now. Please try again later or contact the Placement Officer.');
        }

        if (! in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER, Password::RESET_THROTTLED], true)) {
            return back()->withInput($request->only('email'))->with('error', __($status));
        }

        return redirect()->route('account.forgotPassword')
            ->with('success', 'If this email belongs to an account, a password reset link has been requested. Check your inbox and spam folder. Please wait at least one minute before requesting another link.');
    }

    public function edit(Request $request, string $token)
    {
        return view('front.account.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->saveOrFail();
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'This password reset link is invalid or has expired. Please request a new link.'])
                ->withInput($request->only('email'));
        }

        return redirect()->route('account.login')
            ->with('success', 'Your password has been reset. Please log in with your new password. Existing account approval and email verification requirements still apply.');
    }
}
