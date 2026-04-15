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
        Schema::create('campaign_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_recipient_id')->constrained('campaign_recipients')->cascadeOnDelete();
            $table->foreignId('campaign_document_id')->nullable()->constrained('campaign_documents')->nullOnDelete();
            $table->string('event_type', 20);
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['campaign_recipient_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_tracking_events');
    }
};
