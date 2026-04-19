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
            DirectMessagesCampaignSeeder::class,
            CampaignTemplateSeeder::class,
        ]);

        if (app()->isLocal()) {
            $this->call([
                LocationSeeder::class,
                PropertySeeder::class,
                DevSeeder::class,
            ]);
        }
    }
}
