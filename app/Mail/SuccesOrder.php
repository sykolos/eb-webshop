<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuccesOrder extends Mailable
{
    use Queueable, SerializesModels;
    public $name,$pdf,$order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$pdf,$order)
    {
        //
        $this->name=$name;
        $this->pdf=$pdf;
        $this->order=$order;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Sikeres Rendelés!',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'vendor.notifications.SuccesOrder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return  [];
    }
}
