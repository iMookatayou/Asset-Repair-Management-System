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

  // pending -> รอรับเรื่อง
  $statusLabel = fn(?string $s) => [
    'pending'     => 'รอรับเรื่อง',
    'accepted'    => 'รับเรื่องแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
    'on_hold'     => 'พักไว้ชั่วคราว',
    'resolved'    => 'แก้ไขเสร็จสิ้น',
    'closed'      => 'ปิดงาน',
    'cancelled'   => 'ยกเลิกซ่อม',
  ][strtolower((string)$s)] ?? Str::of((string)$s)->replace('_',' ')->title();

  $priorityLabel = fn(?string $p) => [
    'low'    => 'ต่ำ',
    'medium' => 'ปานกลาง',
    'high'   => 'สูง',
    'urgent' => 'เร่งด่วน',
  ][strtolower((string)$p)] ?? '-';

  $statusDot = fn(?string $s) => match(strtolower((string)$s)) {
    'pending'     => 'bg-amber-500',
    'accepted'    => 'bg-indigo-500',
    'in_progress' => 'bg-sky-500',
    'on_hold'     => 'bg-slate-400',
    'resolved'    => 'bg-emerald-500',
    'closed'      => 'bg-zinc-400',
    'cancelled'   => 'bg-rose-500',
    default       => 'bg-slate-400',
  };

  $priorityClass = fn(?string $p) => match(strtolower((string)$p)) {
    'urgent' => 'text-rose-700 bg-rose-50 border-rose-200',
    'high'   => 'text-amber-700 bg-amber-50 border-amber-200',
    'medium' => 'text-blue-700 bg-blue-50 border-blue-200',
    'low'    => 'text-slate-600 bg-slate-50 border-slate-200',
    default  => 'text-gray-500 bg-gray-50 border-gray-200',
  };

  $hasActiveFilter =
    (($q ?? '') !== '') ||
    (($status ?? '') !== '') ||
    (($tech ?? '') !== '') ||
    (($filter ?? 'all') !== 'all');

  $activeTech = isset($tech) && isset($team) ? $team->firstWhere('id', (int)$tech) : null;

  $statPending    = (int)($stats['pending'] ?? 0);
  $statInProgress = (int)($stats['in_progress'] ?? 0);
  $statCompleted  = (int)($stats['completed'] ?? 0);
  $statMyActive   = (int)($stats['my_active'] ?? 0);

  $getIp = fn($r) => $r->client_ip ?? $r->ip_address ?? $r->ip ?? null;
  $getDept = fn($r) => $r->department->name ?? ($r->department->name_th ?? ($r->department->title ?? null));

  $getWorkOrderNo = function($r) {
    return $r->work_order_no
        ?? ($r->workOrder->order_no ?? null)
        ?? ($r->job_no ?? null)
        ?? null;
  };
@endphp

<div class="pt-6 md:pt-8 lg:pt-10"></div>

