<?php

namespace App\Services;

use App\Jobs\SendCampaignEmail;
use App\Jobs\SendTestEmail;
use App\Models\EmailCampaign;
use App\Models\EmailDelivery;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

final class EmailCampaignService
{
    public function audience(string $segment = 'newsletter')
    {
        return User::query()
            ->where('account_status', 'active')
            ->whereNotNull('email_verified_at')
            ->whereNotNull('email')
            ->whereHas('preference', function ($query) use ($segment): void {
                $query->where($segment === 'marketing' ? 'marketing_notifications' : 'newsletter_subscribed', true);
            })
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('email_suppressions')->whereColumn('email_suppressions.email', 'users.email'))
            ->orderBy('id');
    }

    public function createCampaign(array $data, User $admin): EmailCampaign
    {
        $campaign = EmailCampaign::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'html_body' => $data['html_body'],
            'text_body' => $data['text_body'] ?: null,
            'type' => 'marketing',
            'status' => filled($data['scheduled_at'] ?? null) ? 'scheduled' : 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'timezone' => $data['timezone'] ?? config('email_campaigns.timezone'),
            'segment' => $data['segment'] ?? 'newsletter',
            'created_by_admin_id' => $admin->id,
        ]);
        $campaign->update(['recipient_count' => $this->audience($campaign->segment)->count()]);
        EmailTemplate::updateOrCreate(
            ['key' => 'marketing-'.Str::slug($campaign->name)],
            ['name' => $campaign->name, 'subject' => $campaign->subject, 'html_body' => $campaign->html_body, 'text_body' => $campaign->text_body, 'type' => 'marketing', 'is_active' => true],
        );
        return $campaign->refresh();
    }

    public function queueCampaign(EmailCampaign $campaign): int
    {
        if (in_array($campaign->status, ['cancelled', 'completed'], true)) {
            return 0;
        }

        $campaign->update(['status' => 'processing', 'started_at' => $campaign->started_at ?: now(), 'recipient_count' => $this->audience($campaign->segment)->count()]);
        $delay = 0;
        $perMinute = max(1, (int) config('email_campaigns.rate_limit_per_minute', 60));
        $queued = 0;

        $this->audience($campaign->segment)->cursor()->each(function (User $user) use ($campaign, &$delay, $perMinute, &$queued): void {
            $delivery = EmailDelivery::firstOrCreate(
                ['idempotency_key' => 'campaign:'.$campaign->id.':user:'.$user->id],
                ['campaign_id' => $campaign->id, 'user_id' => $user->id, 'email' => $user->email, 'type' => 'marketing', 'status' => 'queued'],
            );
            if (! $delivery->wasRecentlyCreated && in_array($delivery->status, ['queued', 'sending', 'sent'], true)) {
                return;
            }
            SendCampaignEmail::dispatch($delivery->id)->delay(now()->addSeconds($delay));
            $queued++;
            $delay += $delay % $perMinute === $perMinute - 1 ? 1 : 0;
            if ($queued % $perMinute === 0) {
                $delay = (int) ceil($queued / $perMinute) * 60;
            }
        });

        if ($queued === 0) {
            $campaign->update(['status' => 'completed', 'completed_at' => now()]);
        }
        return $queued;
    }

    public function sendTest(EmailCampaign $campaign, string $email): void
    {
        SendTestEmail::dispatch($campaign->id, $email);
    }

    public function render(string $body, ?User $user = null, ?EmailDelivery $delivery = null): string
    {
        $name = e($user?->name ?: 'Partner');
        $company = e($user?->user_invoice?->company_name ?: '');
        $unsubscribe = $delivery ? URL::signedRoute('email.unsubscribe', ['delivery' => $delivery->id]) : '#';
        return str_replace(['{{name}}', '{{company}}', '{{unsubscribe_url}}'], [$name, $company, $unsubscribe], $body);
    }

    public function unsubscribe(EmailDelivery $delivery): void
    {
        DB::transaction(function () use ($delivery): void {
            EmailSuppression::updateOrCreate(['email' => strtolower($delivery->email)], ['reason' => 'unsubscribe', 'source' => 'email_link']);
            if ($delivery->user) {
                $delivery->user->preference()->updateOrCreate([], ['newsletter_subscribed' => false, 'marketing_notifications' => false]);
            }
            $delivery->update(['status' => 'skipped', 'last_error' => 'Leiratkozott címzett']);
            if ($delivery->campaign) {
                $delivery->campaign->increment('skipped_count');
            }
        });
    }
}
