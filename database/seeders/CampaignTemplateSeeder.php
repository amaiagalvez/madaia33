<?php

namespace Database\Seeders;

use App\Models\CampaignTemplate;
use Illuminate\Database\Seeder;

class CampaignTemplateSeeder extends Seeder
{
    public function run(): void
    {
        CampaignTemplate::query()->updateOrCreate(
            ['name' => 'Bienvenida propietaria'],
            [
                'subject_eu' => __('admin.owners.email.default_subject', [], 'eu'),
                'subject_es' => __('admin.owners.email.default_subject', [], 'es'),
                'body_eu' => __('admin.owners.email.default_body', [], 'eu'),
                'body_es' => __('admin.owners.email.default_body', [], 'es'),
                'channel' => 'email',
                'created_by_user_id' => null,
            ],
        );
    }
}
