{{-- resources/views/repairs/my_jobs.blade.php --}}
@extends('layouts.app')
@section('title','My Jobs')

@section('content')
@php
  use Illuminate\Support\Str;

  $q      = $q ?? request('q');
  $status = $status ?? request('status');
  $filter = $filter ?? (request('filter') ?: 'all');
  $tech   = $tech ?? request('tech');

  $filterLabels = [
    'my'        => 'งานของฉัน',
    'available' => 'งานว่าง',
    'all'       => 'ทั้งหมด',
  ];

  $statusLabel = fn(?string $s) => [
    'pending'     => 'รอดำเนินการ',
    'accepted'    => 'รับงานแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
    'on_hold'     => 'พักไว้ชั่วคราว',
    'resolved'    => 'แก้ไขเสร็จสิ้น',
    'closed'      => 'ปิดงาน',
  ][strtolower((string)$s)] ?? Str::of((string)$s)->replace('_',' ')->title();

  $statusTextClass = fn(?string $s) => match(strtolower((string)$s)) {
    'pending'     => 'text-amber-700',
    'accepted'    => 'text-indigo-700',
    'in_progress' => 'text-sky-700',
    'on_hold'     => 'text-slate-600',
    'resolved'    => 'text-emerald-700',
    'closed'      => 'text-zinc-500',
    default       => 'text-slate-700',
  };

  $priorityLabel = fn(?string $p) => [
    'low'    => 'ต่ำ',
    'medium' => 'ปานกลาง',
    'high'   => 'สูง',
    'urgent' => 'เร่งด่วน',
  ][strtolower((string)$p)] ?? '-';

  $priorityTextClass = fn(?string $p) => match(strtolower((string)$p)) {
    'low'    => 'text-zinc-500',
    'medium' => 'text-sky-700',
    'high'   => 'text-amber-700',
    'urgent' => 'text-rose-700',
    default  => 'text-slate-700',
  };

  $hasActiveFilter =
    (($q ?? '') !== '') ||
    (($status ?? '') !== '') ||
    (($tech ?? '') !== '') ||
    (($filter ?? 'all') !== 'all');

  $activeTech = isset($tech) && isset($team) ? $team->firstWhere('id', (int)$tech) : null;

  // stats from controller
  $statPending    = (int)($stats['pending'] ?? 0);
  $statInProgress = (int)($stats['in_progress'] ?? 0);
  $statCompleted  = (int)($stats['completed'] ?? 0);
  $statMyActive   = (int)($stats['my_active'] ?? 0);
@endphp

{{-- ✅ เว้นหัวเหมือน Maintenance Requests --}}
<div class="pt-6 md:pt-8 lg:pt-10"></div>

