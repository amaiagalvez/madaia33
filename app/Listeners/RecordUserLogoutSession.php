<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\UserLoginSession;
use Illuminate\Auth\Events\Logout;

class RecordUserLogoutSession
{
    public function handle(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $sessionQuery = UserLoginSession::query()
            ->where('user_id', $event->user->id)
            ->whereNull('logged_out_at');

        if (request()->hasSession()) {
            $sessionId = request()->session()->getId();

            $session = (clone $sessionQuery)
                ->where('session_id', $sessionId)
                ->latest('logged_in_at')
                ->first();

            if ($session !== null) {
                $session->update([
                    'logged_out_at' => now(),
                ]);

                return;
            }
        }

        $sessionQuery
            ->latest('logged_in_at')
            ->limit(1)
            ->update([
                'logged_out_at' => now(),
            ]);
    }
}
