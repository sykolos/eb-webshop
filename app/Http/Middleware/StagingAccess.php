<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StagingAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('staging.enabled')) {
            return $next($request);
        }

        $headers = ['X-Robots-Tag' => 'noindex, nofollow, noarchive', 'Cache-Control' => 'no-store'];
        $username = (string) config('staging.username');
        $hash = (string) config('staging.password_hash');

        if ($username === '' || $hash === '') {
            return response('Staging access is not configured.', 503, $headers);
        }

        if (!hash_equals($username, (string) $request->getUser())
            || !password_verify((string) $request->getPassword(), $hash)) {
            return response('Staging authentication required.', 401, $headers + [
                'WWW-Authenticate' => 'Basic realm="Electrobusiness staging", charset="UTF-8"',
            ]);
        }

        $response = $next($request);
        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }
        $revision = (string) config('staging.revision');
        if (preg_match('/^[a-f0-9]{40}$/D', $revision)) {
            $response->headers->set('X-Release-Sha', $revision);
        }

        return $response;
    }
}
