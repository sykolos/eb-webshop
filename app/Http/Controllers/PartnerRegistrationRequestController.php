<?php

namespace App\Http\Controllers;

use App\Models\PartnerRegistrationRequest;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class PartnerRegistrationRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = PartnerRegistrationRequest::with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.partner-requests.index', compact('requests'));
    }

    public function update(Request $request, PartnerRegistrationRequest $registrationRequest, AuditLogger $audit)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,blocked'],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $registrationRequest->forceFill([
            'status' => $data['status'],
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        if ($registrationRequest->user) {
            $registrationRequest->user->forceFill([
                'account_status' => match ($data['status']) {
                    'approved' => 'active',
                    'rejected' => 'rejected',
                    'blocked' => 'blocked',
                    default => 'pending',
                },
            ])->save();
        }

        $audit->record('partner_registration.updated', $registrationRequest, [
            'status' => $data['status'],
            'user_id' => $registrationRequest->user_id,
        ]);

        return back()->with('success', 'A partnerkérelem állapota frissítve.');
    }
}