<div class="w-full flex flex-col min-h-screen bg-[#F8F9FA] pb-20 font-sans text-slate-900">

  {{-- HEADER: ใช้โค้ดเดิมของคุณ 그대로 --}}
  <div id="stickyHeaderMJ" class="sticky top-[4rem] md:top-[5rem] lg:top-[6rem] z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300">
    <div class="mx-auto px-4 md:px-6 lg:px-8 py-4 max-w-[95rem]">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
          <h1 class="text-[20px] font-bold text-slate-900">My Jobs</h1>
          <p class="mt-1 text-[13px] text-slate-600">
            จัดการและติดตามงานซ่อมบำรุง
            @if($activeTech)
              <span class="text-slate-500">• ของ {{ $activeTech->name }}</span>
            @endif
          </p>
        </div>

        {{-- ✅ สรุปงาน: คำสีๆ ไม่มีกรอบ + (เอา animation ออกจาก “รอรับเรื่อง” ที่ Header) --}}
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-[13px]">
          <span class="font-bold text-amber-700 flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
            รอรับเรื่อง <span id="stat-pending" class="text-amber-900">{{ $statPending }}</span>
          </span>

          <span class="font-bold text-sky-700 flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
            กำลังดำเนินการ <span id="stat-in-progress" class="text-slate-900">{{ $statInProgress }}</span>
          </span>

          <span class="font-bold text-emerald-700 flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            เสร็จสิ้น <span id="stat-completed" class="text-slate-900">{{ $statCompleted }}</span>
          </span>

          <span class="font-bold text-slate-700 flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
            งานของฉัน <span id="stat-my-active" class="text-slate-900">{{ $statMyActive }}</span>
          </span>

          {{-- Donut (คงไว้ตามเดิมของคุณ) --}}
          <div class="flex items-center gap-2 ml-2 border-l border-slate-200 pl-3">
            <div class="relative">
              <div id="donut" class="h-9 w-9 rounded-full" style="background: conic-gradient(#0F2D5C 0deg, #0F2D5C 0deg, #e2e8f0 0deg 360deg);"></div>
              <div class="absolute inset-0 m-auto h-5 w-5 rounded-full bg-white shadow-inner"></div>
            </div>
            <span id="donutPct" class="font-bold text-slate-700 text-xs">0%</span>
          </div>
        </div>
      </div>

      {{-- Filters (เดิม) --}}
      <form method="GET"
            action="{{ route('repairs.my_jobs') }}"
            class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
            onsubmit="showLoader()">

        <div class="md:col-span-7 min-w-0">
          <label for="q" class="mb-1 block text-[12px] text-slate-600">คำค้นหา</label>
          <div class="relative group">
            <input id="q" type="text" name="q" value="{{ $q }}"
                   placeholder="เช่น #ID, เรื่อง, ทรัพย์สิน, หน่วยงาน, ผู้แจ้ง..."
                   class="w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 py-2 text-[13px] placeholder:text-slate-400
                          focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35 transition-all">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400 group-focus-within:text-[#0F2D5C]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
          </div>
        </div>

        <div class="md:col-span-2">
          <label for="filter" class="mb-1 block text-[12px] text-slate-600">ช่วงงาน</label>
          <select id="filter" name="filter"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35 cursor-pointer">
            @foreach($filterLabels as $key => $label)
              <option value="{{ $key }}" @selected($filter===$key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-2">
          <label for="status" class="mb-1 block text-[12px] text-slate-600">สถานะ</label>
          <select id="status" name="status"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35 cursor-pointer">
            <option value="">ทุกสถานะ</option>
            @foreach(['pending','accepted','in_progress','on_hold','resolved','closed','cancelled'] as $s)
              <option value="{{ $s }}" @selected($status===$s)>{{ $statusLabel($s) }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-1 flex items-end justify-end gap-2">
          @if($hasActiveFilter)
            <a href="{{ route('repairs.my_jobs') }}"
               onclick="showLoader()"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600
                      hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/30 focus:ring-offset-1 transition-transform hover:scale-105"
               title="ล้างตัวกรอง" aria-label="ล้างตัวกรอง">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </a>
          @endif

          <button type="submit"
                  class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#0F2D5C] text-white
                         hover:bg-[#0F2D5C]/90 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/45 focus:ring-offset-1 transition-transform hover:scale-105 active:scale-95"
                  title="ค้นหา" aria-label="ค้นหา">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>
        </div>

        @if($tech)
          <input type="hidden" name="tech" value="{{ $tech }}">
        @endif
      </form>
    </div>
  </div>

  {{-- ✅ เพิ่มระยะห่างหลัง Header ให้ชัดขึ้น (จากเดิมน้อยไป) --}}
  <div class="h-10 md:h-12 lg:h-14"></div>

  {{-- LIST --}}
  <div class="mx-auto px-4 md:px-6 lg:px-8 max-w-[95rem]">
    <div id="myJobsTbody" class="space-y-4">
      @forelse($list as $r)
        @php
          $isOpen   = empty($r->technician_id) && (($r->status ?? '') === 'pending');

          $assetName = $r->asset->name ?? null;
          $assetCode = $r->asset->asset_code ?? null;

          $deptName = $getDept($r);
          $location = $r->location_text ?? null;

          $reporterName  = $r->reporter_name ?? $r->reporter?->name ?? '-';
          $reporterPhone = $r->reporter_phone ?? null;

          $ip = $getIp($r);

          $createdAtText = optional($r->created_at)->format('d/m/Y H:i');
          $statusText   = $statusLabel($r->status ?? null);
          $priorityText = $priorityLabel($r->priority ?? null);

          $workOrderNo = $getWorkOrderNo($r);
        @endphp

        <div class="bg-white border border-gray-300 rounded-sm shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden flex flex-col md:flex-row group">
          <div class="flex-grow flex flex-col border-b md:border-b-0 md:border-r border-gray-200">

            <div class="bg-white border-b border-gray-200 px-5 py-3 flex justify-between items-center gap-3">
              <div class="flex items-center gap-3 overflow-hidden">
                <span class="bg-white border border-gray-400 text-gray-800 px-2 py-0.5 text-sm font-mono font-bold rounded-sm shrink-0">
                  แจ้งซ่อม #{{ $r->id }}
                </span>

                <span class="bg-white border border-slate-300 text-slate-700 px-2 py-0.5 text-sm font-mono font-semibold rounded-sm shrink-0">
                  ใบงาน: {{ $workOrderNo ? $workOrderNo : '-' }}
                </span>

                <h3 class="font-bold text-gray-800 text-base md:text-lg truncate group-hover:text-[#0F2D5C] transition-colors" title="{{ $r->title }}">
                  {{ $r->title }}
                </h3>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs px-3 py-1 rounded-sm font-semibold border bg-white border-gray-200 text-gray-700 flex items-center gap-2">
                  <span class="relative inline-flex h-3 w-3">
                    {{-- (คง ping ในการ์ดได้ตามเดิม) --}}
                    @if(strtolower((string)$r->status) === 'pending')
                      <span class="mj-gps-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-60"></span>
                    @endif
                    <span class="relative inline-flex h-3 w-3 rounded-full {{ $statusDot($r->status) }}"></span>
                  </span>
                  {{ $statusText }}
                </span>

                <span class="text-xs px-3 py-1 rounded-sm border {{ $priorityClass($r->priority) }} font-bold uppercase">
                  {{ $priorityText }}
                </span>
              </div>
            </div>

            <div class="bg-white border-b border-gray-100 px-5 py-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-700">
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="font-bold">วันแจ้ง:</span> {{ $createdAtText }}
              </div>

              <div class="flex items-center gap-1.5 border-l border-gray-300 pl-4">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="font-bold">หน่วยงาน:</span> {{ $deptName ?? '-' }}
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-200 flex-grow">
              <div class="p-4 text-sm">
                <h4 class="text-xs font-bold text-rose-700 uppercase mb-1.5 tracking-wider">รายละเอียดปัญหา</h4>
                <div class="border border-rose-200 bg-rose-50 px-3 py-2 rounded-sm">
                  <p class="text-rose-800 leading-snug">
                    @if($r->description)
                      {{ $r->description }}
                    @else
                      <span class="text-rose-700 italic">- ไม่มีรายละเอียด -</span>
                    @endif
                  </p>
                </div>
              </div>

              <div class="p-4 text-sm">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-wider">สถานที่ / ทรัพย์สิน</h4>
                <div class="flex flex-col gap-1">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <span class="text-gray-500 text-xs shrink-0 w-16">สถานที่:</span>
                    <span class="font-semibold text-gray-900 truncate" title="{{ $location }}">{{ $location ?? '-' }}</span>
                  </div>
                  <div class="flex items-center gap-2 overflow-hidden">
                    <span class="text-gray-500 text-xs shrink-0 w-16">ทรัพย์สิน:</span>
                    <div class="truncate">
                      @if($assetCode)
                        <span class="font-mono text-xs font-bold text-[#0F2D5C] bg-indigo-50 px-1.5 py-0.5 rounded-sm mr-1">{{ $assetCode }}</span>
                      @endif
                      <span class="text-gray-800 truncate" title="{{ $assetName }}">{{ $assetName ?? '-' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="p-4 text-sm">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-wider">ผู้แจ้ง / ติดต่อ</h4>
                <div class="flex flex-col gap-1">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <span class="text-gray-500 text-xs shrink-0 w-16">ชื่อผู้แจ้ง:</span>
                    <span class="font-semibold text-gray-900 truncate">{{ $reporterName }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500 text-xs shrink-0 w-16">เบอร์โทร:</span>
                    <span class="font-medium text-gray-800">{{ $reporterPhone ?? '-' }}</span>
                  </div>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-gray-400 text-[10px] shrink-0 w-16">IP Addr:</span>
                    <span class="text-gray-400 text-[10px] font-mono">{{ $ip ?? '-' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="w-full md:w-44 bg-white border-t md:border-t-0 md:border-l border-gray-200 p-4 flex flex-col justify-center gap-3 shrink-0">
            @if($isOpen)
              @can('accept', $r)
                <form method="POST" action="{{ route('repairs.accept', $r) }}" onsubmit="return confirm('ยืนยันการรับเรื่อง #{{ $r->id }} ?')" class="w-full">
                  @csrf
                  <button type="submit" class="w-full h-10 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-sm shadow-md flex items-center justify-center gap-2 transition-all duration-200 transform hover:scale-105 active:scale-95 border border-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>รับเรื่อง</span>
                  </button>
                </form>
              @endcan
            @endif

            <a href="{{ route('maintenance.requests.show', $r) }}" onclick="showLoader()"
               class="w-full h-9 bg-white border border-gray-300 text-gray-700 hover:bg-white hover:text-[#0F2D5C] hover:border-[#0F2D5C] text-sm font-bold rounded-sm shadow-sm flex items-center justify-center gap-2 transition-all duration-200 transform hover:scale-105 active:scale-95">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <span>รายละเอียด</span>
            </a>

            @if($isOpen)
              @can('cancelByReporter', $r)
                <button type="button"
                        onclick="handleRejectClick(this, '{{ $r->id }}', '{{ $r->request_no }}', true)"
                        class="w-full h-9 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 hover:border-rose-400 text-sm font-bold rounded-sm shadow-sm flex items-center justify-center gap-2 transition-all duration-200 transform hover:scale-105 active:scale-95"
                        title="กด 2 ครั้งเพื่อยืนยัน">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  <span>ไม่รับเรื่อง</span>
                </button>
              @endcan
            @endif
          </div>
        </div>
      @empty
        <div class="bg-white border border-gray-300 rounded-sm p-16 text-center shadow-sm">
          <p class="text-sm text-gray-500">ไม่พบรายการงานตามเงื่อนไขที่เลือก</p>
        </div>
      @endforelse
    </div>

    @if($list->hasPages())
      <div class="mt-8 mb-10">
        {{ $list->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm px-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-md overflow-hidden transform scale-100 opacity-100 transition-all border-t-4 border-rose-600">
    <div class="bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
      <h3 class="text-lg font-bold text-rose-700 flex items-center gap-2">
        <span id="modalTitle">ไม่รับเรื่อง</span>
      </h3>
      <button type="button" onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form id="rejectForm" method="POST" action="">
      @csrf
      <div class="p-6 space-y-4">
        <p id="modalDesc" class="text-sm text-gray-600 leading-relaxed"></p>
        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">ระบุเหตุผล <span class="text-rose-500">*</span></label>
          <textarea name="reason" rows="3" required
                    class="w-full text-base border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500 p-3 placeholder:text-gray-400 bg-white"
                    placeholder="เช่น อุปกรณ์ไม่เพียงพอ..."></textarea>
        </div>
      </div>

      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-100 shadow-sm transition-transform hover:scale-105 active:scale-95">
          ยกเลิก
        </button>
        <button type="submit" class="px-4 py-2 bg-rose-600 text-white text-sm font-bold rounded-md hover:bg-rose-700 shadow-md transition-transform hover:scale-105 active:scale-95 flex items-center gap-2">
          ยืนยัน
        </button>
      </div>
    </form>
  </div>
</div>

<div id="loaderOverlay" class="loader-overlay">
  <div class="loader-spinner"></div>
</div>
@endsection

@push('styles')
<style>
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:48px;height:48px;border:5px solid #0F2D5C;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* ping (คงไว้ใน “การ์ด” ได้) */
  @keyframes mjPing {0%{transform:scale(1);opacity:.65}70%{transform:scale(2.8);opacity:0}100%{transform:scale(2.8);opacity:0}}
  .mj-gps-ping{animation:mjPing 1.2s ease-out infinite}
</style>
@endpush

@push('scripts')
<script>
  function showLoader(){ document.getElementById('loaderOverlay')?.classList.add('show') }
  function hideLoader(){ document.getElementById('loaderOverlay')?.classList.remove('show') }

  let rejectClicks = {};
  function handleRejectClick(btn, id, reqNo, isOpen) {
    const now = Date.now();
    if (rejectClicks[id] && (now - rejectClicks[id] < 3000)) {
      delete rejectClicks[id];
      btn.classList.remove('bg-rose-600', 'text-white', 'border-rose-600');
      btn.classList.add('bg-white', 'text-rose-600', 'border-rose-200');
      btn.querySelector('span').textContent = 'ไม่รับเรื่อง';
      openRejectModal(id, reqNo, isOpen);
    } else {
      rejectClicks[id] = now;
      btn.classList.remove('bg-white', 'text-rose-600', 'border-rose-200');
      btn.classList.add('bg-rose-600', 'text-white', 'border-rose-600');
      btn.querySelector('span').textContent = 'กดอีกครั้ง';
      setTimeout(() => {
        if (rejectClicks[id] === now) {
          delete rejectClicks[id];
          btn.classList.remove('bg-rose-600', 'text-white', 'border-rose-600');
          btn.classList.add('bg-white', 'text-rose-600', 'border-rose-200');
          btn.querySelector('span').textContent = 'ไม่รับเรื่อง';
        }
      }, 3000);
    }
  }

  function openRejectModal(id, requestNo, isOpen) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const titleSpan = document.getElementById('modalTitle');
    const descP = document.getElementById('modalDesc');

    form.action = `/maintenance/requests/${id}/reject`;
    titleSpan.textContent = `ไม่รับเรื่อง #${requestNo}`;
    descP.textContent = 'คุณต้องการปฏิเสธ/ไม่รับเรื่องนี้ใช่หรือไม่? กรุณาระบุเหตุผลเพื่อให้ผู้แจ้งรับทราบ';
    modal.classList.remove('hidden');
  }

  function closeRejectModal() { document.getElementById('rejectModal')?.classList.add('hidden'); }

  document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
  });

  // Donut (เดิม)
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
      #10b981 ${a2}deg ${a3}deg,
      #e2e8f0 ${a3}deg 360deg
    )`;
  }

  document.addEventListener('DOMContentLoaded', () => {
    hideLoader();
    renderDonut();
  });
</script>
@endpush
