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
        Schema::table('campaign_recipients', function (Blueprint $table) {
            $table->string('message_subject', 255)->nullable()->after('contact');
            $table->text('message_body')->nullable()->after('message_subject');
            $table->timestamp('sent_at')->nullable()->after('status');
            $table->foreignId('sent_by_user_id')->nullable()->after('sent_at')->constrained('users')->nullOnDelete();

            $table->index(['campaign_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_recipients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sent_by_user_id');
            $table->dropIndex('campaign_recipients_campaign_id_sent_at_index');
            $table->dropColumn([
                'message_subject',
                'message_body',
                'sent_at',
            ]);
        });
    }
};
