<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    // GET /api/stats/summary
    public function summary()
    {
        $key = 'stats:summary:v1';
        $payload = Cache::remember($key, 60, function () {
            // Aggregate counts in minimal queries
            $assetTotal = DB::table('assets')->count();
            $openRequests = DB::table('maintenance_requests')->whereIn('status', [
                'pending','accepted','in_progress','on_hold'
            ])->count();
            $closedRequests = DB::table('maintenance_requests')->whereIn('status', [
                'resolved','closed','cancelled'
            ])->count();

            // Requests per priority (map missing to 0)
            $priorityRaw = DB::table('maintenance_requests')
                ->selectRaw('priority, COUNT(*) as c')
                ->groupBy('priority')
                ->pluck('c','priority')
                ->all();
            $priorities = [];
            foreach (['low','medium','high','urgent'] as $p) {
                $priorities[$p] = (int) ($priorityRaw[$p] ?? 0);
            }

            // Recent 7 days new requests
            $recent = DB::table('maintenance_requests')
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->where('created_at','>=', now()->subDays(6)->startOfDay())
                ->groupBy('d')
                ->orderBy('d','asc')
                ->pluck('c','d')
                ->all();
            $recentSeries = [];
            for ($i=6; $i>=0; $i--) {
                $day = now()->subDays($i)->format('Y-m-d');
                $recentSeries[] = ['date' => $day, 'count' => (int)($recent[$day] ?? 0)];
            }

            return [
                'assets_total'    => $assetTotal,
                'requests_open'   => $openRequests,
                'requests_closed' => $closedRequests,
                'priority_counts' => $priorities,
                'recent_daily'    => $recentSeries,
            ];
        });

        return response()->json($payload);
    }

    // GET /api/stats/maintenance/status-counts
    public function maintenanceStatusCounts()
    {
        $raw = Cache::remember('stats:maintenance_status_counts:v1', 60, function () {
            return DB::table('maintenance_requests')
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c','status')
                ->all();
        });
        return response()->json(['data' => $raw]);
    }

    // GET /api/stats/maintenance/technicians
    public function technicianSummary()
    {
        $rows = Cache::remember('stats:technician_summary:v1', 60, function () {
            return DB::table('maintenance_requests')
            ->selectRaw('technician_id as id, 
                SUM(CASE WHEN status IN (\'pending\',\'accepted\',\'in_progress\',\'on_hold\') THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN status IN (\'resolved\',\'closed\',\'cancelled\') THEN 1 ELSE 0 END) as closed_count,
                COUNT(*) as total_count,
                AVG(CASE WHEN resolved_at IS NOT NULL AND started_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, started_at, resolved_at) END) as avg_hours
            ')
            ->whereNotNull('technician_id')
            ->groupBy('technician_id')
            ->orderByDesc('total_count')
            ->limit(50)
            ->get();
        });

        // attach technician names (single query)
        $ids = $rows->pluck('id')->filter()->all();
        $names = $ids ? DB::table('users')->whereIn('id',$ids)->pluck('name','id')->all() : [];

        $mapped = $rows->map(fn($r) => [
            'id'         => $r->id,
            'name'       => $names[$r->id] ?? null,
            'total'      => (int) $r->total_count,
            'open'       => (int) $r->open_count,
            'closed'     => (int) $r->closed_count,
            'avg_hours'  => $r->avg_hours !== null ? round((float) $r->avg_hours, 2) : null,
        ]);

        return response()->json(['data' => $mapped]);
    }

    // GET /api/stats/assets/by-department
    public function assetsByDepartment()
    {
        $rows = Cache::remember('stats:assets_by_department:v1', 60, function () {
            return DB::table('assets')
            ->selectRaw('department_id as id, COUNT(*) as c')
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->orderByDesc('c')
            ->get();
        });
        $ids = $rows->pluck('id')->all();
        $deptNames = $ids ? DB::table('departments')->whereIn('id',$ids)->pluck('name','id')->all() : [];
        $mapped = $rows->map(fn($r) => [
            'id'    => $r->id,
            'name'  => $deptNames[$r->id] ?? null,
            'count' => (int) $r->c,
        ]);
        return response()->json(['data' => $mapped]);
    }
}
