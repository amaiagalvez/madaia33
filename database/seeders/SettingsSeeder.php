<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key' => 'admin_email',
                'value' => 'admin@madaia33.eus',
                'section' => Setting::SECTION_CONTACT_FORM,
            ],
            [
                'key' => 'recaptcha_site_key',
                'value' => '',
                'section' => Setting::SECTION_RECAPTCHA,
            ],
            [
                'key' => 'recaptcha_secret_key',
                'value' => '',
                'section' => Setting::SECTION_RECAPTCHA,
            ],
            [
                'key' => 'legal_checkbox_text_eu',
                'value' => 'Pribatutasun politika irakurri eta onartzen dut.',
                'section' => Setting::SECTION_CONTACT_FORM,
            ],
            [
                'key' => 'legal_checkbox_text_es',
                'value' => 'He leído y acepto la política de privacidad.',
                'section' => Setting::SECTION_CONTACT_FORM,
            ],
            [
                'key' => 'legal_url',
                'value' => '/politica-de-privacidad',
                'section' => Setting::SECTION_CONTACT_FORM,
            ],
            [
                'key' => 'legal_page_privacy_policy_eu',
                'value' => '<p>Pribatutasun politikaren edukia hemen agertuko da.</p>',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'legal_page_privacy_policy_es',
                'value' => '<p>El contenido de la política de privacidad aparecerá aquí.</p>',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'legal_page_legal_notice_eu',
                'value' => '<p>Lege-ohartarazpenaren edukia hemen agertuko da.</p>',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'legal_page_legal_notice_es',
                'value' => '<p>El contenido del aviso legal aparecerá aquí.</p>',
                'section' => Setting::SECTION_FRONT,
            ],
        ];

        foreach ($defaults as $data) {
            Setting::firstOrCreate(
                ['key' => $data['key']],
                ['value' => $data['value'], 'section' => $data['section']]
            );
        }
    }
}
