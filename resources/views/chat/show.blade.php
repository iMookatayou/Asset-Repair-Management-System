@extends('layouts.app')
@section('title', $thread->title)

@php
  $totalMessages = $messages->count();
  $lastAt = $messages->last()?->created_at ?? $thread->created_at;

  $me = auth()->user();
  // ✅ แก้ตรงนี้: ใครก็ตามที่ role ไม่ใช่ member ให้จัดการ lock/unlock ได้
  $canManageLock = $me && ($me->role !== 'member');
@endphp

@section('page-header')
  <style>:root{ --app-top: 64px; }</style>

  <div class="sticky z-30 border-b border-slate-200 bg-white/90 backdrop-blur"
       style="top: var(--app-top, 0px)">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
      <div class="py-2.5 sm:py-3">
        <div class="flex items-center justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2 text-[11px] text-slate-500">
              <a href="{{ route('chat.index') }}" class="hover:underline">ห้องแชต</a>
              <span>›</span>
              <span class="truncate" title="{{ $thread->title }}">รายละเอียด</span>
            </div>
            <h1 class="mt-0.5 truncate text-base font-semibold text-slate-900 sm:text-lg" title="{{ $thread->title }}">
              {{ $thread->title }}
            </h1>
            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
              <span>สร้างเมื่อ {{ $thread->created_at->format('Y-m-d H:i') }}</span>
              <span class="select-none">·</span>
              <span>อัปเดตล่าสุด {{ $lastAt->format('Y-m-d H:i') }}</span>
              <span class="select-none">·</span>
              <span>{{ number_format($totalMessages) }} ข้อความ</span>
              @if($thread->is_locked)
                <span class="select-none">·</span>
                <span id="threadStatusBadge" class="inline-flex items-center rounded border border-amber-300 bg-amber-50 px-1.5 py-0.5 text-amber-700">
                  ล็อกแล้ว
                </span>
              @else
                <span class="select-none">·</span>
                <span id="threadStatusBadge" class="inline-flex items-center rounded border border-emerald-300 bg-emerald-50 px-1.5 py-0.5 text-emerald-700">
                  เปิดรับข้อความ
                </span>
              @endif
            </div>
          </div>

          <div class="flex items-center gap-2">
            <a href="{{ route('chat.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
              <svg viewBox="0 0 24 24" class="h-4 w-4"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Back
            </a>

            <button id="btnHeaderRefresh" type="button"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
               title="โหลดข้อความใหม่">
              <svg viewBox="0 0 24 24" class="h-4 w-4"><path d="M21 12a9 9 0 1 1-2.64-6.36" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Refresh
            </button>

            {{-- Lock / Unlock button (ตาม role) --}}
            @if($canManageLock)
              <button
                id="btnToggleLock"
                type="button"
                data-locked="{{ $thread->is_locked ? '1' : '0' }}"
                class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium
                       {{ $thread->is_locked
                          ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100'
                          : 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                @if($thread->is_locked)
                  <svg viewBox="0 0 24 24" class="h-4 w-4"><path d="M5 11h14v10H5z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  ปลดล็อก
                @else
                  <svg viewBox="0 0 24 24" class="h-4 w-4"><path d="M5 11h14v10H5z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 16v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 11V8a4 4 0 0 1 8 0v0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  ล็อกกระทู้
                @endif
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
  <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-2">
      <div class="flex items-center gap-2 text-xs text-slate-600">
        <span class="rounded-md bg-slate-100 px-2 py-0.5">ห้องแชต</span>
        <span class="hidden sm:inline">•</span>
        <span class="hidden truncate sm:inline" title="{{ $thread->title }}">{{ $thread->title }}</span>
      </div>
      <button id="btnScrollBottom"
              type="button"
              class="hidden rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-50"
              title="เลื่อนไปข้อความล่าสุด">
        ไปท้ายแชต
      </button>
    </div>

    <div class="p-0">
      <div id="chatBox"
           class="overflow-y-auto bg-white p-4"
           style="max-height: calc(100vh - var(--app-top, 0px) - 220px); min-height: 40vh;">
        @if($messages->isEmpty())
          <div class="grid place-items-center py-12">
            <div class="text-center">
              <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-slate-100">
                <svg viewBox="0 0 24 24" class="h-6 w-6 text-slate-500"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v10Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <p class="text-sm font-medium text-slate-800">ยังไม่มีข้อความ</p>
              <p class="mt-1 text-xs text-slate-500">พิมพ์ข้อความแรกเพื่อเริ่มการสนทนา</p>
            </div>
          </div>
        @else
          <div class="space-y-3">
            @foreach($messages as $m)
              <div class="flex gap-2">
                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-200 text-xs">
                  {{ strtoupper(mb_substr($m->user->name,0,1)) }}
                </div>
                <div>
                  <div class="text-sm">
                    <span class="font-medium">{{ $m->user->name }}</span>
                    <span class="text-xs text-gray-500">• {{ $m->created_at->format('Y-m-d H:i') }}</span>
                  </div>
                  <div class="whitespace-pre-line text-[15px] leading-snug">{{ $m->body }}</div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    @if(!$thread->is_locked)
      <form method="POST" action="{{ route('chat.messages.store',$thread) }}"
            class="flex items-start gap-2 border-t border-slate-200 bg-slate-50 px-3 py-3"
            id="chatForm">
        @csrf
        <label for="msgInput" class="sr-only">พิมพ์ข้อความ</label>
        <input id="msgInput" name="body" required maxlength="3000" placeholder="พิมพ์ข้อความ..."
               class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder-slate-400 focus:border-[#0E2B51] focus:ring-[#0E2B51]" />
        <button class="rounded-lg bg-[#0E2B51] px-3 py-2 text-white hover:opacity-95">ส่ง</button>
      </form>
    @else
      <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 text-center text-slate-600"
           id="lockedNotice">
        กระทู้นี้ถูกล็อก ไม่สามารถส่งข้อความใหม่ได้
      </div>
    @endif
  </div>

  @if (session('status'))
    <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
      {{ session('status') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif
</div>

<script>
  const box = document.getElementById('chatBox');
  const btnScrollBottom = document.getElementById('btnScrollBottom');
  const btnHeaderRefresh = document.getElementById('btnHeaderRefresh');

  const threadId = {{ $thread->id }};
  let lastId = {{ $messages->last()?->id ?? 0 }};
  let autoScroll = true;
  let polling = null;

  function appendMessage(m){
    const row = document.createElement('div');
    row.className = 'flex gap-2';
    row.innerHTML = `
      <div class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-200 text-xs">
        ${(m.user?.name || '?').slice(0,1).toUpperCase()}
      </div>
      <div>
        <div class="text-sm">
          <span class="font-medium">${m.user?.name || 'Unknown'}</span>
          <span class="text-xs text-gray-500">• ${new Date(m.created_at).toLocaleString()}</span>
        </div>
        <div class="text-[15px] leading-snug whitespace-pre-line"></div>
      </div>`;
    row.querySelector('.leading-snug').textContent = m.body;
    box.appendChild(row);
  }

  async function poll(){
    try{
      const res = await fetch(`{{ route('chat.messages',$thread) }}?after_id=${lastId}`, { headers:{ 'Accept':'application/json' }});
      if(!res.ok) return;
      const json = await res.json();
      const data = json.data ?? json; // เผื่อกรณี API ส่ง {data: [...]}
      if(Array.isArray(data) && data.length){
        data.forEach(m => { appendMessage(m); lastId = Math.max(lastId, m.id); });
        if (autoScroll) {
          box.scrollTop = box.scrollHeight;
        } else if (btnScrollBottom) {
          btnScrollBottom.classList.remove('hidden');
        }
      }
    }catch(e){}
  }

  function updateAutoScrollFlag(){
    const nearBottom = box.scrollTop + box.clientHeight >= box.scrollHeight - 30;
    autoScroll = nearBottom;
    if (nearBottom && btnScrollBottom) btnScrollBottom.classList.add('hidden');
  }
  box.addEventListener('scroll', updateAutoScrollFlag);

  if(btnScrollBottom){
    btnScrollBottom.addEventListener('click', () => {
      box.scrollTop = box.scrollHeight;
      autoScroll = true;
      btnScrollBottom.classList.add('hidden');
    });
  }
  if(btnHeaderRefresh){
    btnHeaderRefresh.addEventListener('click', () => { poll(); });
  }

  window.addEventListener('pageshow', () => {
    box.style.maxHeight = `calc(100vh - (getComputedStyle(document.documentElement).getPropertyValue('--app-top') || '0px') - 220px)`;
    box.scrollTop = box.scrollHeight;
    polling = setInterval(poll, 2000);
  });
  window.addEventListener('pagehide', () => {
    if (polling) clearInterval(polling);
  });

  const input = document.getElementById('msgInput');
  if (input) {
    input.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        const form = input.closest('form');
        if (form) form.submit();
      }
    });
  }

  // === Lock / Unlock (ใช้ web route) ===
  const btnToggleLock = document.getElementById('btnToggleLock');
  if (btnToggleLock) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    btnToggleLock.addEventListener('click', async () => {
      const isLocked = btnToggleLock.dataset.locked === '1';
      const confirmText = isLocked
        ? 'ต้องการปลดล็อกกระทู้นี้ใช่ไหม?'
        : 'ต้องการล็อกกระทู้นี้ ไม่ให้ส่งข้อความใหม่ใช่ไหม?';

      if (!window.confirm(confirmText)) return;

      const url = isLocked
        ? '{{ route('chat.unlock', $thread) }}'
        : '{{ route('chat.lock', $thread) }}';

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf ?? '',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        const text = await res.text();

        if (!res.ok) {
          console.error('Lock route error', res.status, text);
          alert(`Error ${res.status}: ${text || 'เกิดข้อผิดพลาดจากเซิร์ฟเวอร์'}`);
          return;
        }

        window.location.reload();
      } catch (e) {
        console.error('Lock route network error', e);
        alert('ไม่สามารถเปลี่ยนสถานะกระทู้ได้ (network error) กรุณาลองใหม่อีกครั้ง');
      }
    });
  }
</script>
@endsection
