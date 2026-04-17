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
            $table->boolean('coprop1_has_whatsapp')->default(false)->after('coprop1_phone');
            $table->boolean('coprop2_has_whatsapp')->default(false)->after('coprop2_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn([
                'coprop1_has_whatsapp',
                'coprop2_has_whatsapp',
            ]);
        });
    }
};
