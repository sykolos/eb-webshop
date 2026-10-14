<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminTwoFactorController extends Controller
{
    public function challenge(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        if (!$request->user()->hasTwoFactorEnabled()) {
            return redirect()->route('adminpanel');
        }

        return view('admin.pages.security.two-factor', [
            'challenge' => true,
            'setup' => false,
            'recoveryCodes' => [],
        ]);
    }

    public function verify(Request $request, TotpService $totp, AuditLogger $audit)
    {
        $request->validate(['code' => ['required', 'string', 'min:6', 'max:20']]);
        $user = $request->user();
        abort_unless($user?->is_admin && $user->hasTwoFactorEnabled(), 403);

        $validTotp = $totp->verify($user->two_factor_secret, $request->string('code')->toString());
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $normalized = strtoupper(trim($request->string('code')->toString()));
        $recoveryIndex = array_search($normalized, $recoveryCodes, true);

        if (!$validTotp && $recoveryIndex === false) {
            return back()->withErrors(['code' => 'A biztonsági kód érvénytelen vagy lejárt.']);
        }

        if ($recoveryIndex !== false) {
            unset($recoveryCodes[$recoveryIndex]);
            $user->two_factor_recovery_codes = array_values($recoveryCodes);
            $user->save();
        }

        $request->session()->put(config('two_factor.verified_session_key'), $user->id);
        $request->session()->forget(config('two_factor.pending_session_key'));
        $audit->record('admin.2fa.verified', $user);

        return redirect()->intended(route('adminpanel'));
    }

    public function setup(Request $request, TotpService $totp)
    {
        $user = $request->user();
        abort_unless($user?->is_admin, 403);

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $totp->generateSecret();
            $user->two_factor_recovery_codes = $totp->recoveryCodes();
            $user->save();
        }

        return view('admin.pages.security.two-factor', [
            'challenge' => false,
            'setup' => true,
            'secret' => $user->two_factor_secret,
            'provisioningUri' => $totp->provisioningUri($user->two_factor_secret, $user->email),
            'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
        ]);
    }

    public function confirm(Request $request, TotpService $totp, AuditLogger $audit)
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();
        abort_unless($user?->is_admin && $user->two_factor_secret, 403);

        if (!$totp->verify($user->two_factor_secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'A hitelesítő alkalmazás kódja érvénytelen.']);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();
        $request->session()->put(config('two_factor.verified_session_key'), $user->id);
        $audit->record('admin.2fa.enabled', $user);

        return redirect()->route('adminpanel')->with('success', 'A kétlépcsős belépés bekapcsolva.');
    }

    public function disable(Request $request, AuditLogger $audit)
    {
        $validated = $request->validate(['password' => ['required', 'string']]);
        $user = $request->user();
        abort_unless($user?->is_admin && $user->hasTwoFactorEnabled(), 403);

        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'A jelszó nem egyezik.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $request->session()->forget(config('two_factor.verified_session_key'));
        $audit->record('admin.2fa.disabled', $user);

        return redirect()->route('adminpanel')->with('success', 'A kétlépcsős belépés kikapcsolva.');
    }
}
