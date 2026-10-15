<?php

namespace App\Console\Commands;

use App\Models\AutomationRun;
use Illuminate\Console\Command;

class CleanupAutomationRuns extends Command
{
    protected $signature = 'automations:cleanup';
    protected $description = 'Lejárt automatizmus-futások törlése a megőrzési szabály szerint';

    public function handle(): int
    {
        $deleted = AutomationRun::where('created_at', '<', now()->subMonths((int) config('automations.retention_months', 12)))->delete();
        $this->info("{$deleted} automatizmus-futás törölve.");
        return self::SUCCESS;
    }
}
