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

  // Helpers สำหรับแปลงสถานะเป็นภาษาไทยและสี
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

  // เช็คว่ามีการ filter อยู่หรือไม่
  $hasActiveFilter =
    (($q ?? '') !== '') ||
    (($status ?? '') !== '') ||
    (($tech ?? '') !== '') ||
    (($filter ?? 'all') !== 'all');

  // ดึงข้อมูลช่างที่ถูกเลือก (ถ้ามี)
  $activeTech = isset($tech) && isset($team) ? $team->firstWhere('id', (int)$tech) : null;

  // Stats numbers
  $statPending    = (int)($stats['pending'] ?? 0);
  $statInProgress = (int)($stats['in_progress'] ?? 0);
  $statCompleted  = (int)($stats['completed'] ?? 0);
  $statMyActive   = (int)($stats['my_active'] ?? 0);

  // Helpers เล็กๆน้อยๆ
  $getIp = fn($r) => $r->client_ip ?? $r->ip_address ?? $r->ip ?? null;
  $getDept = fn($r) => $r->department->name ?? ($r->department->name_th ?? ($r->department->title ?? null));

  $getWorkOrderNo = function($r) {
    return $r->work_order_no
        ?? ($r->workOrder->order_no ?? null)
        ?? ($r->job_no ?? null)
        ?? null;
  };

  $acceptBaseUrl = url('/maintenance/requests');
  $teamUsers = $team ?? collect();
@endphp

