<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'admin_email' => 'admin@madaia33.eus',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'legal_checkbox_text_eu' => 'Pribatutasun politika irakurri eta onartzen dut.',
            'legal_checkbox_text_es' => 'He leído y acepto la política de privacidad.',
            'legal_url' => '/politica-de-privacidad',
            'legal_page_privacy_policy_eu' => '<p>Pribatutasun politikaren edukia hemen agertuko da.</p>',
            'legal_page_privacy_policy_es' => '<p>El contenido de la política de privacidad aparecerá aquí.</p>',
            'legal_page_legal_notice_eu' => '<p>Lege-ohartarazpenaren edukia hemen agertuko da.</p>',
            'legal_page_legal_notice_es' => '<p>El contenido del aviso legal aparecerá aquí.</p>',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
