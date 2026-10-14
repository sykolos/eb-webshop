<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->hasPermission('admin.access')) {
            abort(403);
        }

        $user = Auth::user();
        $verifiedKey = config('two_factor.verified_session_key');
        if ($user->hasTwoFactorEnabled() && $request->session()->get($verifiedKey) !== $user->id) {
            return redirect()->route('admin.2fa.challenge');
        }

        return $next($request);
    }
}
