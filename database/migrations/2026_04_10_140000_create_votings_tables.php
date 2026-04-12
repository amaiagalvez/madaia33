<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('votings', function (Blueprint $table): void {
            $table->id();
            $table->string('name_eu');
            $table->string('name_es')->nullable();
            $table->text('question_eu');
            $table->text('question_es')->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['starts_at', 'ends_at']);
            $table->index(['is_published', 'starts_at', 'ends_at'], 'votings_published_dates_index');
        });

        Schema::create('voting_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('voting_id')->constrained();
            $table->string('label_eu');
            $table->string('label_es')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['voting_id', 'position']);
        });

        Schema::create('voting_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('voting_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['voting_id', 'location_id']);
        });

        Schema::create('voting_ballots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('voting_id')->constrained();
            $table->foreignId('owner_id')->constrained();
            $table->foreignId('cast_by_user_id')->nullable()->constrained('users');
            $table->ipAddress('cast_ip_address')->nullable();
            $table->decimal('cast_latitude', 10, 7)->nullable();
            $table->decimal('cast_longitude', 10, 7)->nullable();
            $table->string('cast_delegate_dni')->nullable();
            $table->boolean('is_in_person')->default(false);
            $table->timestamp('voted_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['voting_id', 'owner_id']);
            $table->index(['owner_id', 'voted_at']);
        });

        Schema::create('voting_selections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('voting_id')->constrained();
            $table->foreignId('voting_ballot_id')->constrained('voting_ballots');
            $table->foreignId('owner_id')->constrained();
            $table->foreignId('voting_option_id')->constrained();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['voting_ballot_id']);
            $table->index(['voting_id', 'owner_id']);
        });

        Schema::create('voting_option_totals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('voting_id')->constrained();
            $table->foreignId('voting_option_id')->constrained();
            $table->unsignedInteger('votes_count')->default(0);
            $table->decimal('pct_total', 8, 4)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['voting_id', 'voting_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_option_totals');
        Schema::dropIfExists('voting_selections');
        Schema::dropIfExists('voting_ballots');
        Schema::dropIfExists('voting_locations');
        Schema::dropIfExists('voting_options');
        Schema::dropIfExists('votings');
    }
};
