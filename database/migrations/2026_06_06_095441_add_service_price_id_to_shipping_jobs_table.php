<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipping_jobs', function (Blueprint $table) {
            $table->foreignId('service_price_id')->nullable()->after('container_type')->constrained('service_prices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_jobs', function (Blueprint $table) {
            $table->dropForeign(['service_price_id']);
            $table->dropColumn('service_price_id');
        });
    }
};
