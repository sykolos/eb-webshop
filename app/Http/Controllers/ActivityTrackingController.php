<?php

namespace App\Http\Controllers;

use App\Services\ActivityTrackingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class ActivityTrackingController extends Controller
{
    public function store(Request $request, ActivityTrackingService $tracking)
    {
        $data = $request->validate([
            'event_uuid' => ['required', 'uuid'],
            'event_name' => ['required', Rule::in(ActivityTrackingService::EVENTS)],
            'page_path' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'campaign_source' => ['nullable', 'string', 'max:191'],
            'campaign_medium' => ['nullable', 'string', 'max:191'],
            'campaign_name' => ['nullable', 'string', 'max:191'],
            'device_type' => ['nullable', Rule::in(['desktop', 'tablet', 'mobile'])],
            'payload' => ['nullable', 'array', 'max:12'],
        ]);

        $tracking->record($request, $data);
        return response()->noContent();
    }
}
