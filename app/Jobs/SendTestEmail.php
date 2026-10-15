<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public int $campaignId, public string $email)
    {
    }

    public function handle(): void
    {
        $campaign = EmailCampaign::findOrFail($this->campaignId);
        $delivery = new EmailDelivery(['email' => $this->email, 'type' => 'marketing', 'status' => 'sending', 'idempotency_key' => 'test:'.$campaign->id.':'.sha1($this->email)]);
        Mail::to($this->email)->send(new CampaignMail($campaign, $delivery));
    }
}
