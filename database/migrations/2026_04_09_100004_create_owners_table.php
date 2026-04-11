<?php

use App\SupportedLocales;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('coprop1_name');
            $table->string('coprop1_dni', 20);
            $table->string('coprop1_phone', 20)->nullable();
            $table->string('coprop1_email');
            $table->enum('language', SupportedLocales::all())->default(SupportedLocales::default());
            $table->string('coprop2_name')->nullable();
            $table->string('coprop2_dni', 20)->nullable();
            $table->string('coprop2_phone', 20)->nullable();
            $table->string('coprop2_email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
