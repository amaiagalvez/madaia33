<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->string('coprop1_telegram_id')->nullable()->after('coprop1_phone');
            $table->string('coprop2_telegram_id')->nullable()->after('coprop2_phone');

            $table->unsignedTinyInteger('coprop1_email_error_count')->default(0);
            $table->boolean('coprop1_email_invalid')->default(false);
            $table->unsignedTinyInteger('coprop1_phone_error_count')->default(0);
            $table->boolean('coprop1_phone_invalid')->default(false);

            $table->unsignedTinyInteger('coprop2_email_error_count')->default(0);
            $table->boolean('coprop2_email_invalid')->default(false);
            $table->unsignedTinyInteger('coprop2_phone_error_count')->default(0);
            $table->boolean('coprop2_phone_invalid')->default(false);

            $table->timestamp('last_contact_error_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn([
                'coprop1_telegram_id',
                'coprop2_telegram_id',
                'coprop1_email_error_count',
                'coprop1_email_invalid',
                'coprop1_phone_error_count',
                'coprop1_phone_invalid',
                'coprop2_email_error_count',
                'coprop2_email_invalid',
                'coprop2_phone_error_count',
                'coprop2_phone_invalid',
                'last_contact_error_at',
            ]);
        });
    }
};
