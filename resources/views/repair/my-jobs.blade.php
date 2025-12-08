{{-- resources/views/repairs/my_jobs.blade.php --}}
@extends('layouts.app')
@section('title','My Jobs')

@section('content')
@php
  use Illuminate\Support\Str;

  $q      = $q ?? request('q');
  $status = $status ?? request('status');
  $filter = $filter ?? 'all';
  $tech   = $tech ?? request('tech');

  $filterLabels = [
    'my'        => 'งานของฉัน',
    'available' => 'งานว่าง',
    'all'       => 'ทั้งหมด',
  ];

  // label สถานะ (เหมือนหน้าอื่น)
  $statusLabel = fn(?string $s) => [
    'pending'     => 'รอดำเนินการ',
    'accepted'    => 'รับงานแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
    'on_hold'     => 'พักไว้ชั่วคราว',
    'resolved'    => 'แก้ไขเสร็จสิ้น',
    'closed'      => 'ปิดงาน',
  ][strtolower((string)$s)] ?? Str::of((string)$s)->replace('_',' ')->title();

  /*
   * STATUS COLOR MAPPING (คำสี ๆ ไม่มีกรอบ – ใช้ให้เหมือนหน้าอื่น)
   *   pending     => text-amber-700
   *   accepted    => text-indigo-700
   *   in_progress => text-sky-700
   *   on_hold     => text-zinc-600
   *   resolved    => text-emerald-700
   *   closed      => text-zinc-500
   */
  $statusTextClass = fn(?string $s) => match(strtolower((string)$s)) {
    'pending'     => 'text-amber-700',
    'accepted'    => 'text-indigo-700',
    'in_progress' => 'text-sky-700',
    'on_hold'     => 'text-zinc-600',
    'resolved'    => 'text-emerald-700',
    'closed'      => 'text-zinc-500',
    default       => 'text-zinc-700',
  };

  // label ความสำคัญ
  $priorityLabel = fn(?string $p) => [
    'low'    => 'ต่ำ',
    'medium' => 'ปานกลาง',
    'high'   => 'สูง',
    'urgent' => 'เร่งด่วน',
  ][strtolower((string)$p)] ?? '-';

  /*
   * PRIORITY COLOR MAPPING (คำสี ๆ ไม่มีกรอบ – ให้ตรงกับหน้า Maintenance/Assets)
   *   low    => text-zinc-500
   *   medium => text-sky-700
   *   high   => text-amber-700
   *   urgent => text-rose-700
   */
  $priorityTextClass = fn(?string $p) => match(strtolower((string)$p)) {
    'low'    => 'text-zinc-500',
    'medium' => 'text-sky-700',
    'high'   => 'text-amber-700',
    'urgent' => 'text-rose-700',
    default  => 'text-zinc-700',
  };
@endphp

<div class="pt-3 md:pt-4"></div>

{{-- MAIN WRAPPER --}}
<div
  id="myJobsContainer"
  class="w-full px-4 md:px-6 lg:px-8 flex flex-col gap-5 pb-8"