<div class="w-full flex flex-col">

  {{-- ✅ Sticky Header + Filters (เหมือน Maintenance Requests) --}}
  <div id="stickyHeaderMJ" class="sticky top-[6rem] z-20 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="px-4 md:px-6 lg:px-8 py-4">

      {{-- Title + Summary + Stats + Donut --}}
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
          <h1 class="text-[17px] font-semibold text-slate-900">My Jobs</h1>
          <p class="mt-1 text-[13px] text-slate-600">
            จัดการและติดตามงานซ่อมบำรุง • งานที่รับผิดชอบและงานที่สามารถรับเพิ่มได้
            @if($activeTech)
              <span class="text-slate-500">• ของ {{ $activeTech->name }}</span>
            @endif
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-[12px] text-slate-700">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
            <span>รอดำเนินการ</span>
            <span id="stat-pending" class="font-semibold text-slate-900">{{ $statPending }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-sky-500"></span>
            <span>กำลังดำเนินการ</span>
            <span id="stat-in-progress" class="font-semibold text-slate-900">{{ $statInProgress }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            <span>เสร็จสิ้น</span>
            <span id="stat-completed" class="font-semibold text-slate-900">{{ $statCompleted }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
            <span>งานของฉัน</span>
            <span id="stat-my-active" class="font-semibold text-slate-900">{{ $statMyActive }}</span>
          </div>

          <div class="flex items-center gap-2">
            <div class="relative">
              <div id="donut" class="h-9 w-9 rounded-full"
                   style="background: conic-gradient(#0F2D5C 0deg, #0F2D5C 0deg, #e2e8f0 0deg 360deg);"></div>
              <div class="absolute inset-0 m-auto h-5 w-5 rounded-full bg-white"></div>
            </div>
            <span id="donutPct" class="font-semibold text-slate-900">0%</span>
          </div>
        </div>
      </div>

      {{-- Filters --}}
      <form method="GET"
            action="{{ route('repairs.my_jobs') }}"
            class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
            onsubmit="showLoader()">

        {{-- คำค้นหา --}}
        <div class="md:col-span-7 min-w-0">
          <label for="q" class="mb-1 block text-[12px] text-slate-600">คำค้นหา</label>
          <div class="relative">
            <input id="q" type="text" name="q" value="{{ $q }}"
                   placeholder="เช่น #ID, เรื่อง, ทรัพย์สิน..."
                   class="w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 py-2 text-[13px] placeholder:text-slate-400
                          focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                      d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
          </div>
        </div>

        {{-- ช่วงงาน --}}
        <div class="md:col-span-2">
          <label for="filter" class="mb-1 block text-[12px] text-slate-600">ช่วงงาน</label>
          <select id="filter" name="filter"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            @foreach($filterLabels as $key => $label)
              <option value="{{ $key }}" @selected($filter===$key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        {{-- สถานะ --}}
        <div class="md:col-span-2">
          <label for="status" class="mb-1 block text-[12px] text-slate-600">สถานะ</label>
          <select id="status" name="status"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            <option value="">ทุกสถานะ</option>
            @foreach(['pending','accepted','in_progress','on_hold','resolved','closed'] as $s)
              <option value="{{ $s }}" @selected($status===$s)>{{ $statusLabel($s) }}</option>
            @endforeach
          </select>
        </div>

        {{-- ปุ่ม --}}
        <div class="md:col-span-1 flex items-end justify-end gap-2">
          @if($hasActiveFilter)
            <a href="{{ route('repairs.my_jobs') }}"
               onclick="showLoader()"
               class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600
                      hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/30 focus:ring-offset-1"
               title="ล้างตัวกรอง" aria-label="ล้างตัวกรอง">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </a>
          @endif

          <button type="submit"
                  class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#0F2D5C] text-white
                         hover:bg-[#0F2D5C]/90 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/45 focus:ring-offset-1"
                  title="ค้นหา" aria-label="ค้นหา">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>
        </div>

        @if($tech)
          <input type="hidden" name="tech" value="{{ $tech }}">
        @endif
      </form>
    </div>
  </div>

  {{-- ✅ แผงหัวข้อรายการ (เหมือน Maintenance Requests) --}}
  <div class="px-4 md:px-6 lg:px-8 py-2 border-b border-slate-200">
    <div class="flex items-center justify-between">
      <div class="text-[13px] font-semibold text-slate-800">รายการงานซ่อมบำรุง</div>
      <div class="text-[12px] text-slate-500">ทั้งหมด {{ $list->total() }} รายการ</div>
    </div>
  </div>

  {{-- ✅ Table (ไม่ทำ thead sticky เพื่อไม่ให้ชน/โดนกิน) --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-[13px]">
      <thead class="bg-white">
        <tr class="text-slate-600 border-b border-slate-200">
          <th class="p-3 text-left font-semibold w-[36%] whitespace-nowrap">เรื่อง</th>
          <th class="p-3 text-center font-semibold w-52 whitespace-nowrap">ทรัพย์สิน</th>
          <th class="p-3 text-center font-semibold w-28 whitespace-nowrap">ความสำคัญ</th>
          <th class="p-3 text-center font-semibold w-40 whitespace-nowrap">สถานะ</th>
          <th class="p-3 text-center font-semibold w-48 whitespace-nowrap">ช่างผู้รับผิดชอบ</th>
          <th class="p-3 text-center font-semibold min-w-[240px] whitespace-nowrap">การดำเนินการ</th>
        </tr>
      </thead>

      <tbody id="myJobsTbody" class="bg-white">
      @forelse($list as $r)
        @php
          $canAccept = !$r->technician_id && in_array($r->status, ['pending','accepted'], true);
        @endphp

        <tr class="align-top border-b border-slate-100 hover:bg-slate-50/60">
          <td class="p-3">
            <a href="{{ route('maintenance.requests.show', $r) }}"
               class="block max-w-full truncate font-semibold text-slate-900 hover:underline"
               onclick="showLoader()">
              #{{ $r->id }} — {{ $r->title }}
            </a>
            @if($r->description)
              <p class="mt-1 text-[12px] leading-relaxed text-slate-600">
                {{ Str::limit($r->description, 110) }}
              </p>
            @endif
          </td>

          <td class="p-3 align-middle text-center text-slate-700 max-w-[260px]">
            <div class="truncate font-medium text-slate-800">
              {{ $r->asset->name ?? '-' }}
            </div>
            @if(!empty($r->asset?->asset_code))
              <div class="mt-0.5 text-[11px] text-slate-500 truncate">
                {{ $r->asset->asset_code }}
              </div>
            @endif
          </td>

          <td class="p-3 align-middle whitespace-nowrap text-center">
            <span class="font-semibold {{ $priorityTextClass($r->priority ?? null) }}">
              {{ $priorityLabel($r->priority ?? null) }}
            </span>
          </td>

          <td class="p-3 align-middle whitespace-nowrap text-center">
            <span class="text-[14px] font-extrabold {{ $statusTextClass($r->status ?? null) }}">
              {{ $statusLabel($r->status ?? null) }}
            </span>
          </td>

          <td class="p-3 align-middle whitespace-nowrap text-center">
            @if($r->technician)
              <span class="text-slate-800 font-medium">{{ $r->technician->name }}</span>
            @else
              <span class="text-slate-400">—</span>
            @endif
          </td>

          <td class="p-3 text-center whitespace-nowrap align-middle">
            <div class="flex items-center justify-center gap-2">
              @if($canAccept)
                <form method="POST" action="{{ route('repairs.accept', $r) }}"
                      onsubmit="return confirm('ยืนยันการรับงาน #{{ $r->id }} ?')">
                  @csrf
                  <button type="submit"
                          class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-[12px] font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/40">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>
                    </svg>
                    รับงาน
                  </button>
                </form>
              @endif

              <a href="{{ route('maintenance.requests.show', $r) }}"
                 class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-[12px] font-semibold text-slate-800 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400"
                 onclick="showLoader()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                </svg>
                ดูรายละเอียด
              </a>
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

              @if($hasActiveFilter)
                @if(($filter ?? 'all') === 'my')
                  <p class="text-[13px]">คุณยังไม่มีงานที่รับผิดชอบในขณะนี้</p>
                @elseif(($filter ?? 'all') === 'available')
                  <p class="text-[13px]">ตอนนี้ยังไม่มีงานว่างให้รับเพิ่มตามเงื่อนไขที่เลือก</p>
                @else
                  <p class="text-[13px]">ไม่พบรายการงานตามเงื่อนไขที่เลือก</p>
                @endif
              @else
                <p class="text-[13px]">ตอนนี้ยังไม่มีงานซ่อมในระบบ</p>
              @endif
            </div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($list->hasPages())
    <div class="px-4 md:px-6 lg:px-8 mt-4 mb-8">
      {{ $list->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection

@section('after-content')
<div id="loaderOverlay" class="loader-overlay">
  <div class="loader-spinner"></div>
</div>

<style>
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:38px;height:38px;border:4px solid #0F2D5C;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
  function showLoader(){ document.getElementById('loaderOverlay')?.classList.add('show') }
  function hideLoader(){ document.getElementById('loaderOverlay')?.classList.remove('show') }

  function renderDonut(){
    const pending = parseInt((document.getElementById('stat-pending')?.textContent||'0').trim(), 10) || 0;
    const inprog  = parseInt((document.getElementById('stat-in-progress')?.textContent||'0').trim(), 10) || 0;
    const comp    = parseInt((document.getElementById('stat-completed')?.textContent||'0').trim(), 10) || 0;

    const total = pending + inprog + comp;
    const donut = document.getElementById('donut');
    const pctEl = document.getElementById('donutPct');
    if(!donut || !pctEl) return;

    const completedPct = total > 0 ? Math.round((comp / total) * 100) : 0;
    pctEl.textContent = `${completedPct}%`;

    const degPending = total > 0 ? (pending / total) * 360 : 0;
    const degInprog  = total > 0 ? (inprog  / total) * 360 : 0;
    const degComp    = total > 0 ? (comp    / total) * 360 : 0;

    const a0 = 0;
    const a1 = a0 + degPending;
    const a2 = a1 + degInprog;
    const a3 = a2 + degComp;

    donut.style.background = `conic-gradient(
      #f59e0b ${a0}deg ${a1}deg,
      #0ea5e9 ${a1}deg ${a2}deg,
      #0F2D5C ${a2}deg ${a3}deg,
      #e2e8f0 ${a3}deg 360deg
    )`;
  }

  let refreshInterval;

  function refreshMyJobs() {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.text())
      .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const pairs = [
          ['stat-pending', 'stat-pending'],
          ['stat-in-progress', 'stat-in-progress'],
          ['stat-completed', 'stat-completed'],
          ['stat-my-active', 'stat-my-active'],
        ];

        let changed = false;

        pairs.forEach(([idNew, idCur]) => {
          const n = doc.getElementById(idNew);
          const c = document.getElementById(idCur);
          if (n && c && n.textContent !== c.textContent) {
            c.textContent = n.textContent;
            changed = true;
          }
        });

        const newTbody = doc.querySelector('#myJobsTbody');
        const curTbody = document.querySelector('#myJobsTbody');

        if (newTbody && curTbody) {
          const curHTML = curTbody.innerHTML.trim();
          const newHTML = newTbody.innerHTML.trim();

          if (curHTML !== newHTML) {
            const curRows = curTbody.querySelectorAll('tr').length;
            const newRows = newTbody.querySelectorAll('tr').length;

            curTbody.innerHTML = newTbody.innerHTML;

            if (newRows > curRows && curRows > 0) {
              showNotification('มีงานใหม่เข้ามา!');
            }
            changed = true;
          }
        }

        if (changed) renderDonut();
      })
      .catch(err => console.error('Error refreshing:', err));
  }

  function showNotification(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-[#0F2D5C] text-white px-5 py-2.5 rounded-lg shadow-lg z-50 text-[13px]';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('opacity-0', 'transition-opacity');
      setTimeout(() => toast.remove(), 250);
    }, 2400);
  }

  document.addEventListener('DOMContentLoaded', () => {
    hideLoader();
    renderDonut();
    refreshInterval = setInterval(refreshMyJobs, 30000);
  });

  window.addEventListener('beforeunload', () => {
    if (refreshInterval) clearInterval(refreshInterval);
  });
</script>
@endsection
