<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@madaia33.eus'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );
    }
}
