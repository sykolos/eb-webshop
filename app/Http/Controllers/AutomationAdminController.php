<?php

namespace App\Http\Controllers;

use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Services\AutomationRuleService;
use Illuminate\Http\Request;

final class AutomationAdminController extends Controller
{
    public function index()
    {
        return view('admin.m17.automations.index', [
            'rules' => AutomationRule::query()->orderBy('id')->get(),
            'recentRuns' => AutomationRun::with(['rule', 'user', 'order'])->latest()->limit(40)->get(),
        ]);
    }

    public function update(Request $request, AutomationRule $rule, AutomationRuleService $automations)
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'delay_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'max_per_user' => ['nullable', 'integer', 'min:1', 'max:100'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:0', 'max:525600'],
            'window_start' => ['nullable', 'date_format:H:i'],
            'window_end' => ['nullable', 'date_format:H:i'],
            'timezone' => ['nullable', 'timezone'],
        ]);
        $rule->forceFill([
            'enabled' => $request->boolean('enabled'),
            'delay_minutes' => $data['delay_minutes'] ?? $rule->delay_minutes,
            'max_per_user' => $data['max_per_user'] ?? $rule->max_per_user,
            'cooldown_minutes' => $data['cooldown_minutes'] ?? $rule->cooldown_minutes,
            'window_start' => $data['window_start'] ?? null,
            'window_end' => $data['window_end'] ?? null,
            'timezone' => $data['timezone'] ?? $rule->timezone,
        ])->save();
        $automations->toggle($rule->fresh(), $request->boolean('enabled'), $request->user());
        return back()->with('success', 'Az automatizmus szabálya frissítve.');
    }

    public function run(AutomationRule $rule, AutomationRuleService $automations)
    {
        abort_unless($rule->key === 'abandoned_cart', 422, 'Ezt az automatizmust jelenleg csak a scheduler indíthatja.');
        $count = $automations->dispatchDueCarts();
        return back()->with('success', "{$count} kosáremlékeztető feldolgozása elindult.");
    }
}
