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
        $subject  = $approved ? 'WFH Pulse Approved!' : 'WFH Pulse Rejected';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.pulse_decision', [
                'pulse' => $this->pulse,
                'notifiable' => $notifiable,
            ]);
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

