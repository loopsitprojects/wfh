<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ───────────────────────────────────────────────────────
        $admin = User::create([
            'name'        => 'Admin User',
            'username'    => 'admin',
            'email'       => 'admin@wfh.com',
            'password'    => bcrypt('password'),
            'role'        => 'admin',
            'department'  => 'Administration',
            'employee_id' => 'ADM001',
            'is_active'   => true,
        ]);

        // ── Manager ─────────────────────────────────────────────────────
        $manager = User::create([
            'name'        => 'Sarah Manager',
            'username'    => 'manager',
            'email'       => 'manager@wfh.com',
            'password'    => bcrypt('password'),
            'role'        => 'manager',
            'department'  => 'Engineering',
            'employee_id' => 'MGR001',
            'is_active'   => true,
        ]);

        // ── Employee ─────────────────────────────────────────────────────
        $employee = User::create([
            'name'        => 'John Employee',
            'username'    => 'employee',
            'email'       => 'employee@wfh.com',
            'password'    => bcrypt('password'),
            'role'        => 'employee',
            'manager_id'  => $manager->id,
            'department'  => 'Engineering',
            'employee_id' => 'EMP001',
            'is_active'   => true,
        ]);

        // ── Sample Project ───────────────────────────────────────────────
        $project = Project::create([
            'name'        => 'WFH Tracker v1',
            'description' => 'Internal work-from-home tracking system.',
            'manager_id'  => $manager->id,
            'status'      => 'active',
            'deadline'    => now()->addMonths(2)->toDateString(),
        ]);
        $project->employees()->attach($employee->id);
    }
}
