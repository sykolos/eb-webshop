<?php

namespace App\Console\Commands;

use App\Services\AutomationRuleService;
use Illuminate\Console\Command;

class DispatchAutomations extends Command
{
    protected $signature = 'automations:dispatch';
    protected $description = 'Esedékes automatizmusok előkészítése és queue-ba helyezése';

    public function handle(AutomationRuleService $automations): int
    {
        $count = $automations->dispatchDueCarts();
        $this->info("{$count} kosáremlékeztető queue-ba helyezve.");
        return self::SUCCESS;
    }
}
