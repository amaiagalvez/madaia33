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
                'value' => 'info@madaia33.eus',
                'section' => Setting::SECTION_CONTACT_FORM,
            ],
            [
                'key' => 'contact_form_subject_eu',
                'value' => 'Madaia 33 - Kontaktu mezua',
                'section' => Setting::SECTION_CONTACT_FORM,
            ],
            [
                'key' => 'contact_form_subject_es',
                'value' => 'Madaia 33 - Mensaje de contacto',
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
                'key' => 'from_address',
                'value' => 'noreply@madaia33.eus',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'from_name',
                'value' => 'Madaia 33',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'smtp_host',
                'value' => '',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'legal_text_eu',
                'value' => 'Mezua pribatua da eta babestu dago legez.',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
            ],
            [
                'key' => 'legal_text_es',
                'value' => 'Este mensaje es privado y está protegido por ley.',
                'section' => Setting::SECTION_EMAIL_CONFIGURATION,
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
            [
                'key' => 'home_history_text_eu',
                'value' => '<p>Komunitatearen hastapenetara begirada laburra: espazio partekatuak, lehen topaketak eta gaur arte ekarri gaituen bilakaera.</p>',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'home_history_text_es',
                'value' => '<p>Un recorrido breve por los inicios de la comunidad: espacios compartidos, primeros encuentros y la evolución que nos ha traído hasta hoy.</p>',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'front_photo_request_text_eu',
                'value' => 'Zure argazkiak bidali nahi badituzu, idatzi honako helbidera: :email',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'front_photo_request_text_es',
                'value' => 'Si quieres enviar tus fotos, escríbenos a: :email',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'front_primary_email',
                'value' => 'info@madaia33.eus',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'front_site_name',
                'value' => 'Madaia 33',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'front_logo_image_path',
                'value' => 'madaia33/madaia33.png',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'legal_page_cookie_policy_eu',
                'value' => '<p>Cookie politikari buruzko edukia hemen agertuko da.</p>',
                'section' => Setting::SECTION_FRONT,
            ],
            [
                'key' => 'legal_page_cookie_policy_es',
                'value' => '<p>El contenido de la política de cookies aparecerá aquí.</p>',
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
