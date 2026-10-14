<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'current_password', 'token',
        'secret', 'two_factor_secret', 'recovery_codes', 'authorization',
        'cookie', 'remember_token', 'api_key', 'access_token',
    ];

    public function record(string $action, ?Model $subject = null, array $properties = [], ?Request $request = null): AuditLog
    {
        $request ??= app()->bound('request') ? request() : null;
        $properties = $this->mask($properties);

        $log = AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'request_id' => $request?->attributes->get('request_id'),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'properties' => $properties,
            'created_at' => now(),
        ]);

        Log::info('Audit event recorded.', [
            'audit_id' => $log->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
        ]);

        return $log;
    }

    public function mask(array $value): array
    {
        $masked = [];

        foreach ($value as $key => $item) {
            $keyString = strtolower((string) $key);
            $isSensitive = collect(self::SENSITIVE_KEYS)->contains(
                fn (string $sensitive) => str_contains($keyString, $sensitive)
            );

            $masked[$key] = $isSensitive
                ? '[REDACTED]'
                : (is_array($item) ? $this->mask($item) : $item);
        }

        return $masked;
    }
}
