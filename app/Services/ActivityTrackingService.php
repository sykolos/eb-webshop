<?php

namespace App\Services;

use App\Models\ActivityEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ActivityTrackingService
{
    public const EVENTS = [
        'page_view', 'search', 'filter', 'product_view', 'cart_add', 'cart_update',
        'cart_remove', 'checkout_step', 'purchase', 'registration_request',
    ];

    public function record(Request $request, array $data): ?ActivityEvent
    {
        if ($request->cookie('eb_analytics_consent') !== 'accepted' && $request->header('X-EB-Analytics-Consent') !== 'accepted') {
            return null;
        }

        $eventUuid = (string) $data['event_uuid'];
        $payload = $this->sanitizePayload((array) ($data['payload'] ?? []));
        $event = ActivityEvent::firstOrCreate(
            ['event_uuid' => $eventUuid],
            [
                'user_id' => $request->user()?->getKey(),
                'session_hash' => $request->hasSession() && $request->session()->getId()
                    ? hash('sha256', $request->session()->getId())
                    : null,
                'event_name' => $data['event_name'],
                'page_path' => $this->path($data['page_path'] ?? $request->path()),
                'referrer_host' => $this->host($data['referrer'] ?? $request->header('referer')),
                'campaign_source' => $this->short($data['campaign_source'] ?? null),
                'campaign_medium' => $this->short($data['campaign_medium'] ?? null),
                'campaign_name' => $this->short($data['campaign_name'] ?? null),
                'device_type' => $this->device($data['device_type'] ?? null, $request->userAgent()),
                'payload' => $payload,
                'occurred_at' => now(),
                'expires_at' => now()->addMonths(6),
            ],
        );

        if ($request->user()) {
            $request->user()->forceFill(['last_seen_at' => now()])->saveQuietly();
        }

        return $event;
    }

    public function purgeExpired(): int
    {
        return ActivityEvent::whereNotNull('expires_at')->where('expires_at', '<', now())->delete();
    }

    public function mask(ActivityEvent $event): array
    {
        return [
            'id' => $event->id,
            'event_name' => $event->event_name,
            'page_path' => $event->page_path,
            'device_type' => $event->device_type,
            'campaign_source' => $event->campaign_source,
            'campaign_medium' => $event->campaign_medium,
            'occurred_at' => $event->occurred_at?->format('Y-m-d H:i:s'),
            'payload' => $this->sanitizePayload($event->payload ?? []),
        ];
    }

    private function sanitizePayload(array $payload): array
    {
        $blocked = ['email', 'name', 'phone', 'address', 'price', 'unit_price', 'partner_price', 'token', 'password'];
        return collect($payload)->filter(function ($value, $key) use ($blocked): bool {
            $key = Str::lower((string) $key);
            return !collect($blocked)->contains(fn ($term) => Str::contains($key, $term));
        })->map(function ($value) {
            if (is_array($value)) return $this->sanitizePayload($value);
            return is_scalar($value) ? Str::limit((string) $value, 191, '') : null;
        })->filter(fn ($value) => $value !== null)->take(12)->all();
    }

    private function path(?string $path): string
    {
        return Str::limit(parse_url((string) $path, PHP_URL_PATH) ?: '/', 255, '');
    }

    private function host(?string $url): ?string
    {
        $host = parse_url((string) $url, PHP_URL_HOST);
        return $host ? Str::limit($host, 191, '') : null;
    }

    private function short(?string $value): ?string
    {
        return filled($value) ? Str::limit(trim($value), 191, '') : null;
    }

    private function device(?string $device, ?string $agent): string
    {
        if (in_array($device, ['desktop', 'tablet', 'mobile'], true)) return $device;
        $agent = Str::lower((string) $agent);
        return Str::contains($agent, ['ipad', 'tablet']) ? 'tablet' : (Str::contains($agent, ['mobile', 'android']) ? 'mobile' : 'desktop');
    }
}
