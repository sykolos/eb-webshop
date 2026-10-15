<?php

namespace App\Console\Commands;

use App\Services\TicketService;
use Illuminate\Console\Command;

class CleanupTickets extends Command
{
    protected $signature = 'tickets:cleanup';

    protected $description = 'Lejárt, lezárt ticketek és privát csatolmányaik törlése a megőrzési szabály szerint';

    public function handle(TicketService $tickets): int
    {
        $deleted = $tickets->purgeExpired();
        $this->info("{$deleted} lejárt ticket törölve.");

        return self::SUCCESS;
    }
}
