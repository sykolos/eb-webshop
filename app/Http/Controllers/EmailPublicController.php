<?php

namespace App\Http\Controllers;

use App\Models\EmailDelivery;
use App\Services\EmailCampaignService;

final class EmailPublicController extends Controller
{
    public function unsubscribe(EmailDelivery $delivery, EmailCampaignService $emails)
    {
        abort_unless($delivery->type === 'marketing', 404);
        $emails->unsubscribe($delivery);
        return response()->view('pages.email-unsubscribed');
    }
}
