<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\EmailDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];

    public function __construct(public int $deliveryId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $delivery = EmailDelivery::with(['campaign', 'user.preference'])->findOrFail($this->deliveryId);
        if (! $delivery->campaign || $delivery->campaign->status === 'cancelled' || $delivery->status === 'sent') {
            return;
        }
        $preferenceAllowed = $delivery->campaign->segment === 'marketing'
            ? $delivery->user?->preference?->marketing_notifications
            : $delivery->user?->preference?->newsletter_subscribed;
        if (! $delivery->user || ! $delivery->user->email_verified_at || ! $preferenceAllowed || $delivery->user->account_status !== 'active') {
            $delivery->update(['status' => 'skipped', 'last_error' => 'A címzett jogosultsága vagy preferenciája megváltozott.']);
            $delivery->campaign->increment('skipped_count');
            return;
        }
        $delivery->increment('attempts');
        $delivery->update(['status' => 'sending']);
        try {
            Mail::to($delivery->email)->send(new CampaignMail($delivery->campaign, $delivery));
            $delivery->update(['status' => 'sent', 'sent_at' => now(), 'last_error' => null]);
            $delivery->campaign->increment('sent_count');
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
            $delivery->campaign->increment('failed_count');
            throw $exception;
        }
    }
}
