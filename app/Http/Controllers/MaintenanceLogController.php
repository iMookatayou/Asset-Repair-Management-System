<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use Illuminate\Http\Request;

class MaintenanceLogController extends Controller
{
    public function index()
    {
        return response()->json(
            MaintenanceLog::with('user')->latest('created_at')->paginate(50)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => 'required|exists:maintenance_requests,id',
            'action'     => 'required|string|max:100',
            'note'       => 'nullable|string',
        ]);

        $log = MaintenanceLog::create($data + [
            'user_id'    => $request->user()->id ?? null,
            'created_at' => now(),
        ]);

        return response()->json(['message'=>'created','data'=>$log], 201);
    }

    public function show(MaintenanceLog $maintenanceLog)
    {
        return response()->json($maintenanceLog->load('user'));
    }
}
