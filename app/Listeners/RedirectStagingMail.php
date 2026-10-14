<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;

class RedirectStagingMail
{
    public function handle(MessageSending $event)
    {
        if (!config('staging.enabled')) {
            return;
        }

        $recipient = (string) config('staging.mail_to');
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            // Fail closed for every Laravel mailer, including named/queued mailers.
            return false;
        }

        $event->message->to($recipient);
        $event->message->cc();
        $event->message->bcc();
    }
}
