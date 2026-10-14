<?php

namespace App\Console\Commands;

use App\Jobs\QueueSmokeJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class QueueSmokeTest extends Command
{
    protected $signature = 'system:queue-smoke';

    protected $description = 'Ellenőrzi a queue dispatch és worker útvonalát.';

    public function handle(): int
    {
        $token = (string) Str::uuid();
        QueueSmokeJob::dispatch($token);

        $this->line('Queue smoke token: '.$token);
        $this->line('Worker ellenőrzéshez futtasd: php artisan queue:work --once');

        return self::SUCCESS;
    }
}
