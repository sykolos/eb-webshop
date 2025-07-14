<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class SuccesOrderAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $states;
    public $id;

    public function __construct($order, $states, $id)
    {
        $this->order = $order;
        $this->states = $states;
        $this->id = $id;
    }

    public function build()
    {
        $pdf = Pdf::loadView('myPDF', [
            'order' => $this->order,
            'states' => $this->states,
            'id' => $this->id,
        ]);

        $filename = 'EBR-2025-' . $this->order->id . '.pdf';

        return $this->view('vendor.notifications.SuccesOrderAdmin')
            ->subject('Új rendelés érkezett a webshopból!')
            ->with([
                'order' => $this->order,
                'states' => $this->states,
                'id' => $this->id,
            ])
            ->attachData($pdf->output(), $filename, [
                'mime' => 'application/pdf',
            ]);
    }
}
