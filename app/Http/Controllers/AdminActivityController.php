<?php

namespace App\Http\Controllers;

use App\Services\ActivityTrackingService;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

final class AdminActivityController extends Controller
{
    public function carts(Request $request, AdminDashboardService $dashboard)
    {
        $carts = $dashboard->carts($request);
        return view('admin.m15.carts', ['carts' => $carts, 'rows' => $dashboard->cartRows($carts)]);
    }

    public function activity(Request $request, AdminDashboardService $dashboard, ActivityTrackingService $tracking)
    {
        return view('admin.m15.activity', [
            'events' => $dashboard->activity($request),
            'eventNames' => ActivityTrackingService::EVENTS,
            'tracking' => $tracking,
        ]);
    }
}
