<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RequestContext
{
    public function handle($request, Closure $next)
    {
        $requestId = (string) ($request->header('X-Request-Id') ?: Str::uuid());
        $request->attributes->set('request_id', $requestId);

        Log::withContext([
            'request_id' => $requestId,
            'route' => optional($request->route())->getName(),
        ]);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
