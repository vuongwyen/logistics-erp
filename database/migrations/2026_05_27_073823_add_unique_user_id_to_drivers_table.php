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
        DB::table('drivers')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id')
            ->each(function (int $userId): void {
                $driverIds = DB::table('drivers')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->pluck('id');

                DB::table('drivers')
                    ->whereIn('id', $driverIds->skip(1)->all())
                    ->update(['user_id' => null]);
            });

        Schema::table('drivers', function (Blueprint $table) {
            $table->unique('user_id', 'drivers_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropUnique('drivers_user_id_unique');
        });
    }
};
