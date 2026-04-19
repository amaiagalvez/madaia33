<?php

use App\SupportedLocales;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildUsersTable(codeNullable: true);

            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('code', 9)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $generateUniqueCode = static function (): string {
            do {
                $code = (string) random_int(100000000, 999999999);
            } while (DB::table('users')->where('code', $code)->exists());

            return $code;
        };

        $this->fillMissingCodes($generateUniqueCode);

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildUsersTable(codeNullable: false);

            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('code', 9)->nullable(false)->change();
        });
    }

    private function fillMissingCodes(callable $generateUniqueCode): void
    {
        DB::table('users')
            ->whereNull('code')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $user) use ($generateUniqueCode): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['code' => $generateUniqueCode()]);
            });
    }

    private function rebuildUsersTable(bool $codeNullable): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('users__temp_code_nullable', function (Blueprint $table) use ($codeNullable): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->enum('language', SupportedLocales::all())->default(SupportedLocales::default());
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('code', 9)->nullable($codeNullable)->unique();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamp('delegated_vote_terms_accepted_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users__temp_code_nullable')->insertUsing(
            [
                'id',
                'name',
                'email',
                'language',
                'email_verified_at',
                'password',
                'code',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'remember_token',
                'delegated_vote_terms_accepted_at',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            DB::table('users')->select([
                'id',
                'name',
                'email',
                'language',
                'email_verified_at',
                'password',
                'code',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'remember_token',
                'delegated_vote_terms_accepted_at',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );

        Schema::drop('users');
        Schema::rename('users__temp_code_nullable', 'users');

        Schema::enableForeignKeyConstraints();
    }
};
