<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Services\EmailCampaignService;
use Illuminate\Console\Command;

class DispatchScheduledEmails extends Command
{
    protected $signature = 'email:dispatch-scheduled';
    protected $description = 'A lejárt emailkampányok queue-ba helyezése';

    public function handle(EmailCampaignService $emails): int
    {
        $count = 0;
        EmailCampaign::where('status', 'scheduled')->where('scheduled_at', '<=', now())->each(function (EmailCampaign $campaign) use ($emails, &$count): void {
            $emails->queueCampaign($campaign);
            $count++;
        });
        $this->info("{$count} kampány feldolgozása elindult.");
        return self::SUCCESS;
    }
}
