<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        }

        $this->verifyOnce($request->user());

        return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
    }

    /**
     * Verify email for unauthenticated users via signed URL.
     */
    public function show(string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Match the SHA-1 email hash used by VerifyEmailNotification and
        // validate it regardless of the current browser's login session.
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, __('messages.Invalid verification link.'));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        }

        $this->verifyOnce($user);

        return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
    }

    private function verifyOnce(User $user): void
    {
        $verified = $user->getConnection()->transaction(function () use ($user) {
            $lockedUser = User::on($user->getConnectionName())->whereKey($user->getKey())
                ->lockForUpdate()->firstOrFail();
            if ($lockedUser->hasVerifiedEmail()) return false;
            if (!$lockedUser->markEmailAsVerified()) return false;
            $user->setRawAttributes($lockedUser->getAttributes(), true);
            return true;
        });

        if ($verified) event(new Verified($user));
    }
}
