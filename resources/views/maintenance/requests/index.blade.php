@extends('layouts.app')

@section('title','Maintenance Requests')

@section('content')
@php
  use Illuminate\Support\Str;

  $q        = $q        ?? request('q');
  $status   = $status   ?? request('status');
  $priority = $priority ?? request('priority');

  // สถานะ (โทนเดียวกับ My Jobs)
  $statusLabel = fn(?string $s) => [
    'pending'     => 'รอดำเนินการ',
    'accepted'    => 'รับงานแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
    'on_hold'     => 'พักไว้ชั่วคราว',
    'resolved'    => 'แก้ไขเสร็จสิ้น',
    'closed'      => 'ปิดงาน',
    'cancelled'   => 'ยกเลิก',
  ][strtolower((string)$s)] ?? Str::of((string)$s)->replace('_',' ')->title();

  $statusClass = fn(?string $s) => match(strtolower((string)$s)) {
    'pending'     => 'ring-1 ring-amber-300 text-amber-800 bg-white',
    'accepted'    => 'ring-1 ring-indigo-300 text-indigo-800 bg-white',
    'in_progress' => 'ring-1 ring-sky-300 text-sky-800 bg-white',
    'on_hold'     => 'ring-1 ring-zinc-300 text-zinc-700 bg-white',
    'resolved'    => 'ring-1 ring-emerald-300 text-emerald-800 bg-white',
    'closed'      => 'ring-1 ring-zinc-300 text-zinc-700 bg-zinc-50',
    'cancelled'   => 'ring-1 ring-zinc-300 text-zinc-700 bg-zinc-50',
    default       => 'ring-1 ring-zinc-300 text-zinc-700 bg-white',
  };

  // ความสำคัญ (โทนเดียวกับ My Jobs)
  $priorityClass = fn(?string $p) => match(strtolower((string)$p)) {
    'low'    => 'ring-1 ring-zinc-300 text-zinc-700 bg-white',
    'medium' => 'ring-1 ring-sky-300 text-sky-800 bg-white',
    'high'   => 'ring-1 ring-amber-300 text-amber-800 bg-white',
    'urgent' => 'ring-1 ring-rose-300 text-rose-800 bg-white',
    default  => 'ring-1 ring-zinc-300 text-zinc-700 bg-white',
  };

  $priorityLabel = fn(?string $p) => [
    'low'    => 'ต่ำ',
    'medium' => 'ปานกลาง',
    'high'   => 'สูง',
    'urgent' => 'เร่งด่วน',
  ][strtolower((string)$p)] ?? '-';
@endphp

<div class="pt-3 md:pt-4"></div>

