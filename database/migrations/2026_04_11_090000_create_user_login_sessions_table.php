<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_login_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('impersonator_user_id')->nullable()->constrained('users');
            $table->string('session_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamp('logged_out_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'logged_in_at']);
            $table->index(['user_id', 'logged_out_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_sessions');
    }
};
