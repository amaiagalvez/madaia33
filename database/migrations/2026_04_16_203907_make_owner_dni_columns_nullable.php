<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->string('coprop1_dni', 20)->nullable()->change();
            $table->string('coprop2_dni', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('owners')
            ->whereNull('coprop1_dni')
            ->update(['coprop1_dni' => '']);

        Schema::table('owners', function (Blueprint $table) {
            $table->string('coprop1_dni', 20)->nullable(false)->change();
            $table->string('coprop2_dni', 20)->nullable()->change();
        });
    }
};
