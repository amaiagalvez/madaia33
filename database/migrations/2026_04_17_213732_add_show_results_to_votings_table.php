<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('votings', function (Blueprint $table): void {
            $table->boolean('show_results')->default(false)->after('is_anonymous');
        });
    }

    public function down(): void
    {
        Schema::table('votings', function (Blueprint $table): void {
            $table->dropColumn('show_results');
        });
    }
};
