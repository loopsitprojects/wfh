<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pulses', function (Blueprint $table) {
            $table->float('duration_hours')->nullable()->after('status');
        });

        Schema::table('time_logs', function (Blueprint $table) {
            $table->float('allocated_hours')->nullable()->after('pulse_id');
        });
    }

    public function down(): void
    {
        Schema::table('pulses', function (Blueprint $table) {
            $table->dropColumn('duration_hours');
        });

        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropColumn('allocated_hours');
        });
    }
};
