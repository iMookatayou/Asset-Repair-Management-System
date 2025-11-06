<?php
// app/Http/Controllers/ChatController.php

namespace App\Http\Controllers;

use App\Models\ChatThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * แสดงหน้ารายการกระทู้ + ค้นหา
     */
    public function index(Request $r)
    {
        $q = (string) $r->string('q');

        $threads = ChatThread::query()
            ->with('author:id,name')
            ->withCount('messages')
            // ❗ อย่ากำหนด select เองใน latestMessage เพื่อลดปัญหา Column ... is ambiguous
            ->with(['latestMessage' => fn($qq) => $qq->with('user:id,name')])
            ->when($q, fn($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('chat.index', compact('threads'));
    }

    /**
     * สร้างกระทู้ใหม่
     */
    public function storeThread(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:180',
        ]);

        $thread = ChatThread::create([
            'title'     => $data['title'],
            'author_id' => Auth::id(),
        ]);

        return redirect()->route('chat.show', $thread);
    }

    /**
     * แสดงหน้ากระทู้และโหลด 50 ข้อความล่าสุด (เรียงจากเก่า->ใหม่)
     */
    public function show(ChatThread $thread)
    {
        $messages = $thread->messages()
            ->with('user:id,name')
            ->latest('created_at')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return view('chat.show', compact('thread', 'messages'));
    }

    /**
     * API: โหลดข้อความเพิ่ม (polling/htmx)
     * - ถ้ามี ?after_id=xxx จะโหลดเฉพาะที่ id > xxx (ใหม่กว่า)
     * - คืนค่าไม่เกิน 100 แถว เรียงจากเก่า->ใหม่
     */
    public function messages(Request $r, ChatThread $thread)
    {
        $afterId = $r->integer('after_id');

        $query = $thread->messages()
            ->with('user:id,name')
            ->orderBy('id', 'asc');

        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        return response()->json($query->take(100)->get());
    }

    /**
     * โพสต์ข้อความลงกระทู้
     */
    public function storeMessage(Request $r, ChatThread $thread)
    {
        abort_if($thread->is_locked, 403, 'Thread locked');

        $data = $r->validate([
            'body' => 'required|string|max:3000',
        ]);

        // ใช้ relation เพื่อให้ตั้งค่า chat_thread_id อัตโนมัติ
        $thread->messages()->create([
            'user_id' => Auth::id(),
            'body'    => $data['body'],
        ]);

        return back();
    }

    public function myUpdates(Request $request)
    {
      $u = $request->user();

      $threads = \App\Models\ChatThread::query()
          ->where(function ($q) use ($u) {
              $q->where('author_id', $u->id) // เจ้าของกระทู้
                ->orWhereHas('messages', fn ($mm) => $mm->where('user_id', $u->id)); // เคยคอมเมนต์
          })
          ->with(['messages' => function ($q) {
              // ดึงข้อความล่าสุด + ชื่อผู้ใช้
              $q->with('user:id,name')->latest('id')->limit(1);
          }])
          ->latest('updated_at')
          ->limit(30)
          ->get();

      // TODO: ถ้ามีตาราง unread ของจริง ให้คำนวณตรงนี้
      $items = $threads->map(function ($t) {
          $last = $t->messages->first();
          return [
              'id'              => $t->id,
              'title'           => $t->title ?? ('กระทู้ #' . $t->id),
              'show_url'        => route('chat.show', $t), // ไปหน้า show
              'unread'          => 0, // ไว้ปรับตามระบบ unread ของคุณ
              'last_user_name'  => $last?->user?->name,
              'last_body'       => $last?->body,
              'last_created_at' => optional($last?->created_at)->toIso8601String(),
          ];
      })->values();

      return response()->json($items);
  }
}
