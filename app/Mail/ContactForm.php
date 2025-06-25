<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactForm extends Mailable
{
    use Queueable, SerializesModels;

    public $cname,$csubject,$cemail,$cmessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cname,$cemail,$csubject,$cmessage)
    {
        //
        $this->cname=$cname;
        $this->cemail=$cemail;
        $this->csubject=$csubject;
        $this->cmessage=$cmessage;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject:  'Üzenet a kapcsolat oldalon keresztül',
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
            markdown: 'vendor.notifications.ContactForm',
        );
    }

    /**
     * Get the attachments for the message. 
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
