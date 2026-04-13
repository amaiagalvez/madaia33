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
        $connection = Schema::getConnection();
        $schema = $connection->getSchemaBuilder();

        if (! $schema->hasColumn('owners', 'coprop1_surname')) {
            $connection->statement('ALTER TABLE owners ADD COLUMN coprop1_surname VARCHAR(255) NULL');
        }

        if (! $schema->hasColumn('owners', 'coprop2_surname')) {
            $connection->statement('ALTER TABLE owners ADD COLUMN coprop2_surname VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['coprop1_surname', 'coprop2_surname']);
        });
    }
};
