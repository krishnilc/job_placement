<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\EmployerAccountStatusChanged;
use App\Notifications\StudentAccountStatusChanged;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AccountStatusNotifier
{
    public function sendIfChanged(User $user, string $previousStatus): ?bool
    {
        if (! in_array($user->role, ['student', 'employer'], true) || $user->status === $previousStatus) {
            return null;
        }

        try {
            $notification = $user->role === 'employer'
                ? new EmployerAccountStatusChanged($previousStatus, $user->status)
                : new StudentAccountStatusChanged($previousStatus, $user->status);
            $user->notify($notification);
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return false;
        }

        return true;
    }
}
