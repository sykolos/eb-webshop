<?php

namespace App\Console\Commands;

use App\Models\SchedulerHeartbeat as SchedulerHeartbeatModel;
use Illuminate\Console\Command;

class SchedulerHeartbeat extends Command
{
    protected $signature = 'system:heartbeat';

    protected $description = 'Rögzíti, hogy a Laravel scheduler életben van.';

    public function handle(): int
    {
        SchedulerHeartbeatModel::updateOrCreate(
            [
                'name' => 'laravel-scheduler',
                'environment' => app()->environment(),
            ],
            [
                'ran_at' => now(),
                'meta' => [
                    'php' => PHP_VERSION,
                    'laravel' => app()->version(),
                ],
            ],
        );

        $this->info('Scheduler heartbeat recorded.');

        return self::SUCCESS;
    }
}
