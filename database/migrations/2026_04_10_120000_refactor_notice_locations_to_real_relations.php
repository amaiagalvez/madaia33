<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('notice_locations', function (Blueprint $table): void {
      $table->foreignId('location_id')->nullable()->after('notice_id')->constrained('locations')->nullOnDelete();
    });

    $codeToLocationId = DB::table('locations')
      ->select(['id', 'code'])
      ->pluck('id', 'code');

    DB::table('notice_locations')
      ->select(['id', 'location_code'])
      ->orderBy('id')
      ->chunkById(200, function ($rows) use ($codeToLocationId): void {
        foreach ($rows as $row) {
          $locationId = $codeToLocationId[$row->location_code] ?? null;

          DB::table('notice_locations')
            ->where('id', $row->id)
            ->update(['location_id' => $locationId]);
        }
      });

    Schema::table('notice_locations', function (Blueprint $table): void {
      $table->dropColumn(['location_type', 'location_code']);
    });
  }

  public function down(): void
  {
    Schema::table('notice_locations', function (Blueprint $table): void {
      $table->string('location_type')->nullable()->after('notice_id');
      $table->string('location_code')->nullable()->after('location_type');
    });

    DB::table('notice_locations')
      ->leftJoin('locations', 'notice_locations.location_id', '=', 'locations.id')
      ->select([
        'notice_locations.id',
        'locations.type as direct_type',
        'locations.code as direct_code',
      ])
      ->orderBy('notice_locations.id')
      ->chunkById(200, function ($rows): void {
        foreach ($rows as $row) {
          DB::table('notice_locations')
            ->where('id', $row->id)
            ->update([
              'location_type' => $row->direct_type,
              'location_code' => $row->direct_code,
            ]);
        }
      }, 'notice_locations.id');

    Schema::table('notice_locations', function (Blueprint $table): void {
      $table->dropConstrainedForeignId('location_id');
    });
  }
};
