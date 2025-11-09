<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;
use Throwable;

class HealthController extends Controller
{
    public function index(Request $request)
    {
        $checks = [];
        $overallOk = true;

        // DB check
        $start = microtime(true);
        try {
            DB::select('select 1');
            $checks['db'] = [
                'status' => 'ok',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (Throwable $e) {
            $overallOk = false;
            $checks['db'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        // Redis check (graceful when phpredis extension is not installed)
        try {
            if (class_exists('Redis')) {
                $pong = Redis::connection()->ping();
                $checks['redis'] = [
                    'status' => strtolower((string) $pong) === 'pong' ? 'ok' : 'unexpected',
                    'reply' => is_string($pong) ? $pong : (string) $pong,
                ];
                if (strtolower((string) $pong) !== 'pong') {
                    $overallOk = false;
                }
            } else {
                $checks['redis'] = [
                    'status' => 'skipped',
                    'reason' => 'phpredis extension not installed',
                ];
            }
        } catch (Throwable $e) {
            $overallOk = false;
            $checks['redis'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        // Queue check (driver + connectivity)
        try {
            $driver = config('queue.default');
            // Attempt to resolve connection (throws if misconfigured)
            Queue::connection($driver);
            $checks['queue'] = [
                'status' => 'ok',
                'driver' => $driver,
            ];
        } catch (Throwable $e) {
            $overallOk = false;
            $checks['queue'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        $status = $overallOk ? 'ok' : 'degraded';
        $httpStatus = $overallOk ? 200 : 503;

        return response()->json([
            'status' => $status,
            'checks' => $checks,
            'meta' => [
                'app' => config('app.name'),
                'env' => config('app.env'),
                'time' => now()->toIso8601String(),
                'version' => config('app.version') ?? null,
            ],
        ], $httpStatus);
    }
}
