<?php

namespace App\Http\Controllers;

use App\Models\ChatThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $r)
    {
        $q = (string) $r->string('q');

        $threads = ChatThread::query()
            ->with('author:id,name')
            ->withCount('messages')
            ->with(['latestMessage' => fn($qq) => $qq->with('user:id,name')])
            ->when($q, fn($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('chat.index', compact('threads'));
    }

    public function storeThread(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:180',
        ]);

        $thread = ChatThread::create([
            'title'     => $data['title'],
            'author_id' => Auth::id(),
            'is_locked' => false,
        ]);

        return redirect()->route('chat.show', $thread);
    }

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

    public function storeMessage(Request $r, ChatThread $thread)
    {
        // ถ้าล็อกแล้ว ห้ามโพสต์
        abort_if($thread->is_locked, 403, 'Thread locked');

        $data = $r->validate([
            'body' => 'required|string|max:3000',
        ]);

        $thread->messages()->create([
            'user_id' => Auth::id(),
            'body'    => $data['body'],
        ]);

        return back();
    }

    public function myUpdates(Request $request)
    {
        $u = $request->user();

        $threads = ChatThread::query()
            ->where(function ($q) use ($u) {
                $q->where('author_id', $u->id)
                  ->orWhereHas('messages', fn ($mm) => $mm->where('user_id', $u->id)); // เคยคอมเมนต์
            })
            ->with(['messages' => function ($q) {
                $q->with('user:id,name')->latest('id')->limit(1);
            }])
            ->latest('updated_at')
            ->limit(30)
            ->get();

        $items = $threads->map(function ($t) {
            $last = $t->messages->first();
            return [
                'id'              => $t->id,
                'title'           => $t->title ?? ('กระทู้ #' . $t->id),
                'show_url'        => route('chat.show', $t),
                'unread'          => 0, // ถ้าอยากนับ unread จริง ๆ ค่อยต่อ logic เพิ่มทีหลัง
                'last_user_name'  => $last?->user?->name,
                'last_body'       => $last?->body,
                'last_created_at' => optional($last?->created_at)->toIso8601String(),
            ];
        })->values();

        return response()->json($items);
    }

    // ========= Lock / Unlock =========

    public function lock(ChatThread $thread)
    {
        $this->authorizeLocking($thread);

        $thread->is_locked = true;
        $thread->save();

        return back()->with('status', 'ล็อกกระทู้เรียบร้อยแล้ว');
    }

    public function unlock(ChatThread $thread)
    {
        $this->authorizeLocking($thread);

        $thread->is_locked = false;
        $thread->save();

        return back()->with('status', 'ปลดล็อกกระทู้เรียบร้อยแล้ว');
    }

    protected function authorizeLocking(ChatThread $thread): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Forbidden');
        }

        // ให้สิทธิ์ทุกคนที่ role ไม่ใช่ member
        if ($user->role === 'member') {
            abort(403, 'Forbidden');
        }

        // ถ้าไม่ใช่ member ก็ผ่านได้เลย (admin, supervisor, technician, it_support, network, developer, ฯลฯ)
    }
}