>
  {{-- ===== Sticky Header + Filter Card ===== --}}
  <div class="sticky top-[6rem] z-20 bg-slate-50/90 backdrop-blur">
    <div class="rounded-lg border border-zinc-300 bg-white shadow-sm">
      <div class="px-5 py-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
          {{-- Left: Icon + Title --}}
          <div class="flex items-start gap-3">
            <div class="grid h-9 w-9 place-items-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <rect x="3" y="7" width="18" height="13" rx="2" />
                <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                <path d="M3 12h18" />
              </svg>
            </div>
            <div>
              <h1 class="text-[17px] font-semibold text-zinc-900">My Jobs</h1>
              <p class="text-[13px] text-zinc-600">
                จัดการและติดตามงานซ่อมบำรุงทรัพย์สินของคุณ • งานที่รับผิดชอบและงานที่สามารถรับเพิ่มได้
              </p>
            </div>
          </div>

          {{-- Right: Stats --}}
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 w-full md:w-auto text-[11px]">
            <div class="min-w-[90px] px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200">
              <p class="text-[11px] text-zinc-600">รอดำเนินการ</p>
              <p class="mt-0.5 text-lg font-semibold text-amber-700" id="stat-pending">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="min-w-[90px] px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200">
              <p class="text-[11px] text-zinc-600">กำลังดำเนินการ</p>
              <p class="mt-0.5 text-lg font-semibold text-sky-700" id="stat-in-progress">{{ $stats['in_progress'] ?? 0 }}</p>
            </div>
            <div class="min-w-[90px] px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200">
              <p class="text-[11px] text-zinc-600">เสร็จสิ้น</p>
              <p class="mt-0.5 text-lg font-semibold text-emerald-700" id="stat-completed">{{ $stats['completed'] ?? 0 }}</p>
            </div>
            <div class="min-w-[90px] px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200">
              <p class="text-[11px] text-zinc-600">งานของฉัน (กำลังทำ)</p>
              <p class="mt-0.5 text-lg font-semibold text-indigo-700" id="stat-my-active">{{ $stats['my_active'] ?? 0 }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 h-px bg-zinc-200"></div>

        {{-- Search / Filter Form --}}
        <form method="GET"
              class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12">
          {{-- Search --}}
          <div class="md:col-span-5 min-w-0">
            <label for="q" class="mb-1 block text-[12px] text-zinc-600">คำค้นหา</label>
            <div class="relative">
              <input type="text" id="q" name="q" value="{{ $q }}"
                     placeholder="ค้นหาเรื่อง, ทรัพย์สิน..."
                     class="w-full rounded-md border border-zinc-300 pl-10 pr-3 py-2 text-[13px] placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600" />
              <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-zinc-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </span>
            </div>
          </div>

          {{-- Filter: scope --}}
          <div class="md:col-span-2">
            <label for="filter" class="mb-1 block text-[12px] text-zinc-600">ช่วงงาน</label>
            <select id="filter" name="filter"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-[13px] text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                    onchange="this.form.submit()">
              @foreach($filterLabels as $key => $label)
                <option value="{{ $key }}" @selected($filter===$key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          {{-- Status --}}
          <div class="md:col-span-3">
            <label for="status" class="mb-1 block text-[12px] text-zinc-600">สถานะ</label>
            <select id="status" name="status"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-[13px] text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              <option value="">ทุกสถานะ</option>
              @foreach(['pending','accepted','in_progress','on_hold','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected($status===$s)>{{ $statusLabel($s) }}</option>
              @endforeach
            </select>
          </div>

          {{-- Hidden tech (ถ้ามี) --}}
          @if($tech)
            <input type="hidden" name="tech" value="{{ $tech }}" />
          @endif

          {{-- Buttons --}}
          <div class="md:col-span-2 flex items-end gap-2">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-md border border-emerald-700 bg-emerald-700 px-3 py-2 text-[13px] font-medium text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              ค้นหา
            </button>
            @if($q || $status)
              <a href="{{ route('repairs.my_jobs', ['filter' => $filter] + ($tech ? ['tech'=>$tech] : [])) }}"
                 class="inline-flex items-center justify-center rounded-md border border-zinc-300 bg-white px-3 py-2 text-[13px] font-medium text-zinc-800 hover:bg-zinc-50">
                ล้างค่า
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ===== Main Table Card ===== --}}
  <div class="rounded-lg border border-zinc-300 bg-white overflow-hidden">
    @php
      $activeTech = isset($tech) && isset($team) ? $team->firstWhere('id', (int)$tech) : null;
    @endphp

    @if($activeTech)
      <div class="px-5 py-2.5 bg-emerald-50 text-emerald-900 border-b border-emerald-100 text-[13px]">
        กำลังดูงานของ: <span class="font-medium">{{ $activeTech->name }}</span>
      </div>
    @endif

    <div class="relative overflow-x-auto">
      <table class="min-w-full text-[13px]">
        <thead class="bg-zinc-50 border-b border-zinc-200">
          <tr class="text-zinc-700">
            <th class="px-4 py-3 text-center font-semibold w-[32%] whitespace-nowrap">เรื่อง</th>
            <th class="px-4 py-3 text-center font-semibold w-40 whitespace-nowrap">ทรัพย์สิน</th>
            <th class="px-4 py-3 text-center font-semibold w-28 whitespace-nowrap">ความสำคัญ</th>
            <th class="px-4 py-3 text-center font-semibold w-36 whitespace-nowrap">สถานะ</th>
            <th class="px-4 py-3 text-center font-semibold w-40 whitespace-nowrap">ช่างผู้รับผิดชอบ</th>
            <th class="px-4 py-3 text-center font-semibold whitespace-nowrap min-w-[200px]">การดำเนินการ</th>
          </tr>
        </thead>

        <tbody class="bg-white">
        @forelse($list as $r)
          @php
            $canAccept = !$r->technician_id && in_array($r->status, ['pending','accepted']);
          @endphp
          <tr class="align-top hover:bg-zinc-50 border-b last:border-0">
            {{-- เรื่อง --}}
            <td class="px-4 py-3">
              <a href="{{ route('maintenance.requests.show', $r) }}"
                 class="block max-w-full truncate font-medium text-zinc-900 hover:underline">
                #{{ $r->id }} — {{ $r->title }}
              </a>
              @if($r->description)
                <p class="mt-1 text-[12px] leading-relaxed text-zinc-600 max-w-full">
                  {{ Str::limit($r->description, 80) }}
                </p>
              @endif
            </td>

            {{-- ทรัพย์สิน --}}
            <td class="px-4 py-3 text-zinc-700 max-w-[220px] truncate text-center">
              {{ $r->asset->name ?? '-' }}
            </td>

            {{-- ความสำคัญ: คำสี ๆ ไม่มีกรอบ --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
              <span class="text-[12px] font-medium {{ $priorityTextClass($r->priority ?? null) }}">
                {{ $priorityLabel($r->priority ?? null) }}
              </span>
            </td>

            {{-- สถานะ: คำสี ๆ ไม่มีกรอบ --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
              <span class="text-[12px] font-medium {{ $statusTextClass($r->status ?? null) }}">
                {{ $statusLabel($r->status ?? null) }}
              </span>
            </td>

            {{-- ช่างผู้รับผิดชอบ --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
              @if($r->technician)
                <span class="text-[13px] text-zinc-800">{{ $r->technician->name }}</span>
              @else
                <span class="text-[13px] text-zinc-400">-</span>
              @endif
            </td>

            {{-- การดำเนินการ --}}
            <td class="px-4 py-3 text-center whitespace-nowrap align-middle">
              <div class="h-full flex items-center justify-center gap-2">
                @if($canAccept)
                  <form method="POST" action="{{ route('repairs.accept', $r) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-md border border-emerald-600 bg-emerald-600 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 whitespace-nowrap">
                      รับงาน
                    </button>
                  </form>
                @endif
                <a href="{{ route('maintenance.requests.show', $r) }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-[12px] font-medium text-zinc-800 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-400 whitespace-nowrap">
                  ดูรายละเอียด
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-[13px]">ไม่พบรายการงานตามเงื่อนไขที่เลือก</p>
              </div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  @if($list->hasPages())
    <div class="mt-3">
      {{ $list->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
// Real-time updates every 30 seconds (logic เดิม)
let refreshInterval;

function refreshMyJobs() {
  fetch(window.location.href, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.text())
  .then(html => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    // Update stats
    const statIds = ['stat-pending', 'stat-in-progress', 'stat-completed', 'stat-my-active'];
    statIds.forEach(id => {
      const newStat = doc.getElementById(id);
      const currentStat = document.getElementById(id);
      if (newStat && currentStat && newStat.textContent !== currentStat.textContent) {
        currentStat.textContent = newStat.textContent;
        currentStat.classList.add('animate-pulse');
        setTimeout(() => currentStat.classList.remove('animate-pulse'), 800);
      }
    });

    // Update table body
    const newTbody = doc.querySelector('tbody.bg-white');
    const currentTbody = document.querySelector('#myJobsContainer tbody.bg-white');
    if (newTbody && currentTbody) {
      const currentRows = currentTbody.querySelectorAll('tr').length;
      const newRows = newTbody.querySelectorAll('tr').length;

      if (currentRows !== newRows || newTbody.innerHTML !== currentTbody.innerHTML) {
        currentTbody.innerHTML = newTbody.innerHTML;

        if (newRows > currentRows && currentRows > 0) {
          showNotification('มีงานใหม่เข้ามา!');
        }
      }
    }
  })
  .catch(error => console.error('Error refreshing:', error));
}

function showNotification(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 bg-emerald-700 text-white px-5 py-2.5 rounded-lg shadow-lg z-50';
  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('opacity-0', 'transition-opacity');
    setTimeout(() => toast.remove(), 250);
  }, 2500);
}

refreshInterval = setInterval(refreshMyJobs, 30000);

window.addEventListener('beforeunload', () => {
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>
@endpush
