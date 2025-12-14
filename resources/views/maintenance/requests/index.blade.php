@extends('layouts.app')

@section('title','Maintenance Requests')

@section('content')
@php
  use Illuminate\Support\Str;

  $q        = $q        ?? request('q');
  $status   = $status   ?? request('status');
  $priority = $priority ?? request('priority');

  // sort from controller (fallback from request)
  $sortBy  = $sortBy  ?? request('sort_by', 'request_no');
  $sortDir = $sortDir ?? request('sort_dir', 'desc');

  $statusLabel = fn(?string $s) => [
    'pending'     => 'รอดำเนินการ',
    'accepted'    => 'รับงานแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
    'on_hold'     => 'พักไว้ชั่วคราว',
    'resolved'    => 'แก้ไขเสร็จสิ้น',
    'closed'      => 'ปิดงาน',
    'cancelled'   => 'ยกเลิก',
  ][strtolower((string)$s)] ?? Str::of((string)$s)->replace('_',' ')->title();

  $statusTextClass = fn(?string $s) => match(strtolower((string)$s)) {
    'pending'     => 'text-amber-700',
    'accepted'    => 'text-indigo-700',
    'in_progress' => 'text-sky-700',
    'on_hold'     => 'text-slate-600',
    'resolved'    => 'text-emerald-700',
    'closed'      => 'text-zinc-500',
    'cancelled'   => 'text-rose-700',
    default       => 'text-slate-700',
  };

  $priorityTextClass = fn(?string $p) => match(strtolower((string)$p)) {
    'low'    => 'text-zinc-500',
    'medium' => 'text-sky-700',
    'high'   => 'text-amber-700',
    'urgent' => 'text-rose-700',
    default  => 'text-slate-700',
  };

  $priorityLabel = fn(?string $p) => [
    'low'    => 'ต่ำ',
    'medium' => 'ปานกลาง',
    'high'   => 'สูง',
    'urgent' => 'เร่งด่วน',
  ][strtolower((string)$p)] ?? '-';

  $statusOptions = [
    'pending'     => 'รอดำเนินการ',
    'accepted'    => 'รับงานแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
    'on_hold'     => 'พักไว้ชั่วคราว',
    'resolved'    => 'แก้ไขเสร็จสิ้น',
    'closed'      => 'ปิดงาน',
    'cancelled'   => 'ยกเลิก',
  ];

  $priorityOptions = [
    'low'    => 'ต่ำ',
    'medium' => 'ปานกลาง',
    'high'   => 'สูง',
    'urgent' => 'เร่งด่วน',
  ];
@endphp

<div class="pt-6 md:pt-8 lg:pt-10"></div>

