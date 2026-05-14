<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pulse extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'manager_id', 'image_path',
        'description', 'status', 'duration_hours', 'rejection_reason', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function timeLog()
    {
        return $this->hasOne(TimeLog::class, 'pulse_id');
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
