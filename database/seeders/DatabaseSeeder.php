<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
            CampaignTemplateSeeder::class,
            LocationSeeder::class,
            PropertySeeder::class,
        ]);

        if (app()->isLocal()) {
            $this->call(DevSeeder::class);
        }
    }
}
