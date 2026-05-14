<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    protected $fillable = [
        'employee_id', 'pulse_id', 'allocated_hours', 'started_at', 'ended_at', 'duration_seconds', 'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function pulse()
    {
        return $this->belongsTo(Pulse::class);
    }

    public function getDurationFormatted(): string
    {
        if (!$this->duration_seconds) return '—';
        $h = intdiv($this->duration_seconds, 3600);
        $m = intdiv($this->duration_seconds % 3600, 60);
        $s = $this->duration_seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
