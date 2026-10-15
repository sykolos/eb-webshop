<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Models\EmailDelivery;
use App\Models\EmailTemplate;
use App\Services\EmailCampaignService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EmailAdminController extends Controller
{
    public function index(EmailCampaignService $emails)
    {
        return view('admin.m16.emails.index', [
            'campaigns' => EmailCampaign::latest()->limit(20)->get(),
            'templates' => EmailTemplate::latest()->get(),
            'recentDeliveries' => EmailDelivery::with('campaign')->latest()->limit(20)->get(),
            'audienceCount' => $emails->audience()->count(),
            'marketingCount' => $emails->audience('marketing')->count(),
        ]);
    }

    public function campaign(Request $request, EmailCampaignService $emails)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'subject' => ['required', 'string', 'max:191'],
            'html_body' => ['required', 'string', 'max:200000'],
            'text_body' => ['nullable', 'string', 'max:200000'],
            'segment' => ['required', Rule::in(['newsletter', 'marketing'])],
            'scheduled_at' => ['nullable', 'date'],
            'timezone' => ['required', 'timezone'],
        ]);
        $campaign = $emails->createCampaign($data, $request->user());
        return back()->with('success', 'A kampány elmentve #'.$campaign->id.'. A címzettszám a küldés előtt újraellenőrződik.');
    }

    public function test(Request $request, EmailCampaignService $emails)
    {
        $data = $request->validate(['campaign_id' => ['required', 'exists:email_campaigns,id'], 'email' => ['required', 'email', 'max:191']]);
        $emails->sendTest(EmailCampaign::findOrFail($data['campaign_id']), $data['email']);
        return back()->with('success', 'A tesztlevél queue-ba került. Stagingen a meglévő sink címre lesz átirányítva.');
    }

    public function send(EmailCampaign $campaign, EmailCampaignService $emails)
    {
        $count = $emails->queueCampaign($campaign);
        return back()->with('success', 'A kampány '.$count.' címzettel queue-ba került.');
    }

    public function cancel(EmailCampaign $campaign)
    {
        if (in_array($campaign->status, ['draft', 'scheduled', 'processing'], true)) {
            $campaign->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }
        return back()->with('success', 'A kampány megszakítva; a már elküldött leveleket ez nem vonja vissza.');
    }
}
