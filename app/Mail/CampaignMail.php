<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailDelivery;
use App\Services\EmailCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmailCampaign $campaign, public EmailDelivery $delivery)
    {
    }

    public function build()
    {
        $service = app(EmailCampaignService::class);
        $user = $this->delivery->user?->loadMissing('user_invoice');
        $html = $service->render($this->campaign->html_body, $user, $this->delivery);
        $text = $service->render($this->campaign->text_body ?: strip_tags($this->campaign->html_body), $user, $this->delivery);
        $unsubscribeUrl = $this->delivery->exists
            ? URL::signedRoute('email.unsubscribe', ['delivery' => $this->delivery->id])
            : '#';

        return $this->view('emails.campaign')
            ->text('emails.campaign-text')
            ->subject($this->campaign->subject)
            ->with(['body' => $html, 'textBody' => $text, 'unsubscribeUrl' => $unsubscribeUrl]);
    }
}
