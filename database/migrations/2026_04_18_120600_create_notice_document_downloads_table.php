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
        Schema::create('notice_document_downloads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notice_document_id')->constrained('notice_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('downloaded_at');

            $table->index(['notice_document_id', 'downloaded_at'], 'notice_doc_downloads_doc_downloaded_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_document_downloads');
    }
};
