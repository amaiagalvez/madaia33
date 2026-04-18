<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('voting_selections', function (Blueprint $table): void {
            $table->decimal('pct_total', 8, 4)->default(0)->after('owner_id');
        });

        $activePctByOwner = DB::table('property_assignments')
            ->join('properties', 'properties.id', '=', 'property_assignments.property_id')
            ->whereNull('property_assignments.end_date')
            ->whereNull('property_assignments.deleted_at')
            ->whereNull('properties.deleted_at')
            ->groupBy('property_assignments.owner_id')
            ->selectRaw('property_assignments.owner_id, COALESCE(SUM(COALESCE(properties.community_pct, 0)), 0) as pct_total')
            ->pluck('pct_total', 'property_assignments.owner_id');

        DB::table('voting_selections')
            ->select(['id', 'owner_id'])
            ->orderBy('id')
            ->chunkById(250, function ($selections) use ($activePctByOwner): void {
                foreach ($selections as $selection) {
                    DB::table('voting_selections')
                        ->where('id', $selection->id)
                        ->update([
                            'pct_total' => (float) ($activePctByOwner[$selection->owner_id] ?? 0),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('voting_selections', function (Blueprint $table): void {
            $table->dropColumn('pct_total');
        });
    }
};
