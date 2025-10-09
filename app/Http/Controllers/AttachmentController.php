<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function index()
    {
        return response()->json(
            Attachment::latest('uploaded_at')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => 'required|exists:maintenance_requests,id',
            'file_path'  => 'required|string|max:255', 
            'file_type'  => 'nullable|string|max:50',
        ]);

        $att = Attachment::create($data + ['uploaded_at'=>now()]);
        return response()->json(['message'=>'created','data'=>$att], 201);
    }

    public function show(Attachment $attachment)
    {
        return response()->json($attachment);
    }

    public function destroy(Attachment $attachment)
    {
        $attachment->delete();
        return response()->json(['message'=>'deleted']);
    }
}
