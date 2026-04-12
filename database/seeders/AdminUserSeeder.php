<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'info@madaia33.eus'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $roleId = Role::query()
            ->where('name', Role::SUPER_ADMIN)
            ->value('id');

        if ($roleId !== null && $user->roles()->whereKey($roleId)->doesntExist()) {
            $user->roles()->attach($roleId);
        }
    }
}
