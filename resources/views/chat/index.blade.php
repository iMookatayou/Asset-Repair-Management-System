@extends('layouts.app')
@section('title','Community Chat')

@section('content')
  <style>
    .btn-hard{
      display:inline-flex;align-items:center;justify-content:center;
      height:44px;min-width:110px;padding:0 16px;border-radius:10px;
      font-weight:600;background:#059669;color:#fff;
      box-shadow: 0 1px 0 rgba(0,0,0,.02);
    }
    .btn-hard:hover{background:#047857}
    .btn-hard:active{transform:translateY(.5px)}
    .btn-outline{
      display:inline-flex;align-items:center;justify-content:center;
      height:40px;padding:0 12px;border-radius:10px;
      border:1px solid #CBD5E1;color:#0F172A;background:#fff;
    }
    .btn-outline:hover{background:#F8FAFC}
    .chip{
      display:inline-flex;align-items:center;gap:6px;
      height:24px;padding:0 8px;border-radius:999px;
      background:#F1F5F9;color:#334155;font-size:12px;font-weight:500;
    }
  </style>

  <div class="max-w-5xl mx-auto py-6 space-y-6">

    {{-- Sticky header + search --}}
    <div class="sticky top-[6rem] z-20 bg-slate-50/90 backdrop-blur">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="px-5 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="flex items-center gap-3">
            <div class="size-10 grid place-items-center rounded-xl bg-emerald-50 text-emerald-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                      d="M6 8h12M6 12h8M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v11l-3.5 3.5H6a2 2 0 0 1-2-2V5z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl font-semibold text-slate-900">Community Chat</h1>
              <p class="text-sm text-slate-600">ถาม-ตอบ แชร์ทริก แลกเปลี่ยนประสบการณ์กันได้ที่นี่</p>
            </div>
          </div>

          <form method="GET" action="{{ route('chat.index') }}"
                class="w-full md:w-[440px] flex items-center rounded-xl border border-slate-300 bg-white shadow-sm focus-within:ring-2 focus-within:ring-emerald-500">
            <input
              type="text"
              name="q"
              value="{{ request('q','') }}"
              placeholder="ค้นหากระทู้…"
              class="flex-1 px-3 h-11 text-sm bg-transparent outline-none border-0 rounded-l-xl"
              aria-label="Search threads"
            >
            <button type="submit"
                    class="h-11 px-4 text-sm font-medium text-white bg-emerald-700 rounded-r-xl hover:bg-emerald-800 active:translate-y-[0.5px]">
              Search
            </button>
          </form>
        </div>
        <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-5 space-y-3">
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-600">สร้างกระทู้ใหม่</div>
          @auth
            <div class="chip" title="คุณกำลังโพสต์ในนาม: {{ auth()->user()->name }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/></svg>
              {{ auth()->user()->name }}
            </div>
          @endauth
        </div>

        <form method="POST" action="{{ route('chat.store') }}" class="space-y-3">
          @csrf
          <div class="flex flex-col sm:flex-row gap-3">
            <input
              name="title"
              required
              maxlength="180"
              class="w-full sm:flex-1 rounded-lg border border-slate-300 px-3 h-11 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
              placeholder='หัวข้อกระทู้ เช่น "เลือกเครื่องพิมพ์เวรดึกยังไงให้ไม่งอแง?"'
              value="{{ old('title') }}"
              aria-label="Thread title"
            >
            <button type="submit" class="btn-hard" aria-label="Post thread">
              โพสต์
            </button>
          </div>

          @error('title')
            <p class="text-sm text-rose-600">{{ $message }}</p>
          @enderror

          @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
              {{ session('status') }}
            </div>
          @endif
        </form>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <div class="px-5 py-3 border-b border-slate-200 text-sm font-medium text-slate-800">
        กระทูล่าสุด
        @if(request('q'))
          <span class="ml-2 text-slate-500 font-normal">ผลลัพธ์สำหรับ “{{ request('q') }}”</span>
        @endif
      </div>

      @if($threads->count())
        <ul class="divide-y divide-slate-200">
          @foreach($threads as $th)
            <li>
              <a href="{{ route('chat.show', $th) }}" class="block px-5 py-4 hover:bg-slate-50 focus:bg-slate-50">
                <div class="flex items-start gap-3">
                  {{-- สถานะกระทู้: Locked / เปิดรับข้อความ --}}
                  @php $locked = (bool)$th->is_locked; @endphp
                  <span class="chip shrink-0"
                        @if($locked)
                          style="background:#FEF3C7;color:#92400E;"
                          title="Thread locked"
                        @else
                          style="background:#ECFDF5;color:#047857;"
                          title="เปิดรับข้อความ"
                        @endif
                  >
                    @if($locked)
                      <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M7 10V7a5 5 0 0 1 10 0v3M6 10h12v9a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-9z"/>
                      </svg>
                      Locked
                    @else
                      {{-- ไอคอนใหม่: bubble แชต + เปิดรับข้อความ --}}
                      <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5 6a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H11l-3.5 3.5a.7.7 0 0 1-1.2-.5V15A3 3 0 0 1 5 12V6z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 9h6"/>
                      </svg>
                      เปิดรับข้อความ
                    @endif
                  </span>

                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                      <h2 class="font-medium text-slate-900 line-clamp-1">{{ $th->title }}</h2>
                      <span class="chip" title="จำนวนข้อความในกระทู้นี้">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M7 8h10M7 12h7M5 4h14a2 2 0 012 2v10l-4 4H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                        </svg>
                        {{ $th->messages_count ?? 0 }}
                      </span>
                    </div>

                    <div class="mt-1 text-xs text-slate-600">
                      โดย {{ $th->author->name ?? 'Unknown' }}
                      • {{ $th->created_at?->diffForHumans() }}
                    </div>

                    @php
                      $last = $th->latestMessage ?? null;
                    @endphp
                    @if($last)
                      <div class="mt-2 text-sm text-slate-700 line-clamp-2">
                        <span class="font-medium">{{ optional($last->user)->name ?? 'Someone' }}: </span>
                        {{ \Illuminate\Support\Str::limit(strip_tags($last->body), 180) }}
                        <span class="text-xs text-slate-500">— {{ $last->created_at?->diffForHumans() }}</span>
                      </div>
                    @endif
                  </div>

                  <div class="shrink-0 self-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 5l7 7-7 7"/>
                    </svg>
                  </div>
                </div>
              </a>
            </li>
          @endforeach
        </ul>
      @else
        <div class="p-10 text-center">
          <div class="mx-auto mb-3 size-12 grid place-items-center rounded-full bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M7 8h10M7 12h7M5 4h14a2 2 0 012 2v10l-4 4H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>
            </svg>
          </div>
          <div class="font-medium text-slate-800">ยังไม่มีกระทู้</div>
          <p class="text-sm text-slate-600">เริ่มพูดคุยกันได้เลยด้วยการสร้างกระทู้ใหม่ด้านบน</p>
        </div>
      @endif
    </div>

    @if($threads->count())
      <div class="mt-2">
        {{ $threads->withQueryString()->links() }}
      </div>
    @endif

  </div>
@endsection
