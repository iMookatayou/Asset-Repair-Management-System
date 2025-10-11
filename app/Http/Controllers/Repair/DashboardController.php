<?php

namespace App\Http\Controllers\Repair;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now('Asia/Bangkok');

        // ===== Summary =====
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $stats = [
            'total'      => MaintenanceRequest::count(),
            'pending'    => MaintenanceRequest::where('status', MaintenanceRequest::STATUS_PENDING)->count(),
            'inProgress' => MaintenanceRequest::where('status', MaintenanceRequest::STATUS_IN_PROGRESS)->count(),
            'completed'  => MaintenanceRequest::where('status', MaintenanceRequest::STATUS_COMPLETED)->count(),
            'monthCost'  => (float) (MaintenanceRequest::whereBetween('request_date', [$monthStart, $monthEnd])->sum('cost') ?? 0),
        ];

        // ===== Trend 12 เดือนล่าสุด =====
        $start12 = $now->copy()->startOfMonth()->subMonths(11);

        $rawTrend = MaintenanceRequest::selectRaw("
                DATE_FORMAT(request_date, '%Y-%m') as ym,
                COUNT(*) as cnt
            ")
            ->whereNotNull('request_date')
            ->whereBetween('request_date', [$start12, $monthEnd])
            ->groupByRaw("DATE_FORMAT(request_date, '%Y-%m')")
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $monthlyTrend = [];
        $cursor = $start12->copy();
        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-m');
            $monthlyTrend[] = [
                'ym'  => $key,
                'cnt' => (int) ($rawTrend[$key]->cnt ?? 0),
            ];
            $cursor->addMonth();
        }

        // ===== Pie: by asset category (subquery กัน ONLY_FULL_GROUP_BY) =====
        $innerAssetType = DB::table('maintenance_requests as mr')
            ->leftJoin('assets as a', 'a.id', '=', 'mr.asset_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(a.category), ''), ?) as type", ['ไม่ระบุ']);

        $byAssetType = DB::query()
            ->fromSub($innerAssetType, 't')
            ->selectRaw('t.type, COUNT(*) as cnt')
            ->groupBy('t.type')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn($r) => ['type' => (string) $r->type, 'cnt' => (int) $r->cnt])
            ->values()
            ->all();

        // ===== Bar: by department =====
        $innerDept = DB::table('maintenance_requests as mr')
            ->leftJoin('assets as a', 'a.id', '=', 'mr.asset_id')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(d.name), ''), ?) as dept", ['ไม่ระบุแผนก']);

        $byDept = DB::query()
            ->fromSub($innerDept, 't')
            ->selectRaw('t.dept, COUNT(*) as cnt')
            ->groupBy('t.dept')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn($r) => ['dept' => (string) $r->dept, 'cnt' => (int) $r->cnt])
            ->values()
            ->all();

        // ===== Recent: ส่งเป็น stdClass เบา ๆ ที่มี field ตรงกับ Blade =====
        // เลือกเฉพาะคอลัมน์ แล้วดึงชื่อความสัมพันธ์ทีละชุด (ลดเมม)
        $recentRows = MaintenanceRequest::query()
            ->select([
                'id','asset_id','reporter_id','technician_id',
                'title','status','priority','request_date','completed_date'
            ])
            ->whereNotNull('request_date')
            ->latest('request_date')
            ->limit(10)
            ->get();

        // preload names เพื่อเลี่ยง Eloquent relation objects
        $assetNames = DB::table('assets')
            ->whereIn('id', $recentRows->pluck('asset_id')->filter()->unique())
            ->pluck('name', 'id'); // [id => name]

        $userIds = $recentRows->pluck('reporter_id')->merge($recentRows->pluck('technician_id'))->filter()->unique();
        $userNames = DB::table('users')
            ->whereIn('id', $userIds)
            ->pluck('name', 'id'); // [id => name]

        $recent = $recentRows->map(function ($m) use ($assetNames, $userNames) {
            // ทำให้ property ตรงกับ Blade:
            // - $t->request_date (Carbon|null)
            // - $t->asset?->name
            // - $t->reporter?->name
            // - $t->status
            // - $t->technician?->name
            // - $t->completed_date (Carbon|null)
            $assetObj     = $m->asset_id     ? (object)['name' => ($assetNames[$m->asset_id]     ?? null)] : null;
            $reporterObj  = $m->reporter_id  ? (object)['name' => ($userNames[$m->reporter_id]   ?? null)] : null;
            $technicianObj= $m->technician_id? (object)['name' => ($userNames[$m->technician_id] ?? null)] : null;

            return (object)[
                'asset_id'      => $m->asset_id,
                'request_date'  => $m->request_date,   // Carbon หรือ null
                'asset'         => $assetObj,          // stdClass หรือ null
                'reporter'      => $reporterObj,       // stdClass หรือ null
                'status'        => $m->status,
                'technician'    => $technicianObj,     // stdClass หรือ null
                'completed_date'=> $m->completed_date, // Carbon หรือ null
            ];
        })->all(); // array ของ stdClass เบา ๆ

        return view('repair.dashboard', [
            'stats'        => $stats,
            'monthlyTrend' => $monthlyTrend, // array for JS
            'byAssetType'  => $byAssetType,  // array for JS
            'byDept'       => $byDept,       // array for JS
            'recent'       => $recent,       // stdClass (ไม่ใช่ Eloquent)
        ]);
    }
}
