<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    /**
     * GET /assets
     * query:
     *  - q: คำค้น (asset_code | name | serial_number)
     *  - status: active|in_repair|disposed
     *  - category: string (optional)
     *  - per_page: จำนวนต่อหน้า (default 20)
     *  - sort_by: id|asset_code|name|purchase_date|warranty_expire|status (default id)
     *  - sort_dir: asc|desc (default desc)
     */
    public function index(Request $request)
    {
        $q         = $request->string('q')->toString();
        $status    = $request->string('status')->toString();
        $category  = $request->string('category')->toString();
        $perPage   = $request->integer('per_page', 20);
        $sortBy    = $request->string('sort_by', 'id')->toString();
        $sortDir   = $request->string('sort_dir', 'desc')->toString();

        // ควบคุมคีย์ที่อนุญาตให้ sort
        $allowedSort = ['id','asset_code','name','purchase_date','warranty_expire','status'];
        if (!in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'id';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $assets = Asset::query()
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('asset_code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%")
                      ->orWhere('serial_number', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($s) => $s->where('status', $status))
            ->when($category, fn($s) => $s->where('category', $category))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        return response()->json($assets);
    }

    /**
     * POST /assets
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_code'      => ['required','string','max:100','unique:assets,asset_code'],
            'name'            => ['required','string','max:255'],
            'category'        => ['nullable','string','max:100'],
            'brand'           => ['nullable','string','max:100'],
            'model'           => ['nullable','string','max:100'],
            'serial_number'   => ['nullable','string','max:100','unique:assets,serial_number'],
            'location'        => ['nullable','string','max:255'],
            'purchase_date'   => ['nullable','date'],
            'warranty_expire' => ['nullable','date'],
            'status'          => ['nullable', Rule::in(['active','in_repair','disposed'])],
        ]);

        $asset = Asset::create($data);

        return response()->json([
            'message' => 'created',
            'data'    => $asset,
        ], 201);
    }

    /**
     * GET /assets/{asset}
     */
    public function show(Asset $asset)
    {
        return response()->json($asset);
    }

    /**
     * PUT /assets/{asset}
     */
    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'asset_code'      => ['sometimes','string','max:100','unique:assets,asset_code,'.$asset->id],
            'name'            => ['sometimes','string','max:255'],
            'category'        => ['nullable','string','max:100'],
            'brand'           => ['nullable','string','max:100'],
            'model'           => ['nullable','string','max:100'],
            'serial_number'   => ['nullable','string','max:100','unique:assets,serial_number,'.$asset->id],
            'location'        => ['nullable','string','max:255'],
            'purchase_date'   => ['nullable','date'],
            'warranty_expire' => ['nullable','date'],
            'status'          => ['nullable', Rule::in(['active','in_repair','disposed'])],
        ]);

        $asset->update($data);

        return response()->json([
            'message' => 'updated',
            'data'    => $asset,
        ]);
    }

    /**
     * DELETE /assets/{asset}
     * (ยังไม่มี route เปิดใช้ก็เก็บไว้เผื่ออนาคต)
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();

        return response()->json(['message' => 'deleted']);
    }
}
