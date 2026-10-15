<?php

namespace App\Services;

use App\Jobs\SendAutomationNotification;
use App\Jobs\SendCartReminder;
use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Models\Cart;
use App\Models\EmailSuppression;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class AutomationRuleService
{
    public function rule(string $key): ?AutomationRule
    {
        return AutomationRule::query()->where('key', $key)->first();
    }

    public function isEnabled(string $key): bool
    {
        return (bool) $this->rule($key)?->enabled;
    }

    public function dispatchDueCarts(?Carbon $now = null): int
    {
        $rule = $this->rule('abandoned_cart');
        if (! $rule?->enabled) {
            return 0;
        }

        $now ??= now();
        if (! $this->withinWindow($rule, $now)) {
            return 0;
        }

        $threshold = $now->copy()->subMinutes(max(1, (int) $rule->delay_minutes));
        $queued = 0;
        Cart::query()
            ->with(['user.preference', 'items'])
            ->whereNull('converted_at')
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<=', $threshold)
            ->whereHas('items')
            ->whereHas('user', function ($query): void {
                $query->where('account_status', 'active')
                    ->whereNotNull('email_verified_at')
                    ->whereNotNull('email')
                    ->whereHas('preference', fn ($preference) => $preference->where('marketing_notifications', true))
                    ->whereNotExists(fn ($suppressed) => $suppressed->selectRaw('1')->from('email_suppressions')->whereColumn('email_suppressions.email', 'users.email'));
            })
            ->orderBy('id')
            ->limit((int) config('automations.dispatch_batch', 500))
            ->get()
            ->each(function (Cart $cart) use ($rule, $now, &$queued): void {
                $activity = $cart->last_activity_at?->toIso8601String();
                if (! $activity || $this->hasReachedLimit($rule, $cart->user_id, $now)) {
                    return;
                }

                $key = 'cart:'.$cart->id.':activity:'.$activity;
                $run = AutomationRun::firstOrCreate(
                    ['idempotency_key' => $key],
                    [
                        'rule_id' => $rule->id,
                        'event' => 'cart_abandoned',
                        'user_id' => $cart->user_id,
                        'cart_id' => $cart->id,
                        'subject_id' => (string) $cart->id,
                        'payload' => ['activity_at' => $activity, 'item_count' => $cart->items->sum('quantity')],
                        'status' => 'queued',
                        'scheduled_for' => $now,
                    ],
                );
                if (! $run->wasRecentlyCreated || $run->status !== 'queued') {
                    return;
                }

                SendCartReminder::dispatch($run->id);
                $queued++;
            });

        $rule->forceFill(['last_run_at' => $now, 'next_run_at' => $now->copy()->addMinutes(5)])->save();

        return $queued;
    }

    public function dispatchEvent(string $event, User $user, array $context = []): ?AutomationRun
    {
        $rule = $this->rule($event);
        if (! $rule?->enabled || ! $this->eligible($user, $event)) {
            return null;
        }

        $subject = (string) ($context['history_id'] ?? $context['registration_request_id'] ?? $context['ticket_id'] ?? $context['order_id'] ?? $user->id);
        $key = 'event:'.$rule->key.':user:'.$user->id.':subject:'.$subject;
        $run = AutomationRun::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'rule_id' => $rule->id,
                'event' => $rule->event,
                'user_id' => $user->id,
                'order_id' => $context['order_id'] ?? null,
                'subject_id' => $subject,
                'payload' => $context,
                'status' => 'queued',
                'scheduled_for' => now(),
            ],
        );

        if ($run->wasRecentlyCreated) {
            SendAutomationNotification::dispatch($run->id);
        }

        return $run;
    }

    public function toggle(AutomationRule $rule, bool $enabled, User $admin): AutomationRule
    {
        return DB::transaction(function () use ($rule, $enabled, $admin): AutomationRule {
            $rule->forceFill(['enabled' => $enabled, 'created_by_admin_id' => $rule->created_by_admin_id ?: $admin->id])->save();
            return $rule->refresh();
        });
    }

    public function markSkipped(AutomationRun $run, string $reason): void
    {
        $run->forceFill(['status' => 'skipped', 'last_error' => $reason, 'executed_at' => now()])->save();
    }

    public function eligible(User $user, string $event): bool
    {
        $user->loadMissing('preference');
        if ($user->account_status !== 'active' || ! $user->email_verified_at || ! $user->email) {
            return false;
        }
        if (EmailSuppression::query()->where('email', strtolower($user->email))->exists()) {
            return false;
        }

        return $event !== 'order_status_changed'
            ? (bool) $user->preference?->marketing_notifications
            : (bool) ($user->preference?->order_updates ?? true);
    }

    private function hasReachedLimit(AutomationRule $rule, ?int $userId, Carbon $now): bool
    {
        if (! $userId || $rule->max_per_user < 1) {
            return false;
        }

        $query = AutomationRun::query()
            ->where('rule_id', $rule->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['queued', 'running', 'sent']);
        if ($rule->cooldown_minutes > 0) {
            $query->where('created_at', '>=', $now->copy()->subMinutes($rule->cooldown_minutes));
        } else {
            $query->where('created_at', '>=', $now->copy()->subDays(30));
        }

        return $query->count() >= $rule->max_per_user;
    }

    private function withinWindow(AutomationRule $rule, Carbon $now): bool
    {
        if (! $rule->window_start || ! $rule->window_end) {
            return true;
        }
        $local = $now->copy()->setTimezone($rule->timezone ?: config('automations.timezone'));
        $current = $local->format('H:i:s');
        return $rule->window_start <= $rule->window_end
            ? $current >= $rule->window_start && $current <= $rule->window_end
            : $current >= $rule->window_start || $current <= $rule->window_end;
    }
}
