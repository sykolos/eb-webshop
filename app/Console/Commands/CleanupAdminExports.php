<?php

namespace App\Console\Commands;

use App\Services\AdminExportService;
use Illuminate\Console\Command;

class CleanupAdminExports extends Command
{
    protected $signature = 'admin:exports-cleanup';
    protected $description = 'Lejáró admin exportfájlok eltávolítása.';

    public function handle(AdminExportService $exports): int
    {
        $this->info('Eltávolított exportok: '.$exports->cleanupExpired());
        return self::SUCCESS;
    }
}
