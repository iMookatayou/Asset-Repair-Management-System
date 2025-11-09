<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    // GET /api/search/assets?q=&limit=10
    public function assets(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $limit = max(1, min((int) $r->query('limit', 10), 50));
        $departmentId = $r->query('department_id');

        $rows = DB::table('assets')
            ->select(['id', 'asset_code', 'name'])
            ->when($departmentId, fn($qq) => $qq->where('department_id', $departmentId))
            ->when($q !== '', function ($qq) use ($q) {
                $like = "%{$q}%";
                $qq->where(function ($w) use ($like) {
                    $w->where('asset_code', 'like', $like)
                      ->orWhere('name', 'like', $like);
                });
            })
            ->orderBy('asset_code')
            ->limit($limit)
            ->get()
            ->map(fn($r) => [
                'id'    => $r->id,
                'code'  => $r->asset_code,
                'name'  => $r->name,
                'label' => trim(($r->asset_code ? ($r->asset_code.' - ') : '').($r->name ?? '')),
            ]);

        return response()->json(['data' => $rows]);
    }

    // GET /api/search/maintenance-requests?q=&limit=10
    public function requests(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $limit = max(1, min((int) $r->query('limit', 10), 50));
        $status = (string) $r->query('status', '');

        $rows = DB::table('maintenance_requests')
            ->select(['id','request_no','title','status'])
            ->when($status !== '', fn($qq) => $qq->where('status', $status))
            ->when($q !== '', function ($qq) use ($q) {
                $like = "%{$q}%";
                $qq->where(function ($w) use ($like) {
                    $w->where('request_no','like',$like)
                      ->orWhere('title','like',$like);
                });
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $rows]);
    }
}
