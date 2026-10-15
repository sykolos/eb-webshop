<?php

namespace App\Jobs;

use App\Mail\AutomationNotificationMail;
use App\Models\AutomationRun;
use App\Services\AutomationRuleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendAutomationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];

    public function __construct(public int $runId)
    {
        $this->afterCommit();
    }

    public function handle(AutomationRuleService $automations): void
    {
        $run = AutomationRun::with(['rule', 'user.preference', 'order'])->findOrFail($this->runId);
        if (! $run->rule?->enabled || $run->status === 'sent') {
            return;
        }
        if (! $run->user || ! $automations->eligible($run->user, $run->event)) {
            $automations->markSkipped($run, 'A címzett jogosultsága vagy preferenciája megváltozott.');
            return;
        }

        $run->increment('attempts');
        $run->update(['status' => 'running']);
        try {
            Mail::to($run->user->email)->send(new AutomationNotificationMail($run));
            $run->update(['status' => 'sent', 'executed_at' => now(), 'last_error' => null]);
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }
    }
}
