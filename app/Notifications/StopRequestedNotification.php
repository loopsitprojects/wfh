<?php

namespace App\Notifications;

use App\Models\Pulse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StopRequestedNotification extends Notification implements ShouldQueue
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
            ->subject('Stop Session Request from ' . $this->pulse->employee->name)
            ->view('emails.stop_requested', [
                'pulse' => $this->pulse,
                'notifiable' => $notifiable,
            ]);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'          => 'stop_requested',
            'pulse_id'      => $this->pulse->id,
            'employee_name' => $this->pulse->employee->name,
            'message'       => $this->pulse->employee->name . ' wants to stop their work session.',
            'url'           => route('manager.dashboard'),
        ];
    }
}
