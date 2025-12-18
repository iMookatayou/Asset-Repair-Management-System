@extends('layouts.app')
@section('title', 'Community Chat | ระบบแลกเปลี่ยนเรียนรู้')

@section('content')
@php
    /* ประกาศ Class แค่นี้พอครับ ตัวแดงจะหายไป */
    use Illuminate\Support\Str;
    $q = request('q');
@endphp

<style>
    /* คุมโทนจากหน้าทรัพย์สิน */
    .sticky-chat-header {
        position: -webkit-sticky;
        position: sticky;
        top: 6rem;
        z-index: 20;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid #E2E8F0;
    }
</style>

<div class="pt-6 md:pt-8 lg:pt-10"></div>

<div class="w-full flex flex-col">

    {{-- ✅ ส่วนที่ 1: Header (ลอกขนาดและโครงสร้างหน้าทรัพย์สิน) --}}
    <div class="sticky-chat-header">
        <div class="px-4 md:px-6 lg:px-8 py-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-[17px] font-semibold text-slate-900">Community Chat</h1>
                    <p class="text-[13px] text-slate-500">พื้นที่แลกเปลี่ยนและสอบถามข้อมูลภายในองค์กร</p>
                </div>

                <button type="submit" form="main-chat-form"
                        class="inline-flex items-center gap-2 rounded-md bg-[#0F2D5C] px-4 py-2 text-[13px] font-medium text-white hover:bg-[#1e3a6d] shadow-sm transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    สร้างกระทู้ใหม่
                </button>
            </div>

            {{-- Search Grid 12 ช่อง ตามหน้าทรัพย์สิน --}}
            <form method="GET" id="main-chat-form" action="{{ route('chat.index') }}"
                  class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">

                <div class="md:col-span-10">
                    <label class="mb-1 block text-[12px] font-semibold text-slate-600">ค้นหาหัวข้อ / เริ่มตั้งหัวข้อใหม่</label>
                    <div class="relative">
                        <input name="q" value="{{ $q }}" id="chat-input"
                               class="w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/20 transition-all"
                               placeholder="พิมพ์หัวข้อที่ต้องการค้นหา หรือพิมพ์ที่นี่แล้วกดปุ่มสร้างกระทู้ด้านบน...">
                        <span class="absolute inset-y-0 left-0 flex items-center justify-center pl-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="md:col-span-2 flex items-end justify-end gap-2">
                    <a href="{{ route('chat.index') }}" onclick="showLoader()"
                       class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-all"
                       title="ล้างค่า">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg>
                    </a>
                    <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#0F2D5C] text-white hover:bg-[#1e3a6d] shadow-md transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ✅ ส่วนที่ 2: แถบสถานะย่อย --}}
    <div class="px-4 md:px-6 lg:px-8 py-2.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
        <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-[0.16em]">รายการกระทู้ล่าสุด</h3>
        <span class="text-[11px] font-semibold text-slate-400 uppercase">จำนวน {{ $threads->total() }} รายการ</span>
    </div>

    {{-- ✅ ส่วนที่ 3: รายการกระทู้ (สไตล์ List คมๆ) --}}
    <div class="divide-y divide-slate-100 bg-white">
        @forelse($threads as $th)
            <div class="group relative hover:bg-slate-50/50 transition-colors">
                <a href="{{ route('chat.show', $th) }}" class="block px-4 md:px-6 lg:px-8 py-4" onclick="showLoader()">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">

                        {{-- Status & Replies --}}
                        <div class="flex items-center gap-3 md:w-32 shrink-0">
                            @if($th->is_locked)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200">ปิดกระทู้</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase">Active</span>
                            @endif
                            <div class="flex items-center gap-1 text-slate-400 text-[11px] font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                {{ $th->messages_count ?? 0 }}
                            </div>
                        </div>

                        {{-- Title & Preview Content --}}
                        <div class="flex-1 min-w-0">
                            <h2 class="text-[14px] font-bold text-slate-800 group-hover:text-[#0F2D5C] transition-colors line-clamp-1">
                                {{ $th->title }}
                            </h2>
                            @php $last = $th->latestMessage; @endphp
                            <div class="text-[12px] text-slate-500 line-clamp-1 mt-0.5">
                                @if($last)
                                    <span class="font-bold text-slate-700">{{ $last->user->name ?? 'สมาชิก' }}:</span>
                                    {{ Str::limit(strip_tags($last->body), 100) }}
                                @else
                                    <span class="italic text-slate-300">ยังไม่มีการตอบกลับ...</span>
                                @endif
                            </div>
                        </div>

                        {{-- Author & Time (ภาษาไทย) --}}
                        <div class="md:text-right shrink-0">
                            <div class="text-[12px] font-bold text-slate-700">{{ $th->author->name ?? 'User' }}</div>
                            <div class="text-[11px] text-slate-400 font-bold uppercase tracking-tighter">
                                {{ $th->updated_at?->diffForHumans() }}
                            </div>
                        </div>

                        <div class="hidden md:block text-slate-200 group-hover:text-[#0F2D5C] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="py-24 text-center text-slate-400 text-[13px] bg-white">
                ไม่พบข้อมูลกระทู้ในขณะนี้
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($threads->hasPages())
        <div class="px-4 md:px-6 lg:px-8 mt-6 mb-12">
            {{ $threads->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Hidden Form สำหรับสร้างกระทู้ --}}
<form id="hidden-create-thread" method="POST" action="{{ route('chat.store') }}" style="display:none;">
    @csrf
    <input type="hidden" name="title" id="final-thread-title">
</form>

<script>
    // ดักปุ่ม "สร้างกระทู้ใหม่"
    document.querySelector('button[form="main-chat-form"]').addEventListener('click', function(e) {
        e.preventDefault();
        const inputVal = document.getElementById('chat-input').value;
        if(!inputVal.trim()) {
            alert('กรุณากรอกหัวข้อกระทู้ที่ต้องการสร้าง');
            return;
        }
        document.getElementById('final-thread-title').value = inputVal;
        showLoader();
        document.getElementById('hidden-create-thread').submit();
    });
</script>
@endsection

@section('after-content')
<div id="loaderOverlay" class="loader-overlay">
    <div class="loader-spinner"></div>
</div>
<style>
    .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:all .2s}
    .loader-overlay.show{visibility:visible;opacity:1}
    .loader-spinner{width:36px;height:36px;border:3.5px solid #0F2D5C;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
</style>
<script>
    function showLoader(){document.getElementById('loaderOverlay')?.classList.add('show')}
    function hideLoader(){document.getElementById('loaderOverlay')?.classList.remove('show')}
    document.addEventListener('DOMContentLoaded', hideLoader);
</script>
@endsection
