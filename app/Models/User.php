<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role',
        'manager_id', 'department', 'employee_id', 'avatar', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /* ---------- Relationships ---------- */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function pulses()
    {
        return $this->hasMany(Pulse::class, 'employee_id');
    }

    public function managedPulses()
    {
        return $this->hasMany(Pulse::class, 'manager_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class, 'employee_id');
    }

    /* ---------- Helpers ---------- */
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isManager(): bool  { return $this->role === 'manager'; }
    public function isEmployee(): bool { return $this->role === 'employee'; }

    /** First approved pulse with no timer session started */
    public function getActivePulse(): ?Pulse
    {
        return $this->pulses()
            ->where('status', 'approved')
            ->latest()
            ->first();
    }


    /** Currently running (not stopped) timer */
    public function getActiveTimer(): ?TimeLog
    {
        return $this->timeLogs()->whereNull('ended_at')->latest()->first();
    }

    /** Total seconds worked today */
    public function getTodaySeconds(): int
    {
        $today = (int) $this->timeLogs()
            ->whereDate('started_at', today())
            ->whereNotNull('ended_at')
            ->sum('duration_seconds');

        $active = $this->getActiveTimer();
        if ($active) {
            $elapsed = now()->diffInSeconds($active->started_at);
            
            // Cap to pulse remaining time
            $pulse = $active->pulse;
            if ($pulse) {
                $spentSoFar = $pulse->timeLog()->where('id', '!=', $active->id)->whereNotNull('ended_at')->sum('duration_seconds');
                $remaining = ($pulse->duration_hours * 3600) - $spentSoFar;
                $today += min($elapsed, $remaining);
            } else {
                $today += $elapsed;
            }
        }
        
        return max(0, $today);
    }


    /** Total seconds worked this week */
    public function getWeekSeconds(): int
    {
        return (int) $this->timeLogs()
            ->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotNull('ended_at')
            ->sum('duration_seconds');
    }

    public function dashboardRoute(): string
    {
        return match($this->role) {
            'admin'   => route('admin.dashboard'),
            'manager' => route('manager.dashboard'),
            default   => route('employee.dashboard'),
        };
    }
}
