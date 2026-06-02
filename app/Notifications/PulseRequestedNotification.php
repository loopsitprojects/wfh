<?php

namespace App\Notifications;

use App\Models\Pulse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PulseRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Pulse $pulse) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New WFH Pulse Request from ' . $this->pulse->employee->name)
            ->view('emails.pulse_requested', [
                'pulse' => $this->pulse,
                'notifiable' => $notifiable,
            ]);
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

