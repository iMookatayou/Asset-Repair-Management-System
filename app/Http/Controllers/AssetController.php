<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $q = request('q');
        $status = request('status');

        $assets = Asset::query()
            ->when($q, fn($s) => $s->where(function($w) use ($q) {
                $w->where('asset_code', 'like', "%$q%")
                  ->orWhere('name', 'like', "%$q%")
                  ->orWhere('serial_number', 'like', "%$q%");
            }))
            ->when($status, fn($s) => $s->where('status', $status))
            ->latest('id')
            ->paginate(20);

        return response()->json($assets);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_code'      => 'required|string|max:100|unique:assets,asset_code',
            'name'            => 'required|string|max:255',
            'category'        => 'nullable|string|max:100',
            'brand'           => 'nullable|string|max:100',
            'model'           => 'nullable|string|max:100',
            'serial_number'   => 'nullable|string|max:100|unique:assets,serial_number',
            'location'        => 'nullable|string|max:255',
            'purchase_date'   => 'nullable|date',
            'warranty_expire' => 'nullable|date',
            'status'          => 'in:active,in_repair,disposed',
        ]);

        $asset = Asset::create($data);
        return response()->json(['message'=>'created','data'=>$asset], 201);
    }

    public function show(Asset $asset)
    {
        return response()->json($asset);
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'asset_code'      => 'sometimes|string|max:100|unique:assets,asset_code,'.$asset->id,
            'name'            => 'sometimes|string|max:255',
            'category'        => 'nullable|string|max:100',
            'brand'           => 'nullable|string|max:100',
            'model'           => 'nullable|string|max:100',
            'serial_number'   => 'nullable|string|max:100|unique:assets,serial_number,'.$asset->id,
            'location'        => 'nullable|string|max:255',
            'purchase_date'   => 'nullable|date',
            'warranty_expire' => 'nullable|date',
            'status'          => 'in:active,in_repair,disposed',
        ]);

        $asset->update($data);
        return response()->json(['message'=>'updated','data'=>$asset]);
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return response()->json(['message'=>'deleted']);
    }
}
