<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class LogResponseTime
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $end = microtime(true);
        $duration = $end - $start;

        Log::channel('daily')->info('Response time: ' . $duration . ' seconds.');

        return $response;
    }
}
