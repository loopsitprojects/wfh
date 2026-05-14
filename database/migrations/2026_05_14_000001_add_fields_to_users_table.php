<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'employee'])->default('employee')->after('email');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete()->after('role');
            $table->string('department')->nullable()->after('manager_id');
            $table->string('employee_id')->unique()->nullable()->after('department');
            $table->string('avatar')->nullable()->after('employee_id');
            $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['role', 'manager_id', 'department', 'employee_id', 'avatar', 'is_active']);
        });
    }
};