<div class="w-full px-4 md:px-6 lg:px-8 flex flex-col gap-5">
  {{-- ===== Sticky Header + Filter Card (โทนเดียวกับ My Jobs) ===== --}}
  <div class="sticky top-[6rem] z-20 bg-slate-50/90 backdrop-blur">
    <div class="rounded-lg border border-zinc-300 bg-white shadow-sm">
      <div class="px-5 py-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
          {{-- Left: Icon + Title --}}
          <div class="flex items-start gap-3">
            <div class="grid h-9 w-9 place-items-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M6 11h12M8 15h8m-6 4h4"/>
              </svg>
            </div>
            <div>
              <h1 class="text-[17px] font-semibold text-zinc-900">Maintenance Requests</h1>
              <p class="text-[13px] text-zinc-600">
                รายการคำขอบำรุงรักษา • ค้นหา กรอง และตรวจทานคำขอ
              </p>
            </div>
          </div>

          {{-- Right: Create button (ขวาบนเหมือนเดิม) --}}
          <div class="flex shrink-0 items-center">
            <a href="{{ route('maintenance.requests.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-emerald-700 bg-emerald-700 px-4 py-2 text-[13px] font-medium text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600"
               onclick="showLoader()">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
              </svg>
              สร้างคำขอใหม่
            </a>
          </div>
        </div>

        <div class="mt-4 h-px bg-zinc-200"></div>

        {{-- Search / Filters --}}
        <form method="GET"
              action="{{ route('maintenance.requests.index') }}"
              class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12"
              onsubmit="showLoader()">

          {{-- Search --}}
          <div class="md:col-span-6 min-w-0">
            <label for="q" class="mb-1 block text-[12px] text-zinc-600">คำค้นหา</label>
            <div class="relative">
              <input id="q" type="text" name="q" value="{{ $q }}"
                     placeholder="เช่น เลขใบงาน 68xxxx, ชื่อเรื่อง, ชื่อผู้แจ้ง, เบอร์, อีเมล"
                     class="w-full rounded-md border border-zinc-300 pl-10 pr-3 py-2 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-zinc-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </span>
            </div>
          </div>

          {{-- Status --}}
          <div class="md:col-span-3">
            <label for="status" class="mb-1 block text-[12px] text-zinc-600">สถานะ</label>
            <select id="status" name="status"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              <option value="">ทุกสถานะ</option>
              @foreach ([
                'pending'     => 'รอดำเนินการ',
                'accepted'    => 'รับงานแล้ว',
                'in_progress' => 'กำลังดำเนินการ',
                'on_hold'     => 'พักไว้ชั่วคราว',
                'resolved'    => 'แก้ไขเสร็จสิ้น',
                'closed'      => 'ปิดงาน',
                'cancelled'   => 'ยกเลิก',
              ] as $k=>$v)
                <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>

          {{-- Priority --}}
          <div class="md:col-span-2">
            <label for="priority" class="mb-1 block text-[12px] text-zinc-600">ความสำคัญ</label>
            <select id="priority" name="priority"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              <option value="">ทั้งหมด</option>
              @foreach (['low'=>'ต่ำ','medium'=>'ปานกลาง','high'=>'สูง','urgent'=>'เร่งด่วน'] as $k=>$v)
                <option value="{{ $k }}" @selected($priority===$k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>

          {{-- Buttons --}}
          <div class="md:col-span-1 flex items-end gap-2">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-md border border-emerald-700 bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              ค้นหา
            </button>
            @if(request()->hasAny(['q','status','priority']))
              <a href="{{ route('maintenance.requests.index') }}"
                 class="inline-flex items-center justify-center rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                 onclick="showLoader()">
                ล้างค่า
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ===== Table Card (โทนเดียวกับ My Jobs) ===== --}}
  <div class="rounded-lg border border-zinc-300 bg-white overflow-hidden">
    <div class="relative overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-zinc-50 border-b border-zinc-200">
          <tr class="text-zinc-700">
            <th class="p-3 text-left font-medium w-[10%] whitespace-nowrap">เลขใบงาน</th>
            <th class="p-3 text-left font-medium w-[30%]">เรื่อง</th>
            <th class="p-3 text-left font-medium w-[18%]">ผู้แจ้ง</th>
            <th class="p-3 text-left font-medium w-[14%]">หน่วยงาน</th>
            <th class="p-3 text-left font-medium w-[10%]">ความสำคัญ</th>
            <th class="p-3 text-left font-medium w-[10%]">สถานะ</th>
            <th class="p-3 text-center font-medium whitespace-nowrap min-w-[200px]">การดำเนินการ</th>
          </tr>
        </thead>

        <tbody class="bg-white">
        @forelse($list as $row)
          <tr class="align-top hover:bg-zinc-50 border-b last:border-0">
            {{-- เลขใบงาน --}}
            <td class="p-3 align-middle text-zinc-700 whitespace-nowrap">
              @if($row->request_no)
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 ring-1 ring-emerald-200">
                  {{ $row->request_no }}
                </span>
              @else
                <span class="text-zinc-500 text-xs">#{{ $row->id }}</span>
              @endif
            </td>

            {{-- เรื่อง + รายละเอียด --}}
            <td class="p-3">
              <a href="{{ route('maintenance.requests.show', $row) }}"
                 class="block max-w-full truncate font-medium text-zinc-900 hover:underline"
                 onclick="showLoader()">
                {{ Str::limit($row->title, 90) }}
              </a>
              @if($row->description)
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">
                  {{ Str::limit($row->description, 140) }}
                </p>
              @endif

              @if(!empty($row->category))
                <div class="mt-2 flex flex-wrap gap-2">
                  <span class="rounded-full bg-white px-2 py-1 text-[11px] ring-1 ring-zinc-300 text-zinc-700">
                    {{ $row->category }}
                  </span>
                </div>
              @endif
            </td>

            {{-- ผู้แจ้ง --}}
            <td class="p-3 text-zinc-700 align-middle">
              @php
                $reporterName = $row->reporter_name ?? $row->reporter?->name;
                $reporterEmail = $row->reporter_email ?? $row->reporter?->email;
                $reporterPhone = $row->reporter_phone;
              @endphp

              <div class="text-[13px] font-medium text-zinc-900">
                {{ $reporterName ?? '—' }}
                @if($row->reporter_position)
                  <span class="text-[11px] text-zinc-500">• {{ $row->reporter_position }}</span>
                @endif
              </div>
              @if($reporterEmail || $reporterPhone)
                <div class="mt-0.5 text-[11px] text-zinc-500 space-x-1">
                  @if($reporterEmail)
                    <span>{{ $reporterEmail }}</span>
                  @endif
                  @if($reporterPhone)
                    <span>• {{ $reporterPhone }}</span>
                  @endif
                </div>
              @endif
            </td>

            @php
              $deptName = $row->department->name
                          ?? $row->asset?->department?->name
                          ?? '—';
            @endphp
            <td class="p-3 text-zinc-700 align-middle">{{ $deptName }}</td>

            <td class="p-3 align-middle">
              <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] {{ $priorityClass($row->priority ?? null) }}">
                {{ $priorityLabel($row->priority ?? null) }}
              </span>
            </td>

            <td class="p-3 align-middle">
              <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] {{ $statusClass($row->status ?? null) }}">
                {{ $statusLabel($row->status ?? null) }}
              </span>
            </td>

            <td class="p-3 text-center whitespace-nowrap align-middle">
              <div class="h-full flex justify-center items-center gap-2">
                <a href="{{ route('maintenance.requests.show', $row) }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 bg-white px-2.5 md:px-3 py-1.5 text-[11px] md:text-xs font-medium text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-600 whitespace-nowrap min-w-[84px] justify-center"
                   onclick="showLoader()" aria-label="ดูรายละเอียด">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                  </svg>
                  <span class="hidden sm:inline">ดูรายละเอียด</span>
                  <span class="sm:hidden">ดู</span>
                </a>
                <a href="{{ route('maintenance.requests.edit', $row) }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 bg-white px-2.5 md:px-3 py-1.5 text-[11px] md:text-xs font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 whitespace-nowrap min-w-[74px] justify-center"
                   onclick="showLoader()" aria-label="แก้ไข">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                  </svg>
                  <span class="hidden sm:inline">แก้ไข</span>
                  <span class="sm:hidden">แก้ไข</span>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="p-12 text-center text-zinc-600">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</p>
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

@section('after-content')
<div id="loaderOverlay" class="loader-overlay">
  <div class="loader-spinner"></div>
</div>
<style>
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:38px;height:38px;border:4px solid #0E2B51;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>
<script>
  function showLoader(){document.getElementById('loaderOverlay').classList.add('show')}
  function hideLoader(){document.getElementById('loaderOverlay').classList.remove('show')}
  document.addEventListener('DOMContentLoaded', hideLoader);
</script>
@endsection
