<?php

namespace App\Notifications;

use App\Models\Pulse;
use Illuminate\Notifications\Notification;

class PulseDecisionNotification extends Notification
{
    public function __construct(public Pulse $pulse) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $approved = $this->pulse->isApproved();
        return [
            'type'    => 'pulse_decision',
            'pulse_id' => $this->pulse->id,
            'status'  => $this->pulse->status,
            'message' => $approved
                ? 'Your pulse request was approved! You can now start your timer.'
                : 'Your pulse request was rejected. Reason: ' . ($this->pulse->rejection_reason ?? 'No reason provided.'),
            'url'     => route('employee.dashboard'),
        ];
    }
}
