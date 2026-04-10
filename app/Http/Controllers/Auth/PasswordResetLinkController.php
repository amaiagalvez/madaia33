<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $inputEmail = Str::lower(trim((string) $request->string('email')));

        $user = User::query()
            ->where('email', $inputEmail)
            ->first();

        if ($user === null) {
            $user = User::query()
                ->whereHas('roles', function ($roleQuery): void {
                    $roleQuery->where('name', Role::PROPERTY_OWNER);
                })
                ->whereHas('owner', function ($ownerQuery) use ($inputEmail): void {
                    $ownerQuery
                        ->whereRaw('LOWER(coprop1_email) = ?', [$inputEmail])
                        ->orWhereRaw('LOWER(coprop2_email) = ?', [$inputEmail]);
                })
                ->first();
        }

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => [trans(Password::INVALID_USER)],
            ]);
        }

        $status = Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return back()->with('status', trans($status));
    }
}
