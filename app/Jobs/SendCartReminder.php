<?php

namespace App\Jobs;

use App\Mail\CartReminderMail;
use App\Models\AutomationRun;
use App\Models\EmailSuppression;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCartReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];

    public function __construct(public int $runId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $run = AutomationRun::with(['rule', 'cart.items', 'user.preference'])->findOrFail($this->runId);
        if (! $run->rule?->enabled || ! in_array($run->status, ['queued', 'failed'], true)) {
            return;
        }

        $cart = $run->cart;
        $user = $run->user;
        $activity = data_get($run->payload, 'activity_at');
        $allowed = $cart && $user && $cart->converted_at === null && $cart->items->isNotEmpty()
            && $cart->last_activity_at?->toIso8601String() === $activity
            && $user->account_status === 'active' && $user->email_verified_at
            && $user->preference?->marketing_notifications
            && ! EmailSuppression::query()->where('email', strtolower($user->email))->exists();
        if (! $allowed) {
            app(\App\Services\AutomationRuleService::class)->markSkipped($run, 'A kosár, aktivitás vagy címzett jogosultsága megváltozott.');
            return;
        }

        $run->increment('attempts');
        $run->update(['status' => 'running']);
        try {
            Mail::to($user->email)->send(new CartReminderMail($cart, $user));
            $run->update(['status' => 'sent', 'executed_at' => now(), 'last_error' => null]);
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }
    }
}