<div class="w-full min-h-screen bg-white text-slate-900 font-sans pb-14">

  {{-- HEADER (Sticky) --}}
  <div id="stickyHeaderMJ"
       class="sticky top-[4rem] md:top-[5rem] lg:top-[6rem] z-40 bg-white border-b border-slate-200 transition-all duration-200">
    <div class="w-full px-4 md:px-6 lg:px-8 py-4">
      <div class="flex flex-wrap items-start justify-between gap-3">
        {{-- Title --}}
        <div class="min-w-0">
          <h1 class="text-[20px] font-bold text-slate-900">My Jobs</h1>
          <p class="mt-1 text-[13px] text-slate-600">
            จัดการและติดตามงานซ่อมบำรุง
            @if($activeTech)
              <span class="text-slate-500">• ของ {{ $activeTech->name }}</span>
            @endif
          </p>
        </div>

        {{-- Stats Bar & Donut --}}
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

          {{-- Donut Chart --}}
          <div class="flex items-center gap-2 ml-2 border-l border-slate-200 pl-3">
            <div class="relative">
              {{-- CSS Conic Gradient จะถูก inject โดย JS --}}
              <div id="donut" class="h-9 w-9 rounded-full"
                   style="background: conic-gradient(#e2e8f0 0deg 360deg);"></div>
              <div class="absolute inset-0 m-auto h-5 w-5 rounded-full bg-white"></div>
            </div>
            <span id="donutPct" class="font-bold text-slate-700 text-xs">0%</span>
          </div>
        </div>
      </div>

      {{-- FILTERS FORM --}}
      <form method="GET"
            action="{{ route('repairs.my_jobs') }}"
            class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
            onsubmit="showLoader()">

        {{-- Search Input --}}
        <div class="md:col-span-7 min-w-0">
          <label for="q" class="mb-1 block text-[12px] text-slate-600">คำค้นหา</label>
          <div class="relative group">
            <input id="q" type="text" name="q" value="{{ $q }}"
                   placeholder="เช่น เลขแจ้งซ่อม, เลขใบงาน, เรื่อง, ทรัพย์สิน, หน่วยงาน, ผู้แจ้ง..."
                   class="w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 py-2 text-[13px] placeholder:text-slate-400
                          focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/25 focus:border-[#0F2D5C]/25 transition-all">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400 group-focus-within:text-[#0F2D5C]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
          </div>
        </div>

        {{-- Filter Select --}}
        <div class="md:col-span-2">
          <label for="filter" class="mb-1 block text-[12px] text-slate-600">ช่วงงาน</label>
          <select id="filter" name="filter"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/25 focus:border-[#0F2D5C]/25 cursor-pointer">
            @foreach($filterLabels as $key => $label)
              <option value="{{ $key }}" @selected($filter===$key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        {{-- Status Select --}}
        <div class="md:col-span-2">
          <label for="status" class="mb-1 block text-[12px] text-slate-600">สถานะ</label>
          <select id="status" name="status"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800
                         focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/25 focus:border-[#0F2D5C]/25 cursor-pointer">
            <option value="">ทุกสถานะ</option>
            @foreach(['pending','accepted','in_progress','on_hold','resolved','closed','cancelled'] as $s)
              <option value="{{ $s }}" @selected($status===$s)>{{ $statusLabel($s) }}</option>
            @endforeach
          </select>
        </div>

        {{-- Buttons --}}
        <div class="md:col-span-1 flex items-end justify-end gap-2">
          @if($hasActiveFilter)
            <a href="{{ route('repairs.my_jobs') }}"
               onclick="showLoader()"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600
                      hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/20 focus:ring-offset-1 transition-transform hover:scale-105"
               title="ล้างตัวกรอง" aria-label="ล้างตัวกรอง">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </a>
          @endif

          <button type="submit"
                  class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#0F2D5C] text-white
                         hover:bg-[#0F2D5C]/90 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/30 focus:ring-offset-1 transition-transform hover:scale-105 active:scale-95"
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

  {{-- LIST WRAPPER --}}
  <div id="mjListWrap" class="w-full px-4 md:px-6 lg:px-8 relative z-0">
    <div class="mj-container mx-auto">
      <div id="myJobsTbody" class="space-y-4">

        @forelse($list as $r)
          @php
            // เงื่อนไขปุ่มรับงาน: ยังไม่มีช่าง + สถานะ pending
            $isOpen   = empty($r->technician_id) && (($r->status ?? '') === 'pending');

            $ticketNo = $r->request_no ?? $r->job_no ?? $r->id;
            $workOrderNo = $getWorkOrderNo($r);

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
          @endphp

          {{-- JOB CARD --}}
          <div class="mj-card group">

            {{-- Card Header --}}
            {{-- [EDIT 1] เพิ่ม gap-5 md:gap-6 เพื่อเว้นระยะห่างข้อมูลชุดแรก --}}
            <div class="mj-card__header gap-5 md:gap-6">
              <div class="flex items-center gap-3 overflow-hidden min-w-0">
                <span class="mj-ticket-plain">#{{ $ticketNo }}</span>
                @if($workOrderNo)
                  <span class="mj-wo">WO# {{ $workOrderNo }}</span>
                @endif
                <h3 class="mj-title truncate" title="{{ $r->title }}">
                  {{ $r->title }}
                </h3>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                {{-- Status Pill with Ping animation if pending --}}
                <span class="mj-pill">
                  <span class="relative inline-flex h-3 w-3">
                    @if(strtolower((string)$r->status) === 'pending')
                      <span class="mj-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-60"></span>
                    @endif
                    <span class="relative inline-flex h-3 w-3 rounded-full {{ $statusDot($r->status) }}"></span>
                  </span>
                  {{ $statusText }}
                </span>
                {{-- Priority Pill --}}
                <span class="mj-pill border {{ $priorityClass($r->priority) }} font-bold uppercase">
                  {{ $priorityText }}
                </span>
              </div>
            </div>

            {{-- Sub Info (Date & Dept) --}}
            <div class="mj-card__sub">
              <div class="mj-sub-item">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="font-bold">วันแจ้ง:</span> {{ $createdAtText }}
              </div>
              <div class="mj-sub-item border-l border-slate-200 pl-4">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="font-bold">หน่วยงาน:</span> {{ $deptName ?? '-' }}
              </div>
            </div>

            {{-- Problem Detail --}}
            <div class="mj-problem-wrap">
              <h4 class="mj-cell__head mj-problem-head text-rose-700">รายละเอียดปัญหา</h4>
              @if($r->description)
                <div class="mj-problem" title="{{ $r->description }}">
                  {{ $r->description }}
                </div>
              @else
                <div class="mj-problem mj-problem--empty">
                  - ไม่มีรายละเอียด -
                </div>
              @endif
            </div>

            {{-- Info Grid (Location/Asset & Reporter) --}}
            <div class="mj-card__grid">
              <div class="mj-cell">
                <h4 class="mj-cell__head text-slate-400">สถานที่ / ทรัพย์สิน</h4>
                <div class="mj-kv">
                  <div class="truncate"><span class="mj-k">สถานที่:</span> <span class="mj-v" title="{{ $location }}">{{ $location ?? '-' }}</span></div>
                  <div class="truncate mt-1">
                    <span class="mj-k">ทรัพย์สิน:</span>
                    <span class="mj-v">
                      @if($assetCode)
                        <span class="font-mono text-xs font-bold text-[#0F2D5C]">{{ $assetCode }}</span>
                        <span class="text-slate-300">—</span>
                      @endif
                      {{ $assetName ?? '-' }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="mj-cell">
                <h4 class="mj-cell__head text-slate-400">ผู้แจ้ง / ติดต่อ</h4>
                <div class="mj-kv">
                  <div class="truncate"><span class="mj-k">ชื่อผู้แจ้ง:</span> <span class="mj-v">{{ $reporterName }}</span></div>
                  <div class="truncate mt-1"><span class="mj-k">เบอร์โทร:</span> <span class="mj-v">{{ $reporterPhone ?? '-' }}</span></div>
                  <div class="truncate mt-2 text-[10px] text-slate-400 font-mono">IP Addr: {{ $ip ?? '-' }}</div>
                </div>
              </div>
            </div>

            {{-- Card Footer (Actions) --}}
            <div class="mj-card__footer">
              <div class="mj-footer-left">
                @if($isOpen)
                  @can('accept', $r)
                    <button type="button"
                            class="mj-accept-btn group/btn"
                            onclick="openAcceptModal('{{ $r->id }}', '{{ $ticketNo }}')"
                            title="รับเรื่อง" aria-label="รับเรื่อง">
                      {{-- [EDIT 2] เปลี่ยนไอคอนเป็น Inbox In เพื่อสื่อความหมายการ "รับเข้า" --}}
                      <span class="mj-accept-ic" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M4 12V7a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                          <path d="M12 8v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                          <path d="M9.8 12.2 12 14.4l2.2-2.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M4 12l2.3 6.3A2 2 0 0 0 8.2 20h7.6a2 2 0 0 0 1.9-1.7L20 12" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                      </span>
                      <span class="mj-accept-text">รับเรื่อง</span>
                    </button>
                  @endcan
                @else
                  <div class="mj-footer-status" title="{{ $statusText }}">
                    <span class="h-2.5 w-2.5 rounded-full {{ $statusDot($r->status) }}"></span>
                    <span class="font-bold text-slate-700 text-[13px]">{{ $statusText }}</span>
                  </div>
                @endif
              </div>

              <div class="mj-footer-right">
                <a href="{{ route('maintenance.requests.show', $r) }}"
                   onclick="showLoader()"
                   class="mj-detail-btn"
                   title="ดูรายละเอียด">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <span class="hidden md:inline">รายละเอียด</span>
                </a>
              </div>
            </div>

          </div>
          {{-- END JOB CARD --}}

        @empty
          {{-- Empty State --}}
          <div class="bg-white border border-slate-200 rounded-md p-12 text-center shadow-sm">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="mt-4 text-sm text-slate-500">ไม่พบรายการงานตามเงื่อนไขที่เลือก</p>
          </div>
        @endforelse

      </div>

      {{-- Pagination --}}
      @if($list->hasPages())
        <div class="mt-8 mb-10">
          {{ $list->withQueryString()->links() }}
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Accept Modal (Popup) --}}
<div id="acceptModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 flex items-center justify-center backdrop-blur-sm px-4 transition-all">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-lg overflow-hidden border-t-4 border-emerald-600 animate-in fade-in zoom-in-95 duration-200">
    <div class="bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center">
      <h3 class="text-lg font-bold text-emerald-700 flex items-center gap-2">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="acceptModalTitle">รับเรื่อง</span>
      </h3>
      <button type="button" onclick="closeAcceptModal()" class="text-slate-400 hover:text-slate-600 transition-colors rounded-full p-1 hover:bg-slate-100">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form id="acceptForm" method="POST" action="">
      @csrf
      <input type="hidden" name="decision" id="acceptDecision" value="accepted">

      <div class="p-6 space-y-5">
        <p id="acceptModalDesc" class="text-sm text-slate-600 leading-relaxed">
          เลือก “รับเรื่อง” เพื่อนำงานเข้าคิว หรือ “กำลังดำเนินการ” หากต้องการเริ่มงานและมอบหมายช่างทันที
        </p>

        <div class="space-y-3">
          <label class="block text-sm font-bold text-slate-700">การดำเนินการ</label>

          <div class="flex flex-col gap-2">
            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-md cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
              <input type="radio" name="decision_radio" value="accepted" checked onchange="handleDecisionChange(this.value)"
                     class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
              <div class="text-sm">
                <span class="font-bold text-slate-900">รับเรื่อง (รับเข้าคิว)</span>
                <p class="text-xs text-slate-500 mt-0.5">รับทราบงานและนำเข้าสู่รายการงานของฉัน</p>
              </div>
            </label>

            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-md cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/50">
              <input type="radio" name="decision_radio" value="in_progress" onchange="handleDecisionChange(this.value)"
                     class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-slate-300">
              <div class="text-sm">
                <span class="font-bold text-slate-900">กำลังดำเนินการ (เริ่มงานทันที)</span>
                <p class="text-xs text-slate-500 mt-0.5">เปลี่ยนสถานะเป็นกำลังทำและระบุช่างผู้รับผิดชอบ</p>
              </div>
            </label>
          </div>
        </div>

        <div id="assignBox" class="border border-slate-200 rounded-md p-4 bg-slate-50 hidden animate-in slide-in-from-top-2 duration-200">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="positionSelect" class="block text-sm font-bold text-slate-700 mb-1">กรองตามตำแหน่ง</label>
              <select id="positionSelect"
                      class="w-full border-slate-300 rounded-md focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 p-2 bg-white text-sm">
                <option value="">-- ทั้งหมด --</option>
                <option value="IT Support">IT Support</option>
                <option value="Network">Network</option>
                <option value="นักพัฒนา">นักพัฒนา</option>
                <option value="ช่างทั่วไป">ช่างทั่วไป</option>
              </select>
            </div>

            <div>
              <label for="techSelect" class="block text-sm font-bold text-slate-700 mb-1">
                ช่างผู้รับผิดชอบ <span class="text-rose-500">*</span>
              </label>
              <select name="technician_id" id="techSelect"
                      class="w-full border-slate-300 rounded-md focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 p-2 bg-white text-sm">
                <option value="">-- เลือกช่าง --</option>
                @foreach($teamUsers as $u)
                  <option value="{{ $u->id }}" data-role="{{ $u->role ?? '' }}">
                    {{ $u->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
        <button type="button" onclick="closeAcceptModal()"
                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-md hover:bg-slate-100 shadow-sm transition-all active:scale-95">
          ยกเลิก
        </button>
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-md hover:bg-emerald-700 shadow-md transition-all hover:shadow-lg active:scale-95 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          บันทึก
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Fullscreen Loader --}}
<div id="loaderOverlay" class="loader-overlay">
  <div class="loader-spinner"></div>
</div>
@endsection

@push('styles')
<style>
  /* Loader Styles */
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.65);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:48px;height:48px;border:5px solid #0F2D5C;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* Container & Layout */
  .mj-container{max-width: 1200px;}
  @media (max-width:1280px){.mj-container{max-width: 1080px;}}
  @media (max-width:1024px){.mj-container{max-width: 100%;}}
  #mjListWrap{ padding-top: var(--mj-top, 18px); }

  /* Card Styles */
  .mj-card{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius: 12px;
    overflow:hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    transition: box-shadow .2s ease, transform .2s ease;
  }
  .mj-card:hover{box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04); transform: translateY(-2px);}

  .mj-card__header{
    padding: 10px 14px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    /* gap จะถูกกำหนดโดย Tailwind classes ใน HTML */
    border-bottom:1px solid #f1f5f9;
    background: #ffffff;
  }

  .mj-card__sub{
    padding: 8px 14px;
    display:flex;
    flex-wrap:wrap;
    gap: 12px;
    border-bottom:1px solid #f8fafc;
    color:#475569;
    font-size: 13px;
    background: #fcfcfd;
  }
  .mj-sub-item{display:flex;align-items:center;gap:6px}
  .mj-title{font-weight:800;font-size:15px;color:#1e293b}

  .mj-ticket-plain{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 900; font-size: 13px; color:#334155; white-space: nowrap;}
  .mj-wo{background:#f1f5f9; border:1px solid #e2e8f0; color:#475569; padding:1px 8px; font-size:11px; font-family: ui-monospace, monospace; font-weight:700; border-radius:6px; white-space:nowrap;}

  .mj-pill{
    height: 28px; padding: 0 10px; border-radius: 9999px;
    display:inline-flex; align-items:center; gap:6px;
    border:1px solid #e2e8f0; background:#fff;
    font-size: 12px; font-weight: 800; color:#334155;
    box-shadow: 0 2px 4px rgba(0,0,0,.03); white-space: nowrap;
  }
  @keyframes mjPing {0%{transform:scale(1);opacity:.6}80%{transform:scale(2.5);opacity:0}100%{transform:scale(2.5);opacity:0}}
  .mj-ping{animation:mjPing 1.5s cubic-bezier(0, 0, 0.2, 1) infinite}

  .mj-detail-btn{
    height: 34px; padding: 0 12px; border-radius: 8px;
    display:inline-flex; align-items:center; gap: 6px;
    border: 1px solid #cbd5e1; background:#fff; color:#475569;
    font-weight: 700; font-size: 13px;
    transition: all .15s ease; box-shadow: 0 2px 4px rgba(0,0,0,.03);
    text-decoration:none;
  }
  .mj-detail-btn:hover{background: #f8fafc; border-color:#94a3b8; color:#1e293b; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,.05);}
  .mj-detail-btn:active{transform: translateY(0); box-shadow: none; background: #f1f5f9;}

  .mj-problem-wrap{ padding: 10px 14px 8px 14px; }
  .mj-problem-head{ margin-bottom:4px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; }
  .mj-problem{
    border:1px solid #fda4af; border-left: 4px solid #e11d48;
    background: #fff1f2; padding: 8px 10px; border-radius:8px;
    color:#9f1239; font-size:13px; line-height:1.4;
    display:-webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow:hidden;
  }
  .mj-problem--empty{color:#9f1239;font-style: italic;background: #fff1f2; padding: 6px 10px; border-radius:8px; font-size:12px;}

  .mj-card__grid{ display:grid; grid-template-columns: 1fr 1fr; border-top: 1px solid #f1f5f9; }
  @media (max-width:1024px){.mj-card__grid{grid-template-columns:1fr}}
  .mj-cell{padding: 10px 14px; border-right:1px solid #f1f5f9}
  .mj-cell:last-child{border-right:none}
  @media (max-width:1024px){.mj-cell{border-right:none;border-top:1px solid #f1f5f9}.mj-cell:first-child{border-top:none}}
  .mj-cell__head{font-size:11px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;margin-bottom:4px; color:#64748b;}
  .mj-kv{font-size:13px;color:#1e293b}
  .mj-k{color:#64748b;font-size:12px;font-weight:700;margin-right:4px}
  .mj-v{font-weight:700}

  .mj-card__footer{
    padding: 10px 14px; border-top: 1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; gap: 10px;
    background: #fcfcfd;
  }
  .mj-footer-left{display:flex;align-items:center;gap:10px;min-width:0}
  .mj-footer-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
  .mj-footer-status{display:inline-flex;align-items:center;gap:6px; padding: 4px 10px; background: #f1f5f9; border-radius: 9999px;}

  /* Accept Button Styles */
  .mj-accept-btn{
    height: 34px; padding: 0 12px; border-radius: 8px;
    display:inline-flex; align-items:center; gap: 8px;
    font-weight: 800; font-size: 13px; color: #ffffff;
    background: #16a34a; border: 1px solid #15803d;
    box-shadow: 0 2px 4px rgba(0,0,0,.05);
    transition: all .15s ease;
    overflow: hidden; /* Important for the new animation */
  }
  .mj-accept-btn:hover{
    background: #15803d; border-color:#14532d;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(22, 163, 74, 0.25);
  }
  .mj-accept-btn:active{ transform: translateY(0); background: #14532d; box-shadow: none;}

  .mj-accept-ic{
    width: 20px; height: 20px; display:flex; align-items:center; justify-content:center;
    transition: transform .2s ease;
  }
  .mj-accept-ic svg{ width:18px; height:18px; stroke-width: 2.5; }

  /* [EDIT 3] New Hover Animation: Bounce In (เด้งลงหลุม) */
  .mj-accept-btn:hover .mj-accept-ic{
    /* ยกเลิกการหมุนเดิม แล้วใช้อนิเมชั่นใหม่ */
    transform: translateY(0);
    animation: mjBounceIn .5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 1 forwards;
  }

  @keyframes mjBounceIn {
    0% { transform: translateY(-3px); opacity: 0.7; }
    40% { transform: translateY(4px); opacity: 1; } /* ลงลึก */
    70% { transform: translateY(-2px); } /* เด้งกลับนิดหน่อย */
    100% { transform: translateY(0); } /* เข้าที่ */
  }
</style>
@endpush

@push('scripts')
<script>
  function showLoader(){ document.getElementById('loaderOverlay')?.classList.add('show') }
  function hideLoader(){ document.getElementById('loaderOverlay')?.classList.remove('show') }

  // คำนวณ padding-top เพื่อหลบ Header
  function applyListTopOffset(){
    const header = document.getElementById('stickyHeaderMJ');
    const wrap   = document.getElementById('mjListWrap');
    if(!header || !wrap) return;

    // gap ขั้นต่ำระหว่าง header กับการ์ดใบแรก
    const minGap = 16;

    // คำนวณหาจุดที่ header สิ้นสุด และจุดที่ wrap เริ่มต้น
    const headerBottom = header.getBoundingClientRect().bottom;
    // ใช้ offsetTop ของ wrap เทียบกับ viewport
    const wrapTopRelativeToViewport = wrap.getBoundingClientRect().top;

    // คำนวณส่วนต่าง ถ้า header ทับ wrap ค่านี้จะเป็นบวก
    let overlap = headerBottom - wrapTopRelativeToViewport;

    // ถ้า overlap ติดลบ แปลว่ายังไม่ชน ให้เป็น 0
    overlap = Math.max(0, overlap);

    // ค่า padding-top สุดท้ายคือ ส่วนที่ทับ + gap ที่ต้องการ
    const finalTop = overlap + minGap;

    wrap.style.setProperty('--mj-top', finalTop + 'px');
  }

  // --- Modal Functions ---
  function openAcceptModal(id, ticketNo) {
    const modal = document.getElementById('acceptModal');
    const form  = document.getElementById('acceptForm');

    // Set Action URL
    form.action = `{{ $acceptBaseUrl }}/${id}/accept`;

    // Set Title & Desc
    document.getElementById('acceptModalTitle').textContent = `รับเรื่อง #${ticketNo}`;

    // Reset Form States
    document.getElementById('acceptDecision').value = 'accepted';
    const radioAccepted = document.querySelector('input[name="decision_radio"][value="accepted"]');
    if (radioAccepted) radioAccepted.checked = true;

    // Reset Selects
    const pos = document.getElementById('positionSelect');
    const tech = document.getElementById('techSelect');
    if (pos) pos.value = '';
    if (tech) {
      tech.value = '';
      // Reset disabled/hidden options
      Array.from(tech.options).forEach(opt => opt.hidden = false);
    }

    // Apply initial UI state based on 'accepted'
    handleDecisionChange('accepted');

    // Show Modal
    modal.classList.remove('hidden');
  }

  function closeAcceptModal() {
    const modal = document.getElementById('acceptModal');
    modal.classList.add('hidden');
  }

  // Close modal when clicking outside the box
  document.getElementById('acceptModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAcceptModal();
  });

  // Handle Radio Button Change (Accepted vs In Progress)
  function handleDecisionChange(value) {
    const assignBox = document.getElementById('assignBox');
    const decision  = document.getElementById('acceptDecision');
    const tech      = document.getElementById('techSelect');

    if (!decision) return;
    decision.value = value;

    if (value === 'in_progress') {
      assignBox?.classList.remove('hidden');
      tech?.setAttribute('required', 'required');
    } else {
      assignBox?.classList.add('hidden');
      tech?.removeAttribute('required');
    }
  }

  // Filter Technician by Position role
  document.getElementById('positionSelect')?.addEventListener('change', function() {
    const role = (this.value || '').trim();
    const tech = document.getElementById('techSelect');
    if (!tech) return;

    // Reset selection first
    tech.value = '';

    Array.from(tech.options).forEach(opt => {
      if (!opt.value) return; // Skip placeholder
      const r = (opt.getAttribute('data-role') || '').trim();
      // ถ้ามี role ที่เลือก และ role ของช่างไม่ตรง ให้ซ่อน
      opt.hidden = (role && r) ? (r !== role) : false;
    });
  });

  // --- Donut Chart Renderer ---
  function renderDonut(){
    const pending = parseInt((document.getElementById('stat-pending')?.textContent||'0').replace(/,/g,''), 10) || 0;
    const inprog  = parseInt((document.getElementById('stat-in-progress')?.textContent||'0').replace(/,/g,''), 10) || 0;
    const comp    = parseInt((document.getElementById('stat-completed')?.textContent||'0').replace(/,/g,''), 10) || 0;

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

    // Colors matches tailwind classes: amber-500, sky-500, emerald-500
    donut.style.background = `conic-gradient(
      #f59e0b ${a0}deg ${a1}deg,
      #0ea5e9 ${a1}deg ${a2}deg,
      #10b981 ${a2}deg ${a3}deg,
      #e2e8f0 ${a3}deg 360deg
    )`;
  }

  // --- Init ---
  document.addEventListener('DOMContentLoaded', () => {
    hideLoader();
    renderDonut();

    // คำนวณ offset ครั้งแรก
    requestAnimationFrame(() => applyListTopOffset());

    // คำนวณใหม่เมื่อจอ resize
    window.addEventListener('resize', applyListTopOffset, { passive: true });
    window.addEventListener('scroll', applyListTopOffset, { passive: true });

    // ใช้ ResizeObserver เฝ้าดู Header เผื่อความสูงเปลี่ยน
    const header = document.getElementById('stickyHeaderMJ');
    if (window.ResizeObserver && header){
      new ResizeObserver(applyListTopOffset).observe(header);
    }
  });
</script>
@endpush
