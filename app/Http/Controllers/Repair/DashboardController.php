<?php

namespace App\Http\Controllers\Repair;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceRequest;

class DashboardController extends Controller
{
    // app/Http/Controllers/Repair/DashboardController.php
    public function index(Request $req)
    {
        $q = \App\Models\MaintenanceRequest::query();

        // ฟิลเตอร์เบื้องต้น (ถ้ามี)
        if ($status = $req->string('status')->toString()) $q->where('status', $status);
        if ($from = $req->date('from')) $q->whereDate('request_date', '>=', $from);
        if ($to   = $req->date('to'))   $q->whereDate('request_date', '<=', $to);

        // --- Trend: 6 เดือนล่าสุด ---
        $monthlyTrend = (clone $q)
            ->selectRaw("DATE_FORMAT(request_date, '%Y-%m') as ym, COUNT(*) as cnt")
            ->where('request_date', '>=', now()->startOfMonth()->subMonths(5))
            ->groupBy('ym')->orderBy('ym')
            ->get();

        // --- Type: Top 8 + อื่นๆ (จำกัดที่ DB) ---
        $totalReq = (clone $q)->count();

        $topTypes = (clone $q)
            ->leftJoin('assets','assets.id','=','maintenance_requests.asset_id')
            ->selectRaw('COALESCE(NULLIF(assets.type,""),"ไม่ระบุ") as type, COUNT(*) as cnt')
            ->groupBy('type')->orderByDesc('cnt')->limit(8)->get();

        $sumTop = (int) $topTypes->sum('cnt');
        $othersCnt = max(0, $totalReq - $sumTop);
        $byAssetType = $othersCnt > 0
            ? $topTypes->push((object)['type'=>'อื่นๆ','cnt'=>$othersCnt])
            : $topTypes;

        // --- Dept: Top 8 (ผ่าน assets.department_id) ---
        $byDept = (clone $q)
            ->leftJoin('assets','assets.id','=','maintenance_requests.asset_id')
            ->leftJoin('departments','departments.id','=','assets.department_id')
            ->selectRaw('COALESCE(departments.name,"ไม่ระบุ") as dept, COUNT(*) as cnt')
            ->groupBy('dept')->orderByDesc('cnt')->limit(8)->get();

        // กัน JSON โต: ตัดให้แน่ใจอีกชั้น
        $monthlyTrend = $monthlyTrend->take(6)->values();
        $byAssetType  = $byAssetType->take(9)->values();  // top8 + อื่นๆ
        $byDept       = $byDept->take(8)->values();

        return view('repair.dashboard-graphs', compact('monthlyTrend','byAssetType','byDept'));
    }
}