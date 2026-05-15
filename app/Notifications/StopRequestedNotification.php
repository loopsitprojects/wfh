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
            ->subject('Stop Request: ' . $this->pulse->employee->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->pulse->employee->name . ' has requested to stop their active work session.')
            ->action('Review Dashboard', route('manager.dashboard'))
            ->salutation('Regards, The ' . config('app.name') . ' Team');
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
