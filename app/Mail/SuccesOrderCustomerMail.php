<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class SuccesOrderCustomerMail extends Mailable
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
        ini_set('memory_limit', '256M');

        $pdf = \PDF::loadView('myPDF', [
            'order' => $this->order,
            'states' => $this->states,
            'id' => $this->id,
        ]);

        $filename = 'EBR-2025-' . $this->order->id . '.pdf';

        // Itt hozzuk létre a CID azonosítót
        $logoPath = public_path('img/eb_logo.png');
        $logoCid = '';

        $this->withSwiftMessage(function ($message) use (&$logoCid, $logoPath) {
            $logoCid = $message->embed($logoPath);
        });

        return $this->view('vendor.notifications.SuccesOrderCustomer')
            ->subject('Sikeres Rendelés')
            ->with([
                'order' => $this->order,
                'states' => $this->states,
                'id' => $this->id,
                'logoCid' => $logoCid,
            ])
            ->attachData($pdf->output(), $filename, [
                'mime' => 'application/pdf',
            ]);
    }

}
