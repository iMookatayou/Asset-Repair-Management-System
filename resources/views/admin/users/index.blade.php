{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')
@section('title','Manage Users')

@php
  /** @var \Illuminate\Pagination\LengthAwarePaginator $list */
  use App\Models\User as UserModel;

  $roles      = $roles      ?? UserModel::availableRoles();
  $roleLabels = $roleLabels ?? UserModel::roleLabels();
  $filters    = $filters    ?? ['s'=>'','role'=>'','department'=>''];

  /** @var \Illuminate\Support\Collection|\App\Models\Department[] $departments */
  $departments = $departments ?? collect();

  $hasFilter =
    (($filters['s'] ?? '') !== '') ||
    (($filters['role'] ?? '') !== '') ||
    (($filters['department'] ?? '') !== '');
@endphp

@section('content')

{{-- ✅ เว้นระยะจาก Navbar แบบ “วัดจริง” กันโดนกิน --}}
<div id="auTopSpacer" style="height: var(--au-nav-h, 96px);"></div>

<div class="w-full flex flex-col">

  {{-- ===== Sticky Header + Filters (เหมือน Maintenance) ===== --}}
  <div id="stickyHeaderAU"
       class="sticky z-30 bg-white border-b border-slate-200"
       style="top: var(--au-nav-h, 96px);">
    <div class="px-4 md:px-6 lg:px-8 py-4">

      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-[17px] font-semibold text-slate-900">Manage Users</h1>
          <p class="mt-1 text-[13px] text-slate-600">เรียกดู กรอง และจัดการผู้ใช้ในระบบ</p>
        </div>

        <a href="{{ route('admin.users.create') }}"
           onclick="showLoader()"
           class="inline-flex items-center gap-2 rounded-md bg-emerald-700 px-4 py-2 text-[13px] font-medium text-white
                  hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600/40">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
          </svg>
          สร้างผู้ใช้ใหม่
        </a>
      </div>

      <form method="GET"
            action="{{ route('admin.users.index') }}"
            class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
            onsubmit="showLoader()">

        {{-- Search --}}
        <div class="md:col-span-5 min-w-0">
          <label for="s" class="mb-1 block text-[12px] text-slate-600">คำค้นหา</label>
          <div class="relative">
            <input id="s" name="s" value="{{ $filters['s'] }}"
                   placeholder="เช่น ชื่อผู้ใช้, อีเมล, หน่วยงาน"
                   class="w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 py-2 text-[13px]
                          placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600/30 focus:border-emerald-600/30">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                      d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
          </div>
        </div>

        {{-- Role --}}
        <div class="md:col-span-3 min-w-0">
          <label for="role" class="mb-1 block text-[12px] text-slate-600">บทบาท</label>
          <select id="role" name="role"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-emerald-600/30 focus:border-emerald-600/30">
            <option value="">บทบาททั้งหมด</option>
            @foreach ($roles as $r)
              <option value="{{ $r }}" @selected(($filters['role'] ?? '') === $r)>{{ $roleLabels[$r] }}</option>
            @endforeach
          </select>
        </div>

        {{-- Department --}}
        <div class="md:col-span-3 min-w-0">
          <label for="department" class="mb-1 block text-[12px] text-slate-600">หน่วยงาน</label>
          <select id="department" name="department"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-emerald-600/30 focus:border-emerald-600/30">
            <option value="">ทุกหน่วยงาน</option>
            @foreach ($departments as $dept)
              @php $deptVal = $dept->code ?? $dept->id; @endphp
              <option value="{{ $deptVal }}" @selected(($filters['department'] ?? '') == $deptVal)>{{ $dept->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Buttons --}}
        <div class="md:col-span-1 flex items-end justify-end gap-2">
          <a href="{{ route('admin.users.index') }}"
             onclick="showLoader()"
             class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600
                    hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-600/30 focus:ring-offset-1"
             title="ล้างค่า" aria-label="ล้างค่า">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </a>

          <button type="submit"
                  class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-emerald-700 text-white
                         hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600/40 focus:ring-offset-1"
                  title="ค้นหา" aria-label="ค้นหา">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ===== Table (ไม่ครอบพื้นหลัง) ===== --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-[13px]">
      <thead class="bg-white">
        <tr class="text-slate-600 border-b border-slate-200">
          <th class="p-3 text-left font-semibold whitespace-nowrap">ชื่อ</th>
          <th class="p-3 text-left font-semibold whitespace-nowrap">อีเมล</th>
          <th class="p-3 text-left font-semibold whitespace-nowrap hidden lg:table-cell">หน่วยงาน</th>
          <th class="p-3 text-center font-semibold whitespace-nowrap hidden md:table-cell">บทบาท</th>
          <th class="p-3 text-left font-semibold whitespace-nowrap hidden xl:table-cell">สร้างเมื่อ</th>
          <th class="p-3 text-center font-semibold whitespace-nowrap min-w-[170px]">การดำเนินการ</th>
        </tr>
      </thead>

      <tbody>
      @forelse ($list as $u)
        @php
          $depName = $u->departmentRef->name ?? $u->department ?? '-';
          $roleTxt = $u->role_label ?? ($roleLabels[$u->role] ?? ucfirst($u->role));
        @endphp

        <tr class="border-b border-slate-100 hover:bg-slate-50/60">
          <td class="p-3 align-middle">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-xs font-semibold text-white">
                {{ strtoupper(mb_substr($u->name,0,1)) }}
              </div>
              <div class="min-w-0">
                <div class="truncate max-w-[220px] font-semibold text-slate-900">{{ $u->name }}</div>
                <div class="text-[11px] text-slate-500">#{{ $u->id }}</div>
              </div>
            </div>
          </td>

          <td class="p-3 align-middle">
            <div class="truncate max-w-[360px] text-slate-800">{{ $u->email }}</div>
          </td>

          <td class="p-3 align-middle hidden lg:table-cell">
            <div class="truncate max-w-[280px] text-slate-700">{{ $depName ?: '-' }}</div>
          </td>

          <td class="p-3 align-middle hidden md:table-cell text-center">
            <span class="text-[12px] font-semibold text-slate-700">{{ $roleTxt }}</span>
          </td>

          <td class="p-3 align-middle hidden xl:table-cell text-slate-700 whitespace-nowrap">
            {{ $u->created_at?->format('Y-m-d H:i') }}
          </td>

          <td class="p-3 align-middle text-center whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-2">
              <a href="{{ route('admin.users.edit', $u) }}"
                 onclick="showLoader()"
                 class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 bg-white px-3 py-2 text-[12px] font-semibold text-emerald-700
                        hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600/30 min-w-[92px] justify-center">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                </svg>
                แก้ไข
              </a>

              @if($u->id !== auth()->id())
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-md border border-rose-300 bg-white px-3 py-2 text-[12px] font-semibold text-rose-600
                               hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500/30 min-w-[76px] justify-center"
                        onclick="return window.confirmDeleteUser('{{ route('admin.users.destroy', $u) }}');">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                  </svg>
                  ลบ
                </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="py-16 text-center text-slate-600">
            <div class="flex flex-col items-center gap-2">
              <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>

              @if($hasFilter)
                <p class="text-[13px]">ไม่พบผู้ใช้ตามเงื่อนไขที่เลือก</p>
              @else
                <p class="text-[13px]">ตอนนี้ยังไม่มีผู้ใช้ในระบบ</p>
              @endif
            </div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  @if($list->hasPages())
    <div class="px-4 md:px-6 lg:px-8 mt-4 mb-6 md:mb-10 lg:mb-12">
      {{ $list->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection

@section('after-content')
<div id="loaderOverlay" class="loader-overlay">
  <div class="loader-spinner"></div>
</div>

<form id="delete-user-form" method="POST" class="hidden">
  @csrf
  @method('DELETE')
</form>

<style>
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:38px;height:38px;border:4px solid #0F2D5C;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
  function showLoader(){ document.getElementById('loaderOverlay')?.classList.add('show') }
  function hideLoader(){ document.getElementById('loaderOverlay')?.classList.remove('show') }

  function detectNavbarHeight(){
    // จับ “ตัวบนสุดที่เป็น fixed/sticky” เพื่อให้ offset เป๊ะ
    const all = Array.from(document.querySelectorAll('header, nav, [role="banner"], #navbar, #topNav, .navbar'));
    let best = null;
    let bestH = 0;

    all.forEach(el => {
      const cs = window.getComputedStyle(el);
      if (cs.position !== 'fixed' && cs.position !== 'sticky') return;
      const r = el.getBoundingClientRect();
      if (r.height > bestH) { bestH = r.height; best = el; }
    });

    if (!bestH) {
      // fallback: เอาสูงสุดของ candidates
      all.forEach(el => {
        const r = el.getBoundingClientRect();
        if (r.height > bestH) bestH = r.height;
      });
    }

    return Math.round(bestH || 96);
  }

  function syncNavOffset(){
    const navH = detectNavbarHeight();
    document.documentElement.style.setProperty('--au-nav-h', `${navH}px`);
  }

  window.confirmDeleteUser = function(url){
    if(!confirm('ยืนยันการลบผู้ใช้นี้?')) return false;
    const f = document.getElementById('delete-user-form');
    if(!f) return true;
    f.action = url;
    f.submit();
    return false;
  };

  document.addEventListener('DOMContentLoaded', () => {
    hideLoader();
    syncNavOffset();
  });

  window.addEventListener('resize', syncNavOffset);
</script>
@endsection
