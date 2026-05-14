<?php

namespace App\Notifications;

use App\Models\Pulse;
use Illuminate\Notifications\Notification;

class PulseRequestedNotification extends Notification
{
    public function __construct(public Pulse $pulse) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'          => 'pulse_requested',
            'pulse_id'      => $this->pulse->id,
            'employee_name' => $this->pulse->employee->name,
            'message'       => $this->pulse->employee->name . ' has requested a work pulse.',
            'url'           => route('manager.pulses'),
        ];
    }
}
