<?php

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'admin_email')
            ->update(['section' => Setting::SECTION_CONTACT_FORM]);

        DB::table('settings')
            ->whereIn('key', ['recaptcha_site_key', 'recaptcha_secret_key'])
            ->update(['section' => Setting::SECTION_RECAPTCHA]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'admin_email')
            ->update(['section' => Setting::SECTION_FRONT]);

        DB::table('settings')
            ->where('key', 'recaptcha_site_key')
            ->update(['section' => Setting::SECTION_FRONT]);

        DB::table('settings')
            ->where('key', 'recaptcha_secret_key')
            ->update(['section' => Setting::SECTION_CONTACT_FORM]);
    }
};
