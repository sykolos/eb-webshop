<?php

namespace App\Mail;

use App\Models\AutomationRun;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AutomationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AutomationRun $run)
    {
    }

    public function build()
    {
        $subject = match ($this->run->event) {
            'partner_approved' => 'A partnerfiókod jóváhagyva',
            'order_status_changed' => 'Rendelésállapot frissítve',
            'ticket_update' => 'Új ticket értesítés',
            default => 'Electro Business értesítés',
        };
        return $this->view('emails.automation-notification')
            ->text('emails.automation-notification-text')
            ->subject($subject);
    }
}
