<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CorrelationId
{
    public function handle(Request $request, Closure $next)
    {
        $incoming = $request->headers->get('X-Correlation-ID');
        $cid = $incoming ?: Str::uuid()->toString();
        $request->attributes->set('correlation_id', $cid);

        $response = $next($request);
        if (method_exists($response, 'header')) {
            $response->headers->set('X-Correlation-ID', $cid);
        }
        return $response;
    }
}
