<?php

namespace App\Http\Controllers\Repair;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $req)
    {
        // ===== Column existence guards =====
        $hasReqDate       = Schema::hasColumn('maintenance_requests','request_date');
        $hasCreatedAt     = Schema::hasColumn('maintenance_requests','created_at');
        $hasCompletedDate = Schema::hasColumn('maintenance_requests','completed_date');
        $hasCompletedAt   = Schema::hasColumn('maintenance_requests','completed_at');

        $hasAssets  = Schema::hasTable('assets');
        $hasType    = $hasAssets && Schema::hasColumn('assets','type');
        $hasDeptTbl = Schema::hasTable('departments');
        $hasDeptCol = $hasDeptTbl && Schema::hasColumn('departments', 'name');
        $hasDeptId  = $hasAssets && Schema::hasColumn('assets', 'department_id');

        // ===== Base query =====
        $base = DB::table('maintenance_requests');

        // ===== Filters from query string =====
        $status = (string)$req->query('status', '');
        $from   = $req->query('from');
        $to     = $req->query('to');

        if ($status !== '') {
            $base->where('status', $status);
        }
        if ($from) {
            try {
                $col = $hasReqDate ? 'request_date' : ($hasCreatedAt ? 'created_at' : null);
                if ($col) $base->whereDate($col, '>=', Carbon::parse($from)->toDateString());
            } catch (\Throwable $e) {}
        }
        if ($to) {
            try {
                $col = $hasReqDate ? 'request_date' : ($hasCreatedAt ? 'created_at' : null);
                if ($col) $base->whereDate($col, '<=', Carbon::parse($to)->toDateString());
            } catch (\Throwable $e) {}
        }

        // ===== KPI =====
        $stats = [
            'total'      => (clone $base)->count(),
            'pending'    => (clone $base)->where('status','pending')->count(),
            'inProgress' => (clone $base)->where('status','in_progress')->count(),
            'completed'  => (clone $base)->where('status','completed')->count(),
            'monthCost'  => 0.0, // ยังไม่มีคอลัมน์ค่าใช้จ่ายก็ให้เป็น 0 ไปก่อน
        ];

        // ===== Trend 6 months =====
        if ($hasReqDate || $hasCreatedAt) {
            $trendCol = $hasReqDate ? 'request_date' : 'created_at';
            $monthlyTrend = (clone $base)
                ->where($trendCol, '>=', now()->startOfMonth()->subMonths(5))
                ->selectRaw("DATE_FORMAT($trendCol, '%Y-%m') as ym, COUNT(*) as cnt")
                ->groupBy('ym')->orderBy('ym')
                ->get()
                ->map(fn($r)=> ['ym'=>$r->ym, 'cnt'=>(int)$r->cnt])
                ->take(6)->values();
        } else {
            $monthlyTrend = collect();
        }

        // ===== By Asset Type (Top 8 + others) =====
        $totalReq = $stats['total'];
        if ($hasAssets) {
            $qType = (clone $base)->leftJoin('assets','assets.id','=','maintenance_requests.asset_id');
            $topTypes = $hasType
                ? $qType->selectRaw('COALESCE(NULLIF(assets.type,""),"ไม่ระบุ") as type, COUNT(*) as cnt')
                       ->groupBy('type')->orderByDesc('cnt')->limit(8)->get()
                : collect([(object)['type'=>'ไม่ระบุ','cnt'=>$totalReq]]);
        } else {
            $topTypes = collect([(object)['type'=>'ไม่ระบุ','cnt'=>$totalReq]]);
        }
        $sumTop = (int)$topTypes->sum('cnt');
        $others = max(0, $totalReq - $sumTop);
        if ($others > 0) $topTypes->push((object)['type'=>'อื่นๆ','cnt'=>$others]);

        $byAssetType = $topTypes
            ->map(fn($r)=> ['type'=>(string)$r->type, 'cnt'=>(int)$r->cnt])
            ->take(9)->values();

        // ===== By Department (Top 8) =====
        if ($hasAssets && $hasDeptTbl && $hasDeptCol && $hasDeptId) {
            $byDept = (clone $base)
                ->leftJoin('assets','assets.id','=','maintenance_requests.asset_id')
                ->leftJoin('departments','departments.id','=','assets.department_id')
                ->selectRaw('COALESCE(departments.name,"ไม่ระบุ") as dept, COUNT(*) as cnt')
                ->groupBy('dept')->orderByDesc('cnt')->limit(8)->get()
                ->map(fn($r)=> ['dept'=>(string)$r->dept, 'cnt'=>(int)$r->cnt]);
        } else {
            $byDept = $totalReq > 0 ? collect([['dept'=>'ไม่ระบุ','cnt'=>$totalReq]]) : collect();
        }
        $byDept = collect($byDept)->take(8)->values();

        // ===== Recent 12 (robust for date columns) =====
        $recentQ = (clone $base);
        if     ($hasReqDate)   $recentQ->orderByDesc('request_date');
        elseif ($hasCreatedAt) $recentQ->orderByDesc('created_at');
        $recentQ->limit(12);

        $selects = ['maintenance_requests.*'];
        if ($hasReqDate)       $selects[] = DB::raw('maintenance_requests.request_date   as req_dt');
        if ($hasCreatedAt)     $selects[] = DB::raw('maintenance_requests.created_at     as created_dt');
        if ($hasCompletedDate) $selects[] = DB::raw('maintenance_requests.completed_date as comp_dt');
        if ($hasCompletedAt)   $selects[] = DB::raw('maintenance_requests.completed_at   as completed_dt');

        if ($hasAssets) {
            $recentQ->leftJoin('assets','assets.id','=','maintenance_requests.asset_id');
            $selects[] = 'assets.name as asset_name';
        }
        $hasUsers = Schema::hasTable('users') && Schema::hasColumn('users','name');
        if ($hasUsers) {
            $recentQ->leftJoin('users as r','r.id','=','maintenance_requests.reporter_id')
                    ->leftJoin('users as t','t.id','=','maintenance_requests.technician_id');
            $selects[] = 'r.name as reporter_name';
            $selects[] = 't.name as technician_name';
        }

        $fmt = function ($v) {
            if ($v === null || $v === '') return '-';
            try { return Carbon::parse($v)->format('Y-m-d H:i'); }
            catch (\Throwable $e) { return is_string($v) ? $v : '-'; }
        };

        $recent = $recentQ->get($selects)->map(function ($r) use ($fmt) {
            $reqRaw  = $r->req_dt  ?? $r->created_dt  ?? null;           // request_date -> created_at
            $compRaw = $r->comp_dt ?? $r->completed_dt ?? null;          // completed_date -> completed_at
            return [
                'request_date' => $fmt($reqRaw),
                'asset_id'     => (int)($r->asset_id ?? 0),
                'asset_name'   => (string)($r->asset_name ?? '-'),
                'reporter'     => (string)($r->reporter_name ?? '-'),
                'technician'   => (string)($r->technician_name ?? '-'),
                'status'       => (string)($r->status ?? ''),
                'completed_at' => $fmt($compRaw),
            ];
        });

        return view('repair.dashboard', compact(
            'stats','monthlyTrend','byAssetType','byDept','recent'
        ));
    }
}
