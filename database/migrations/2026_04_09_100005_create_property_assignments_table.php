<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained();
            $table->foreignId('owner_id')->constrained();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->tinyInteger('active_assignment_key')->nullable()->storedAs('case when end_date is null then 1 else null end');
            $table->boolean('admin_validated')->default(false);
            $table->boolean('owner_validated')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'active_assignment_key'], 'property_assignments_active_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_assignments');
    }
};
