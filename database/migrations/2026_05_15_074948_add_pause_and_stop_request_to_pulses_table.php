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
        Schema::table('pulses', function (Blueprint $table) {
            $table->boolean('is_paused')->default(false);
            $table->boolean('stop_requested')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('pulses', function (Blueprint $table) {
            $table->dropColumn(['is_paused', 'stop_requested']);
        });
    }

};
