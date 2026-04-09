<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CreateOwnerAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Owner
    {
        $password = Str::password(16);

        $user = User::create([
            'name' => $data['coprop1_dni'],
            'email' => $data['coprop1_email'],
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $owner = Owner::create([
            'user_id' => $user->id,
            'coprop1_name' => $data['coprop1_name'],
            'coprop1_dni' => $data['coprop1_dni'],
            'coprop1_phone' => $data['coprop1_phone'] ?? null,
            'coprop1_email' => $data['coprop1_email'],
            'coprop2_name' => $data['coprop2_name'] ?? null,
            'coprop2_dni' => $data['coprop2_dni'] ?? null,
            'coprop2_phone' => $data['coprop2_phone'] ?? null,
            'coprop2_email' => $data['coprop2_email'] ?? null,
        ]);

        $user->sendPasswordResetNotification(
            Password::createToken($user)
        );

        return $owner;
    }
}
