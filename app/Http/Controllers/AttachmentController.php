<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    /**
     * รองรับสองกรณี:
     * 1) GET /attachments?request_id=123   -> เฉพาะของใบงานนั้น
     * 2) GET /attachments                   -> ทั้งหมด (paginate)
     */
    public function index(Request $request)
    {
        $requestId = $request->integer('request_id');

        $q = Attachment::query()
            ->when($requestId, fn($qq) => $qq->where('request_id', $requestId))
            ->latest('uploaded_at');

        return response()->json($q->paginate(20));
    }

    /**
     * สำหรับ route แบบ nested: GET /repair-requests/{req}/attachments
     * ใช้ได้ทันทีถ้าพี่เพิ่ม route นี้ใน api.php:
     * Route::get('/repair-requests/{req}/attachments', [AttachmentController::class, 'indexByRequest']);
     */
    public function indexByRequest(MaintenanceRequest $req)
    {
        return response()->json(
            $req->attachments()->latest('uploaded_at')->paginate(20)
        );
    }

    /** POST /attachments */
    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => 'required|exists:maintenance_requests,id',
            'file_path'  => 'required|string|max:255', // เก็บ path ตามสคีมเดิม
            'file_type'  => 'nullable|string|max:50',
        ]);

        $att = Attachment::create($data + ['uploaded_at' => now()]);

        return response()->json(['message' => 'created', 'data' => $att], 201);
    }

    /** GET /attachments/{attachment} */
    public function show(Attachment $attachment)
    {
        return response()->json($attachment);
    }

    /** DELETE /attachments/{attachment} */
    public function destroy(Attachment $attachment)
    {
        // ไม่ลบไฟล์จริง เพราะสคีมพี่เก็บเป็น file_path เฉย ๆ
        $attachment->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function storeForRequest(Request $request, MaintenanceRequest $req)
    {
        $validated = $request->validate([
            'type' => 'required|in:before,after,other',
            'file' => 'required|file|max:10240', // 10 MB
        ]);

        // เก็บไฟล์จริงลง storage แบบ public
        // $path จะเป็นเช่น: "maintenance/abc123.jpg"
        $path = $request->file('file')->store('maintenance', 'public');

        // บันทึกลงตาราง Attachment ตามสคีมเดิมของคุณ
        $att = Attachment::create([
            'request_id'  => $req->id,
            'file_path'   => $path,                 // <<== map ไป field เดิม
            'file_type'   => $validated['type'],    // <<== map ไป field เดิม
            'uploaded_at' => now(),
        ]);

        // กลับไปหน้าเดิม + flash message ให้ Chip สีเขียวขึ้น
        return back()->with('ok', 'อัปโหลดไฟล์เรียบร้อย');
    }
}
