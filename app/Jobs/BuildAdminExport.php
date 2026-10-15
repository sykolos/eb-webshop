<?php

namespace App\Jobs;

use App\Models\AdminExport;
use App\Services\AdminExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildAdminExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(public int $exportId)
    {
    }

    public function handle(AdminExportService $exports): void
    {
        $exports->build(AdminExport::findOrFail($this->exportId));
    }

    public function failed(?\Throwable $exception): void
    {
        AdminExport::whereKey($this->exportId)->update([
            'status' => 'failed',
            'error' => $exception?->getMessage() ?: 'Az export háttérfeldolgozása sikertelen.',
            'completed_at' => now(),
        ]);
    }
}
