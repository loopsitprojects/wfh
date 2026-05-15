<?php

namespace App\Notifications;

use App\Models\Pulse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PulseDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Pulse $pulse) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $approved = $this->pulse->isApproved();
        $subject  = $approved ? 'Pulse Approved!' : 'Pulse Rejected';
        
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($approved) {
            $message->line('Great news! Your pulse request has been approved.')
                   ->line('Allocated time: ' . $this->pulse->duration_hours . ' hours.')
                   ->action('Start Timer', route('employee.dashboard'));
        } else {
            $message->line('Unfortunately, your pulse request was rejected.')
                   ->line('Reason: ' . ($this->pulse->rejection_reason ?? 'No reason provided.'))
                   ->action('Request Again', route('employee.dashboard'));
        }

        return $message->line('Stay productive!')
                       ->salutation('Regards, The ' . config('app.name') . ' Team');
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

