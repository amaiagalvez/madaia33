<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class DirectMessagesCampaignSeeder extends Seeder
{
    public function run(): void
    {
        Campaign::unguarded(function (): void {
            Campaign::withTrashed()->firstOrCreate(
                ['id' => 1],
                [
                    'created_by_user_id' => null,
                    'subject_eu' => 'Web-etik Bidalitako Mezuak',
                    'subject_es' => 'Mensajes enviados desde la web',
                    'body_eu' => null,
                    'body_es' => null,
                    'channel' => 'email',
                    'status' => 'sent',
                    'scheduled_at' => null,
                    'sent_at' => now(),
                    'deleted_at' => null,
                ],
            );
        });
    }
}