<div class="w-full flex flex-col">

  {{-- ✅ Sticky Header + Filters (อนุญาตพื้นหลังได้เฉพาะส่วนนี้) --}}
  <div class="sticky top-[6rem] z-20 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="px-4 md:px-6 lg:px-8 py-4">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-[17px] font-semibold text-slate-900">Maintenance Requests</h1>
          <p class="text-[13px] text-slate-600">รายการคำขอบำรุงรักษา • ค้นหา กรอง และตรวจทานคำขอ</p>
        </div>

        <a href="{{ route('maintenance.requests.create') }}"
           class="inline-flex items-center gap-2 rounded-md bg-[#0F2D5C] px-4 py-2 text-[13px] font-medium text-white hover:bg-[#0F2D5C]/90 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/40"
           onclick="showLoader()">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
          </svg>
          สร้างคำขอใหม่
        </a>
      </div>

      <form method="GET"
            action="{{ route('maintenance.requests.index') }}"
            class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
            onsubmit="showLoader()">

        {{-- keep sort --}}
        <input type="hidden" name="sort_by"  value="{{ $sortBy }}">
        <input type="hidden" name="sort_dir" value="{{ $sortDir }}">

        <div class="md:col-span-5 min-w-0">
          <label for="q" class="mb-1 block text-[12px] text-slate-600">คำค้นหา</label>
          <div class="relative">
            <input id="q" type="text" name="q" value="{{ $q }}"
                   placeholder="เช่น เลขใบงาน 68xxxx, ชื่อเรื่อง, ชื่อผู้แจ้ง, เบอร์, อีเมล"
                   class="w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 py-2 text-[13px] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
          </div>
        </div>

        <div class="md:col-span-2">
          <label for="status" class="mb-1 block text-[12px] text-slate-600">สถานะ</label>
          <select id="status" name="status"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            <option value="">ทุกสถานะ</option>
            @foreach ($statusOptions as $k => $v)
              <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-3">
          <label for="priority" class="mb-1 block text-[12px] text-slate-600">ความสำคัญ</label>
          <select id="priority" name="priority"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            <option value="">ทั้งหมด</option>
            @foreach ($priorityOptions as $k => $v)
              <option value="{{ $k }}" @selected($priority === $k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-2 flex items-end justify-end gap-2">
          <a href="{{ route('maintenance.requests.index') }}"
             onclick="showLoader()"
             class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/30 focus:ring-offset-1"
             title="ล้างตัวกรอง" aria-label="ล้างตัวกรอง">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </a>

          <button type="submit"
                  class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#0F2D5C] text-white hover:bg-[#0F2D5C]/90 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/45 focus:ring-offset-1"
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

  {{-- ✅ แผงหัวข้อรายการ (เหมือน Asset / ไม่ใส่พื้นหลัง) --}}
  <div class="px-4 md:px-6 lg:px-8 py-2 border-b border-slate-200">
    <div class="flex items-center justify-between">
      <div class="text-[13px] font-semibold text-slate-800">
        รายการคำขอบำรุงรักษา
      </div>
      <div class="text-[12px] text-slate-500">
        ทั้งหมด {{ $list->total() }} รายการ
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-[13px]">
      <thead class="bg-white">
        <tr class="text-slate-600">

          {{-- ✅ Sort เหมือน Asset: inline + icon ตลอด --}}
          <th class="p-3 text-center font-semibold w-[10%] whitespace-nowrap border-b border-slate-200">
            @php
              $isActive = ($sortBy === 'request_no');
              $nextDir  = ($isActive && $sortDir === 'asc') ? 'desc' : 'asc';
              $sortUrl  = request()->fullUrlWithQuery(['sort_by' => 'request_no', 'sort_dir' => $nextDir]);

              $labelCls = $isActive ? 'text-[#0F2D5C]' : 'text-slate-600 group-hover:text-slate-900';
              $iconCls  = $isActive ? 'text-[#0F2D5C]' : 'text-slate-300 group-hover:text-slate-400';

              $iconPathAsc  = 'M12 7l-4 6h8l-4-6z';
              $iconPathDesc = 'M12 17l4-6H8l4 6z';
              $iconPath = ($isActive && $sortDir === 'asc') ? $iconPathAsc : $iconPathDesc;
            @endphp

            <a href="{{ $sortUrl }}"
               onclick="showLoader()"
               class="inline-flex items-center justify-center gap-1.5 group select-none">
              <span class="text-[13px] font-semibold whitespace-nowrap {{ $labelCls }}">เลขใบงาน</span>
              <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 {{ $iconCls }}">
                <path d="{{ $iconPath }}" fill="currentColor"/>
              </svg>
            </a>
          </th>

          <th class="p-3 text-center font-semibold w-[30%] border-b border-slate-200">เรื่อง</th>
          <th class="p-3 text-center font-semibold w-[18%] border-b border-slate-200">ผู้แจ้ง</th>
          <th class="p-3 text-center font-semibold w-[14%] border-b border-slate-200">หน่วยงาน</th>
          <th class="p-3 text-center font-semibold w-[10%] whitespace-nowrap border-b border-slate-200">ความสำคัญ</th>
          <th class="p-3 text-center font-semibold w-[10%] whitespace-nowrap border-b border-slate-200">สถานะ</th>
          <th class="p-3 text-center font-semibold whitespace-nowrap min-w-[200px] border-b border-slate-200">การดำเนินการ</th>
        </tr>
      </thead>

      <tbody class="bg-white">
      @forelse($list as $row)
        <tr class="align-top border-b border-slate-100 hover:bg-slate-50/60">
          <td class="p-3 align-middle whitespace-nowrap text-center font-semibold text-slate-900">
            {{ $row->request_no ?: ('#'.$row->id) }}
          </td>

          <td class="p-3">
            <a href="{{ route('maintenance.requests.show', $row) }}"
               class="block max-w-full truncate font-semibold text-slate-900 hover:underline"
               onclick="showLoader()">
              {{ Str::limit($row->title, 90) }}
            </a>
            @if($row->description)
              <p class="mt-1 text-[12px] leading-relaxed text-slate-600">
                {{ Str::limit($row->description, 140) }}
              </p>
            @endif
            @if(!empty($row->category))
              <p class="mt-2 text-[11px] text-slate-500">
                หมวดหมู่: <span class="font-medium text-slate-700">{{ $row->category }}</span>
              </p>
            @endif
          </td>

          <td class="p-3 align-middle">
            @php
              $reporterName  = $row->reporter_name ?? $row->reporter?->name;
              $reporterEmail = $row->reporter_email ?? $row->reporter?->email;
              $reporterPhone = $row->reporter_phone;
            @endphp
            <div class="text-[13px] font-semibold text-slate-900">
              {{ $reporterName ?? '—' }}
              @if($row->reporter_position)
                <span class="text-[11px] text-slate-500">• {{ $row->reporter_position }}</span>
              @endif
            </div>
            @if($reporterEmail || $reporterPhone)
              <div class="mt-0.5 text-[11px] text-slate-500 space-x-1">
                @if($reporterEmail)<span>{{ $reporterEmail }}</span>@endif
                @if($reporterPhone)<span>• {{ $reporterPhone }}</span>@endif
              </div>
            @endif
          </td>

          @php
            $deptName = $row->department->name
                        ?? $row->asset?->department?->name
                        ?? '—';
          @endphp
          <td class="p-3 align-middle text-center text-slate-700">
            {{ $deptName }}
          </td>

          <td class="p-3 align-middle whitespace-nowrap text-center">
            <span class="text-[12px] font-semibold {{ $priorityTextClass($row->priority ?? null) }}">
              {{ $priorityLabel($row->priority ?? null) }}
            </span>
          </td>

          <td class="p-3 align-middle whitespace-nowrap text-center">
            <span class="text-[12px] font-semibold {{ $statusTextClass($row->status ?? null) }}">
              {{ $statusLabel($row->status ?? null) }}
            </span>
          </td>

          <td class="p-3 text-center whitespace-nowrap align-middle">
            <div class="h-full flex justify-center items-center gap-2">
              <a href="{{ route('maintenance.requests.show', $row) }}"
                 class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 bg-white px-2.5 md:px-3 py-1.5 text-[12px] font-medium text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-600 whitespace-nowrap min-w-[96px] justify-center"
                 onclick="showLoader()">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                </svg>
                ดูรายละเอียด
              </a>

              <a href="{{ route('maintenance.requests.edit', $row) }}"
                 class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 bg-white px-2.5 md:px-3 py-1.5 text-[12px] font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 whitespace-nowrap min-w-[80px] justify-center"
                 onclick="showLoader()">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                </svg>
                แก้ไข
              </a>
            </div>
          </td>
        </tr>
      @empty
        @php
          $hasFilter =
            (($q ?? null) && $q !== '') ||
            (($status ?? null) && $status !== '') ||
            (($priority ?? null) && $priority !== '');
        @endphp
        <tr>
          <td colspan="7" class="py-16 text-center text-slate-600">
            <div class="flex flex-col items-center gap-2">
              <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              @if($hasFilter)
                <p class="text-[13px]">ไม่พบคำขอบำรุงรักษาตามเงื่อนไขที่เลือก</p>
              @else
                <p class="text-[13px]">ตอนนี้ยังไม่มีคำขอบำรุงรักษาในระบบ</p>
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

<style>
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:38px;height:38px;border:4px solid #0F2D5C;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
  function showLoader(){ document.getElementById('loaderOverlay')?.classList.add('show') }
  function hideLoader(){ document.getElementById('loaderOverlay')?.classList.remove('show') }
  document.addEventListener('DOMContentLoaded', hideLoader);
</script>
@endsection
