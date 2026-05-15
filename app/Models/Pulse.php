<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pulse extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'manager_id', 'approved_by', 'image_path',
        'description', 'status', 'duration_hours', 'rejection_reason', 'approved_at',
        'is_paused', 'stop_requested',
    ];


    public function getSpentSeconds(): int
    {
        return (int) $this->timeLog()->whereNotNull('ended_at')->sum('duration_seconds');
    }

    public function timeLog()
    {
        return $this->hasMany(TimeLog::class, 'pulse_id');
    }


    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }



    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default    => 'badge-warning',
        };
    }
}
