<?php

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /** @var array<string, string> */
    private array $knownKeyMap = [
        'admin_email' => Setting::SECTION_FRONT,
        'recaptcha_site_key' => Setting::SECTION_FRONT,
        'recaptcha_secret_key' => Setting::SECTION_CONTACT_FORM,
        'legal_checkbox_text_eu' => Setting::SECTION_CONTACT_FORM,
        'legal_checkbox_text_es' => Setting::SECTION_CONTACT_FORM,
        'legal_url' => Setting::SECTION_CONTACT_FORM,
        'legal_page_privacy_policy_eu' => Setting::SECTION_FRONT,
        'legal_page_privacy_policy_es' => Setting::SECTION_FRONT,
        'legal_page_legal_notice_eu' => Setting::SECTION_FRONT,
        'legal_page_legal_notice_es' => Setting::SECTION_FRONT,
    ];

    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->string('section')->default('general')->after('value');
            $table->index('section');
        });

        $rows = DB::table('settings')->whereNull('deleted_at')->get(['id', 'key']);

        foreach ($rows as $row) {
            $section = $this->knownKeyMap[$row->key] ?? null;

            if ($section === null) {
                Log::warning("add_section migration: unknown key '{$row->key}', defaulting to 'general'.");
                $section = 'general';
            }

            DB::table('settings')->where('id', $row->id)->update(['section' => $section]);
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropIndex(['section']);
            $table->dropColumn('section');
        });
    }
};
