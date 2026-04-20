<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notice_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('owners')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('opened_at');
            $table->softDeletes();

            $table->unique(['notice_id', 'owner_id'], 'notice_reads_notice_owner_unique');
            $table->index(['notice_id'], 'notice_reads_notice_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_reads');
    }
};
