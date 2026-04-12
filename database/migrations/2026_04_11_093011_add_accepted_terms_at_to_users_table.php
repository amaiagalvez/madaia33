<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No-op: accepted_terms_at belongs to owners table.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: accepted_terms_at belongs to owners table.
    }
};
