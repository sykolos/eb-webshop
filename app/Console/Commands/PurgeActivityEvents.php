<?php

namespace App\Console\Commands;

use App\Services\ActivityTrackingService;
use Illuminate\Console\Command;

class PurgeActivityEvents extends Command
{
    protected $signature = 'activity:purge';
    protected $description = 'Lejárt aktivitási események törlése a megőrzési szabály szerint';

    public function handle(ActivityTrackingService $tracking): int
    {
        $this->info('Törölt események: '.$tracking->purgeExpired());
        return self::SUCCESS;
    }
}
