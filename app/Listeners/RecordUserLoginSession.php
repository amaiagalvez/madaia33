<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\UserLoginSession;
use Illuminate\Auth\Events\Login;

class RecordUserLoginSession
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        UserLoginSession::query()->create([
            'user_id' => $event->user->id,
            'impersonator_user_id' => session('impersonator_user_id'),
            'session_id' => request()->hasSession() ? request()->session()->getId() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'logged_in_at' => now(),
            'logged_out_at' => null,
        ]);
    }
}
