<?php

namespace App\Console\Commands;

use App\Models\EmailDelivery;
use Illuminate\Console\Command;

class CleanupEmailDeliveries extends Command
{
    protected $signature = 'email:cleanup';
    protected $description = 'Lejárt email kézbesítési naplók törlése a megőrzési szabály szerint';

    public function handle(): int
    {
        $deleted = EmailDelivery::where('created_at', '<', now()->subMonths((int) config('email_campaigns.retention_months', 12)))->delete();
        $this->info("{$deleted} kézbesítési rekord törölve.");
        return self::SUCCESS;
    }
}
