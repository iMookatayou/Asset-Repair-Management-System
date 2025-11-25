@extends('layouts.app')

@section('title', 'Asset Repair Dashboard — Compact')

@section('content')

  @php
    $toast = session('toast');
    if ($toast) { session()->forget('toast'); } // ใช้ครั้งเดียว

    // base fields
    $type     = $toast['type']     ?? null;   // success|info|warning|error
    $message  = $toast['message']  ?? null;
    $position = $toast['position'] ?? 'center';  // tr|tl|br|bl|center|tc|bc
    $timeout  = (int)($toast['timeout'] ?? 3200);
    $size     = $toast['size']     ?? 'lg';      // sm|md|lg

    // map error/status → toast อัตโนมัติ
    $firstError = ($errors ?? null)?->first();
    if (!$message && $firstError) { $message = $firstError; $type = $type ?: 'error'; }
    if (!$message && session('error')) { $message = session('error'); $type = $type ?: 'error'; }
    if (!$message && session('status')) { $message = session('status'); $type = $type ?: 'success'; }
  @endphp

  @php
    $monthlyTrend = is_iterable($monthlyTrend ?? null) ? collect($monthlyTrend) : collect();
    $byAssetType  = is_iterable($byAssetType  ?? null) ? collect($byAssetType) : collect();
    $byDept       = is_iterable($byDept       ?? null) ? collect($byDept)       : collect();
    $recent       = is_iterable($recent       ?? null) ? collect($recent)       : collect();

    // แสดงแนวโน้มทุกเดือนที่มีข้อมูล ไม่ตัดเหลือ 6 เดือนแล้ว
    $monthlyTrend = $monthlyTrend->values();
    // ดึงทุกหมวดหมู่ / ทุกแผนก ไม่ตัด Top แล้ว
    $byAssetType  = $byAssetType->values();
    $byDept       = $byDept->values();

    $intVal   = fn($v)=> is_numeric($v) ? (int)$v : 0;
    $strVal   = fn($v,$f='')=> is_string($v) && $v!=='' ? $v : $f;

    $trendLabels = $monthlyTrend->map(fn($i)=> $strVal(is_array($i)?($i['ym']??''):($i->ym??'')))->all();
    $trendCounts = $monthlyTrend->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $typeLabels  = $byAssetType->map(fn($i)=> $strVal(is_array($i)?($i['type']??'Unspecified'):($i->type??'Unspecified'),'Unspecified'))->all();
    $typeCounts  = $byAssetType->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $deptLabels  = $byDept->map(fn($i)=> $strVal(is_array($i)?($i['dept']??'Unspecified'):($i->dept??'Unspecified'),'Unspecified'))->all();
    $deptCounts  = $byDept->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $get = function($row, $key, $fallback='-'){
      if (is_array($row))  return data_get($row, $key, $fallback);
      if (is_object($row)) return data_get((array)$row, $key, $fallback);
      return $fallback;
    };

    // เปิดแผง Filters อัตโนมัติถ้ามีพารามิเตอร์กรอง
    $filtersActive = (string)request('status','') !== '' || (string)request('from','') !== '' || (string)request('to','') !== '';

    // สี pill สำหรับสถานะ
    $statusPillClass = function (string $status): string {
      return match ($status) {
        \App\Models\MaintenanceRequest::STATUS_PENDING     => 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-200',
        \App\Models\MaintenanceRequest::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-800 ring-1 ring-inset ring-sky-200',
        \App\Models\MaintenanceRequest::STATUS_COMPLETED   => 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-200',
        default                                            => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200',
      };
    };
  @endphp

  {{-- พื้นหลัง dashboard แบบราชการสะอาด ๆ --}}
  <div class="bg-slate-50/90">
    <div class="px-4 sm:px-6 lg:px-10 2xl:px-20 pt-3 pb-8 space-y-5">

      {{-- HEADER รายงาน + FILTER PANEL (sticky เลื่อนตามลงมาเต็มดุ้นใต้ Navbar) --}}
      <div class="sticky z-30" style="top: var(--topbar-h, 4rem);">
        <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
          <div class="flex flex-wrap items-start gap-4 px-4 py-3 sm:px-5 sm:py-4">

            {{-- กล่องไอคอนหัวรายงาน --}}
            <div class="hidden sm:flex">
              <div class="h-11 w-11 rounded-xl bg-emerald-600/95 grid place-items-center">
                <svg class="h-5 w-5 text-emerald-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M4 19V5a2 2 0 012-2h6.5M10 21h8a2 2 0 002-2V7.5a2 2 0 00-.586-1.414l-3.5-3.5A2 2 0 0014.5 2H12" />
                  <path d="M8 13.5L10.5 16 16 10.5" />
                </svg>
              </div>
            </div>

            <div class="flex-1 min-w-0">
              {{-- แคปชันบนหัวรายงาน --}}
              <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] text-emerald-50">
                  DB
                </span>
                <span>รายงานสรุปภาพรวมการแจ้งซ่อมครุภัณฑ์</span>
              </div>

              <h1 class="mt-2 text-base sm:text-lg md:text-xl font-semibold text-zinc-900 leading-snug">
                รายงานภาพรวมการแจ้งซ่อมครุภัณฑ์ของโรงพยาบาลพระปกเกล้า
              </h1>

              <p class="mt-1 text-xs text-zinc-600 max-w-3xl">
                จัดทำโดยกลุ่มงานเทคโนโลยีสารสนเทศ เพื่อแสดงภาพรวมภาระงานซ่อมบำรุงครุภัณฑ์ แยกตามสถานะ หน่วยงาน และช่วงเวลา
                สำหรับใช้ประกอบการติดตามงานและรายงานต่อผู้บริหาร
              </p>
            </div>

            {{-- มุมขวา: ปุ่มตัวกรอง + วันที่ --}}
            <div class="ms-auto flex flex-col items-end gap-2">
              <div class="text-[11px] text-emerald-900/80">
                วันที่ออกรายงาน:
                <span class="font-semibold">{{ now()->format('d/m/Y') }}</span>
              </div>

              <div class="flex items-center gap-2">
                @if ($filtersActive)
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200">
                    ใช้ตัวกรองอยู่
                  </span>
                @endif

                <button id="filterToggle" type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50"
                        aria-expanded="{{ $filtersActive ? 'true' : 'false' }}"
                        aria-controls="filtersPanel">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4h18M6 8h12M9 12h6M11 16h2"/>
                  </svg>
                  ตัวกรองข้อมูล
                </button>
              </div>
            </div>
          </div>

          {{-- แถบเมต้าใต้หัวข้อ --}}
          <div class="border-t border-emerald-100 bg-emerald-50/70 px-4 py-1.5 sm:px-5 flex flex-wrap items-center gap-2 justify-between">
            <div class="text-[11px] text-emerald-900">
              ข้อมูลนี้เป็นภาพรวมเชิงสถิติของระบบแจ้งซ่อมครุภัณฑ์ ณ ปัจจุบัน
            </div>
            <div class="text-[11px] text-emerald-900/80">
              ระบบ: Asset Repair Management — Phrapokklao Hospital
            </div>
          </div>

          {{-- FILTER PANEL: พื้นหลังเทาอ่อน --}}
          <div
            id="filtersPanel"
            class="border-t border-dashed border-zinc-200 bg-zinc-50 px-4 py-3 sm:px-5 sm:py-4 {{ $filtersActive ? '' : 'hidden' }}"
          >
            <div class="flex flex-col gap-1">
              <h2 class="text-xs font-semibold text-zinc-900">
                ตัวกรองข้อมูลภาพรวมงานซ่อม
              </h2>
              <p class="mt-0.5 text-[11px] text-zinc-600 max-w-xl">
                เลือกช่วงเวลาและสถานะ เพื่อดูเฉพาะงานซ่อมที่สนใจ
              </p>
            </div>

            <form method="GET" class="mt-3">
              <div class="grid grid-cols-2 gap-3 md:grid-cols-6">
                <div class="md:col-span-2">
                  <label for="f_status" class="block text-xs font-medium text-zinc-800">สถานะงานซ่อม</label>
                  <select id="f_status" name="status"
                          class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">ทั้งหมด</option>
                    <option value="pending"     {{ request('status')==='pending'?'selected':'' }}>รอดำเนินการ</option>
                    <option value="in_progress" {{ request('status')==='in_progress'?'selected':'' }}>กำลังดำเนินการ</option>
                    <option value="completed"   {{ request('status')==='completed'?'selected':'' }}>เสร็จสิ้น</option>
                  </select>
                </div>

                <div>
                  <label for="f_from" class="block text-xs font-medium text-zinc-800">จากวันที่ (From)</label>
                  <input id="f_from" type="date" name="from" value="{{ e(request('from','')) }}"
                         class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div>
                  <label for="f_to" class="block text-xs font-medium text-zinc-800">ถึงวันที่ (To)</label>
                  <input id="f_to" type="date" name="to" value="{{ e(request('to','')) }}"
                         class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div class="md:col-span-2 flex items-end gap-2 justify-end">
                  <button class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/30">
                    ค้นหาตามเงื่อนไข
                  </button>
                  <a href="{{ route('repair.dashboard') }}"
                     class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">
                    ล้างตัวกรอง
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- CARD หลักของ Dashboard --}}
      <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="px-4 py-4 sm:px-6 sm:py-5 space-y-6">

          {{-- ส่วนที่ 1: ภาพรวมสถิติ + กราฟสถานะ --}}
          <div class="space-y-4">
            <div class="flex flex-col gap-1">
              <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] text-emerald-50">
                  1
                </span>
                <span>ส่วนที่ 1 • ภาพรวมสถิติ</span>
              </div>
              <h2 class="text-sm sm:text-base font-semibold text-zinc-900">
                ภาพรวมจำนวนงานซ่อมในระบบ
              </h2>
              <p class="mt-0.5 text-xs text-zinc-500 max-w-2xl">
                แสดงจำนวนงานซ่อมทั้งหมดและจำแนกตามสถานะหลัก เพื่อเห็นปริมาณงานในระบบโดยรวม
              </p>
            </div>

            {{-- การ์ดสถิติ 4 ใบ เต็มความกว้าง --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

              {{-- TOTAL JOBS --}}
              <article class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-zinc-400"></div>
                <div class="flex items-start justify-between gap-3 px-4 pt-3 pb-1 pl-5">
                  <div>
                    <p class="text-[11px] font-semibold tracking-[0.12em] text-zinc-500 uppercase">
                      TOTAL JOBS
                    </p>
                    <p class="mt-1 text-3xl font-semibold text-zinc-900 leading-tight">
                      {{ number_format($stats['total'] ?? 0) }}
                    </p>
                  </div>
                  <div class="shrink-0 rounded-full border border-zinc-200 bg-zinc-50 p-2">
                    <svg class="h-5 w-5 text-zinc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <path d="M4 4h16v4H4z" />
                      <path d="M4 10h10v4H4z" />
                      <path d="M4 16h7v4H4z" />
                    </svg>
                  </div>
                </div>
                <div class="px-4 pb-3 pt-1 pl-5">
                  <p class="text-[11px] text-zinc-500">
                    จำนวนงานซ่อมทั้งหมดที่บันทึกในระบบ (ทุกสถานะ)
                  </p>
                </div>
              </article>

              {{-- PENDING --}}
              <article class="relative overflow-hidden rounded-xl border border-amber-200 bg-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-amber-500"></div>
                <div class="flex items-start justify-between gap-3 px-4 pt-3 pb-1 pl-5">
                  <div>
                    <p class="text-[11px] font-semibold tracking-[0.12em] text-amber-700 uppercase">
                      PENDING
                    </p>
                    <p class="mt-1 text-3xl font-semibold text-amber-700 leading-tight">
                      {{ number_format($stats['pending'] ?? 0) }}
                    </p>
                  </div>
                  <div class="shrink-0 rounded-full border border-amber-200 bg-amber-50 p-2">
                    <svg class="h-5 w-5 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <circle cx="12" cy="12" r="9" />
                      <path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <div class="px-4 pb-3 pt-1 pl-5">
                  <p class="text-[11px] text-amber-800/90">
                    งานที่ยังรอรับ/รอดำเนินการจากช่างหรือหน่วยงานที่เกี่ยวข้อง
                  </p>
                </div>
              </article>

              {{-- IN PROGRESS --}}
              <article class="relative overflow-hidden rounded-xl border border-sky-200 bg-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-sky-500"></div>
                <div class="flex items-start justify-between gap-3 px-4 pt-3 pb-1 pl-5">
                  <div>
                    <p class="text-[11px] font-semibold tracking-[0.12em] text-sky-700 uppercase">
                      IN PROGRESS
                    </p>
                    <p class="mt-1 text-3xl font-semibold text-sky-700 leading-tight">
                      {{ number_format($stats['inProgress'] ?? 0) }}
                    </p>
                  </div>
                  <div class="shrink-0 rounded-full border border-sky-200 bg-sky-50 p-2">
                    <svg class="h-5 w-5 text-sky-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <path d="M3 12h4l3 7 4-14 3 7h4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <div class="px-4 pb-3 pt-1 pl-5">
                  <p class="text-[11px] text-sky-800/90">
                    งานที่ช่างกำลังดำเนินการตรวจสอบ แก้ไข หรือรอดำเนินการขั้นต่อไป
                  </p>
                </div>
              </article>

              {{-- COMPLETED --}}
              <article class="relative overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-emerald-600"></div>
                <div class="flex items-start justify-between gap-3 px-4 pt-3 pb-1 pl-5">
                  <div>
                    <p class="text-[11px] font-semibold tracking-[0.12em] text-emerald-700 uppercase">
                      COMPLETED
                    </p>
                    <p class="mt-1 text-3xl font-semibold text-emerald-700 leading-tight">
                      {{ number_format($stats['completed'] ?? 0) }}
                    </p>
                  </div>
                  <div class="shrink-0 rounded-full border border-emerald-200 bg-emerald-50 p-2">
                    <svg class="h-5 w-5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <circle cx="12" cy="12" r="9" />
                      <path d="M8.5 12.5L11 15l4.5-5.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <div class="px-4 pb-3 pt-1 pl-5">
                  <p class="text-[11px] text-emerald-800/90">
                    งานที่ดำเนินการเสร็จเรียบร้อยและบันทึกผลในระบบแล้ว
                  </p>
                </div>
              </article>
            </div>

            {{-- กราฟภาพรวมสถานะ (Bar แนวนอน) เต็มความกว้าง --}}
            <div class="rounded-xl border border-zinc-200 bg-zinc-50/80 px-4 py-4 sm:px-5 sm:py-4 mt-1">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-[11px] font-medium text-zinc-700">
                    กราฟที่ 1 — ภาพรวมสถานะงานซ่อม
                  </div>
                  <h3 class="mt-2 text-sm sm:text-base font-semibold text-zinc-900">
                    เทียบจำนวนงานซ่อมตามสถานะ (Total / Pending / In Progress / Completed)
                  </h3>
                  <p class="mt-0.5 text-xs text-zinc-500 max-w-xl">
                    แสดงจำนวนงานซ่อมในแต่ละสถานะในรูปแบบกราฟแท่งแนวนอน เพื่อเปรียบเทียบได้ชัดเจน
                  </p>
                </div>
              </div>

              <div class="mt-4 h-56 w-full">
                <canvas
                  id="statusTrend"
                  class="block w-full h-full"
                  data-pending="{{ $stats['pending'] ?? 0 }}"
                  data-progress="{{ $stats['inProgress'] ?? 0 }}"
                  data-completed="{{ $stats['completed'] ?? 0 }}"
                  data-total="{{ $stats['total'] ?? 0 }}"
                ></canvas>
              </div>
            </div>
          </div>

          {{-- Divider --}}
          <div class="h-px bg-gradient-to-r from-transparent via-zinc-200 to-transparent"></div>

          {{-- ส่วนที่ 2: กราฟ & การวิเคราะห์ --}}
          <div class="space-y-4">
            <div class="flex flex-col gap-1">
              <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-semibold text-indigo-700">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] text-indigo-50">
                  2
                </span>
                <span>ส่วนที่ 2 • กราฟและการวิเคราะห์</span>
              </div>
              <h2 class="text-sm sm:text-base font-semibold text-zinc-900">
                กราฟภาพรวมงานซ่อมตามหน่วยงาน ช่วงเวลา และหมวดหมู่ครุภัณฑ์
              </h2>
            </div>

            {{-- กราฟตามหน่วยงาน (Bar chart) --}}
            <div class="rounded-xl border border-zinc-200 bg-white/80 px-4 py-4 sm:px-5 sm:py-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-medium text-indigo-700">
                    กราฟที่ 2 — จำนวนงานซ่อมตามหน่วยงาน (Bar chart)
                  </div>
                  <h3 class="mt-2 text-sm sm:text-base font-semibold text-zinc-900">
                    จำนวนงานซ่อมแยกตามหน่วยงาน (ทั้งหมด)
                  </h3>
                  <p class="mt-0.5 text-xs text-zinc-500 max-w-xl">
                    แสดงภาพรวมจำนวนงานซ่อมของทุกหน่วยงานที่มีการแจ้งซ่อมในระบบ อ่านง่ายในรูปแบบแท่งแนวตั้ง
                  </p>
                </div>
              </div>

              @if (count($deptLabels) && count($deptCounts))
                <div class="mt-4 h-72 w-full">
                  <canvas id="deptBar"
                          class="block w-full h-full"
                          data-labels='@json($deptLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                          data-values='@json($deptCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
                </div>
              @else
                <div class="mt-4 grid h-40 place-items-center text-zinc-400 text-sm">
                  ยังไม่มีข้อมูลสำหรับสร้างกราฟ
                </div>
              @endif
            </div>

            {{-- กราฟแนวโน้ม + Donut --}}
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
              {{-- Trend: เปลี่ยนเป็น Bar chart --}}
              <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white/80 px-4 py-4 sm:px-5 sm:py-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-[11px] font-medium text-sky-700">
                      กราฟที่ 3 — แนวโน้มจำนวนงานซ่อม (Bar chart)
                    </div>
                    <h3 class="mt-2 text-sm sm:text-base font-semibold text-zinc-900">
                      แนวโน้มจำนวนงานซ่อมตามเดือนที่มีข้อมูล
                    </h3>
                    <p class="mt-0.5 text-xs text-zinc-500">
                      แสดงจำนวนงานซ่อมในแต่ละเดือนด้วยกราฟแท่งสีน้ำเงินแบบชัดเจน เหมาะสำหรับดูเทรนด์ขึ้นลงของปริมาณงาน
                    </p>
                  </div>
                </div>

                @if (count($trendLabels) && count($trendCounts))
                  <div class="mt-4 h-80 w-full">
                    <canvas id="trendChart"
                            class="block w-full h-full"
                            data-labels='@json($trendLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                            data-values='@json($trendCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
                  </div>
                @else
                  <div class="mt-4 grid h-40 place-items-center text-zinc-400 text-sm">
                    ยังไม่มีข้อมูลสำหรับสร้างกราฟ
                  </div>
                @endif
              </div>

              {{-- Donut chart --}}
              <div class="rounded-xl border border-zinc-200 bg-white/80 px-4 py-4 sm:px-5 sm:py-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-medium text-emerald-700">
                      กราฟที่ 4 — สัดส่วนหมวดหมู่ครุภัณฑ์ (Donut chart)
                    </div>
                    <h3 class="mt-2 text-sm sm:text-base font-semibold text-zinc-900">
                      สัดส่วนหมวดหมู่ครุภัณฑ์ที่ถูกแจ้งซ่อม (ทั้งหมด)
                    </h3>
                    <p class="mt-0.5 text-xs text-zinc-500">
                      แสดงสัดส่วนของหมวดหมู่ครุภัณฑ์ในรูปแบบวงแหวน พร้อมเปอร์เซ็นต์ใน Tooltip โดยใช้ข้อมูลทุกหมวดหมู่ที่มี
                    </p>
                  </div>
                </div>

                @if (count($typeLabels) && count($typeCounts))
                  <div class="mt-4 h-80 w-full">
                    <canvas id="typePie"
                            class="block w-full h-full"
                            data-labels='@json($typeLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                            data-values='@json($typeCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
                  </div>
                @else
                  <div class="mt-4 grid h-40 place-items-center text-zinc-400 text-sm">
                    ยังไม่มีข้อมูลสำหรับสร้างกราฟ
                  </div>
                @endif
              </div>
            </div>
          </div>

          {{-- Divider --}}
          <div class="h-px bg-gradient-to-r from-transparent via-zinc-200 to-transparent"></div>

          {{-- ส่วนที่ 3: รายการล่าสุด --}}
          <div class="space-y-3">
            <div class="flex flex-col gap-1">
              <div class="inline-flex items-center gap-2 rounded-full bg-zinc-900/5 px-3 py-1 text-[11px] font-semibold text-zinc-800">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-zinc-900 text-[10px] text-zinc-50">
                  3
                </span>
                <span>ส่วนที่ 3 • รายการแจ้งซ่อมล่าสุด</span>
              </div>
              <h2 class="text-sm sm:text-base font-semibold text-zinc-900">
                รายการงานซ่อมล่าสุด (Recent Jobs)
              </h2>
            </div>

            <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white/80">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-xs uppercase text-zinc-500 border-b border-zinc-100 bg-zinc-50/80">
                    <th class="py-2.5 pr-3 pl-3 text-left">วันที่แจ้ง</th>
                    <th class="py-2.5 pr-3 text-left">ครุภัณฑ์</th>
                    <th class="py-2.5 pr-3 text-left">ผู้แจ้ง</th>
                    <th class="py-2.5 pr-3 text-left">สถานะ</th>
                    <th class="py-2.5 pr-3 text-left">ผู้รับผิดชอบ</th>
                    <th class="py-2.5 pr-3 text-left">วันที่เสร็จ</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                  @forelse($recent as $t)
                    @php
                      $status = (string) $get($t,'status','');
                      $pill   = $statusPillClass($status);
                      $assetId   = $get($t,'asset_id','-');
                      $assetName = $get($t,'asset_name') ?: $get($t,'asset.name','-');
                      $reporter  = $get($t,'reporter')   ?: $get($t,'reporter.name','-');
                      $tech      = $get($t,'technician')  ?: $get($t,'technician.name','-');
                      $reqAt     = $get($t,'request_date','-');
                      $doneAt    = $get($t,'completed_at') ?: $get($t,'completed_date','-');
                    @endphp
                    <tr class="hover:bg-zinc-50">
                      <td class="py-2.5 pr-3 pl-3 text-zinc-800">
                        {{ is_string($reqAt) ? e($reqAt) : optional($reqAt)->format('Y-m-d H:i') }}
                      </td>
                      <td class="py-2.5 pr-3 text-zinc-800">
                        #{{ e((string)$assetId) }} — {{ e((string)$assetName) }}
                      </td>
                      <td class="py-2.5 pr-3 text-zinc-800">
                        {{ e((string)$reporter) }}
                      </td>
                      <td class="py-2.5 pr-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pill }}">
                          {{ ucfirst(str_replace('_',' ', $status)) }}
                        </span>
                      </td>
                      <td class="py-2.5 pr-3 text-zinc-800">
                        {{ e((string)$tech) }}
                      </td>
                      <td class="py-2.5 pr-3 text-zinc-800">
                        {{ is_string($doneAt) ? e($doneAt) : (optional($doneAt)->format('Y-m-d H:i') ?? '-') }}
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="py-10 text-center text-zinc-400 text-sm">
                        ยังไม่มีข้อมูลรายการแจ้งซ่อมล่าสุดให้แสดง
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </section>

    </div>
  </div>

  {{-- Toast styles --}}
  <style>
    .toast-overlay{position:fixed;inset:0;z-index:100001;pointer-events:none}
    .toast-pos{display:flex;width:100%;height:100%;padding:1rem}
    .toast-pos.tr{align-items:flex-start;justify-content:flex-end}
    .toast-pos.tl{align-items:flex-start;justify-content:flex-start}
    .toast-pos.br{align-items:flex-end;justify-content:flex-end}
    .toast-pos.bl{align-items:flex-end;justify-content:flex-start}
    .toast-pos.center{align-items:center;justify-content:center}
    .toast-pos.tc{align-items:flex-start;justify-content:center;padding-top:calc(var(--topbar-h,0px) + .75rem)}
    .toast-pos.bc{align-items:flex-end;justify-content:center;padding-bottom:.75rem}
    .toast-card{
      --toast-max-w:min(92vw,680px);--toast-min-w:440px;--toast-pad-x:24px;--toast-pad-y:18px;
      --toast-fs:16px;--toast-icon:36px;--toast-radius:16px;--toast-bar-h:4px;
      pointer-events:auto;width:max-content;max-width:var(--toast-max-w);min-width:var(--toast-min-w);
      background:#fff;border-radius:var(--toast-radius);border:1px solid #e5eef7;box-shadow:0 14px 48px rgba(15,23,42,.14);
      opacity:0;transform:translateY(-6px);transition:opacity .22s ease, transform .22s ease;
      display:flex;align-items:center;gap:.9rem;padding:var(--toast-pad-y) var(--toast-pad-x);position:relative;overflow:hidden;
    }
    .toast-card.show{opacity:1;transform:translateY(0)}
    .toast--sm{--toast-max-w:min(92vw,420px);--toast-min-w:320px;--toast-pad-x:16px;--toast-pad-y:10px;--toast-fs:14px;--toast-icon:28px;--toast-radius:12px;--toast-bar-h:3px}
    .toast--md{--toast-max-w:min(92vw,520px);--toast-min-w:380px;--toast-pad-x:18px;--toast-pad-y:14px;--toast-fs:15px;--toast-icon:32px;--toast-radius:12px;--toast-bar-h:4px}
    .toast-icon{flex:0 0 var(--toast-icon);display:flex;align-items:center;justify-content:center}
    .toast-msg{font-size:var(--toast-fs);color:#0f172a;line-height:1.5;white-space:normal;word-break:break-word;flex:1}
    .toast-close{border:0;background:transparent;font-size:calc(var(--toast-fs) + 1px);color:#64748b;cursor:pointer;line-height:1}
    .toast-close:hover{color:#0f172a}
    .toast-bar{position:absolute;bottom:0;left:0;height:var(--toast-bar-h);width:100%;background:#f1f5f9}
    .toast-fill{height:var(--toast-bar-h);width:0;transition:width linear}
    .fill-success{background:#10b981}.fill-info{background:#3b82f6}.fill-warning{background:#f59e0b}.fill-error{background:#ef4444}
    @media (max-width:480px){.toast-card{min-width:calc(100vw - 2rem)}}
  </style>
  <div class="toast-overlay" aria-live="polite" aria-atomic="true"></div>

  @php
    $lottieMap = $lottieMap ?? [
      'success' => asset('lottie/lock_with_green_tick.json'),
      'info'    => asset('lottie/lock_with_blue_info.json'),
      'warning' => asset('lottie/lock_with_yellow_alert.json'),
      'error'   => asset('lottie/lock_with_red_tick.json'),
    ];
  @endphp

  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>

  <script>
  (function(){
    // Toggle filters panel (panel อยู่ใน Header sticky)
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('filterToggle');
      const panel = document.getElementById('filtersPanel');
      if (btn && panel) {
        if (!@json($filtersActive)) {
          panel.classList.add('hidden');
        }
        btn.addEventListener('click', () => {
          const hidden = panel.classList.toggle('hidden');
          btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        });
      }
    });

    function waitFor(condFn, {tries=50, interval=60} = {}) {
      return new Promise((resolve, reject) => {
        const t = setInterval(() => {
          if (condFn()) { clearInterval(t); resolve(true); }
          else if (--tries <= 0) { clearInterval(t); reject(new Error('waitFor timeout')); }
        }, interval);
      });
    }

    function parseData(el){
      try {
        const labels = JSON.parse(el.dataset.labels || '[]');
        const values = JSON.parse(el.dataset.values || '[]');
        return { labels, values };
      } catch(e) {
        return { labels: [], values: [] };
      }
    }

    const palette = [
      '#2563eb','#10b981','#f59e0b','#ef4444','#0ea5e9','#8b5cf6',
      '#14b8a6','#f97316','#22c55e','#a855f7','#e11d48','#06b6d4'
    ];
    const CHART_INSTANCES = {};

    // Bar chart หน่วยงาน: สีอ่อน + แท่งมุมโค้ง
    function makeBarChart(el){
      const { labels, values } = parseData(el);
      if (!labels.length || !values.length) return;
      const id = el.id || 'deptBar';
      if (CHART_INSTANCES[id]) CHART_INSTANCES[id].destroy();

      const pastel = [
        'rgba(37,99,235,0.45)',
        'rgba(16,185,129,0.45)',
        'rgba(245,158,11,0.45)',
        'rgba(239,68,68,0.4)',
        'rgba(14,165,233,0.45)',
        'rgba(139,92,246,0.45)',
        'rgba(20,184,166,0.45)',
        'rgba(249,115,22,0.45)',
      ];

      CHART_INSTANCES[id] = new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'จำนวนงานซ่อม',
            data: values,
            backgroundColor: labels.map((_,i)=> pastel[i % pastel.length]),
            borderColor: 'rgba(148,163,184,0.4)',
            borderWidth: 1,
            borderRadius: 8,
            maxBarThickness: 32,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              grid: { display:false },
              ticks: { color:'#475569' }
            },
            y: {
              grid: { color:'rgba(148,163,184,0.18)' },
              ticks: { color:'#475569', precision:0 },
              beginAtZero:true
            }
          },
          plugins: {
            legend: { display:false },
            tooltip: { mode:'index', intersect:false }
          }
        }
      });
    }

    // Trend chart: เปลี่ยนเป็น Bar chart แนวตั้ง
    function makeTrendBarChart(el){
      const { labels, values } = parseData(el);
      if (!labels.length || !values.length) return;

      if (CHART_INSTANCES[el.id]) CHART_INSTANCES[el.id].destroy();
      const ctx = el.getContext('2d');

      const gradient = ctx.createLinearGradient(0, 0, 0, el.clientHeight || 260);
      gradient.addColorStop(0, 'rgba(37,99,235,0.9)');
      gradient.addColorStop(1, 'rgba(191,219,254,0.15)');

      CHART_INSTANCES[el.id] = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'จำนวนงานซ่อม / เดือน',
            data: values,
            backgroundColor: gradient,
            borderColor: '#1d4ed8',
            borderWidth: 1.5,
            borderRadius: 6,
            maxBarThickness: 40,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: '#6b7280' }
            },
            y: {
              beginAtZero: true,
              ticks: { color: '#6b7280' },
              grid: { color: 'rgba(148,163,184,0.25)' }
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: { color: '#374151', usePointStyle: true, boxWidth: 10 }
            },
            tooltip: {
              backgroundColor: 'rgba(15,23,42,0.92)',
              titleColor: '#fff',
              bodyColor: '#fff',
              callbacks: {
                label: ctx => {
                  const v = ctx.parsed.y ?? 0;
                  return ` ${ctx.dataset.label}: ${v} งาน`;
                }
              }
            }
          }
        }
      });
    }

    // Donut chart
    function makePieChart(el){
      const { labels, values } = parseData(el);
      if (!labels.length || !values.length) return;
      const id = el.id || 'typePie';
      if (CHART_INSTANCES[id]) CHART_INSTANCES[id].destroy();

      const total = values.reduce((a,b)=> a + (Number(b)||0), 0);

      CHART_INSTANCES[id] = new Chart(el.getContext('2d'), {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{
            data: values,
            backgroundColor: labels.map((_,i)=> palette[i % palette.length]),
            borderWidth: 2,
            borderColor: '#ffffff',
            borderRadius: 4,
          }]
        },
        options: {
          responsive:true,
          maintainAspectRatio:false,
          cutout: '60%',
          plugins:{
            legend:{
              position:'right',
              labels:{ color:'#334155', usePointStyle:true, boxWidth:10 }
            },
            tooltip: {
              backgroundColor: 'rgba(15,23,42,0.92)',
              titleColor: '#e5e7eb',
              bodyColor: '#f9fafb',
              callbacks: {
                label: (ctx) => {
                  const value = ctx.parsed;
                  const percent = total ? (value / total * 100).toFixed(1) : 0;
                  return ` ${ctx.label}: ${value} งาน (${percent}%)`;
                }
              }
            }
          }
        }
      });
    }

    // สรุปสถานะ: bar แนวนอน
    function makeStatusTrend(el) {
      el.style.width  = '100%';
      el.style.height = '100%';

      const pending   = Number(el.dataset.pending || 0);
      const progress  = Number(el.dataset.progress || 0);
      const completed = Number(el.dataset.completed || 0);
      const total     = Number(el.dataset.total || 0);

      const labels = ['Pending', 'In Progress', 'Completed', 'Total'];
      const data   = [pending, progress, completed, total];

      const ctx = el.getContext('2d');

      if (CHART_INSTANCES['statusTrend']) {
        CHART_INSTANCES['statusTrend'].destroy();
      }

      CHART_INSTANCES['statusTrend'] = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'จำนวนงานซ่อม',
              data,
              backgroundColor: [
                'rgba(251,191,36,0.85)',
                'rgba(59,130,246,0.85)',
                'rgba(16,185,129,0.85)',
                'rgba(15,23,42,0.92)',
              ],
              borderRadius: 8,
              borderSkipped: false,
              maxBarThickness: 36,
            },
          ],
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              beginAtZero: true,
              ticks: { color: '#475569' },
              grid: { color: 'rgba(148,163,184,0.25)' },
            },
            y: {
              ticks: { color: '#475569' },
              grid: { display: false },
            },
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(15,23,42,0.92)',
              titleColor: '#e5e7eb',
              bodyColor: '#f9fafb',
              callbacks: {
                label: (ctx) => ` ${ctx.parsed.x} งาน`,
              },
            },
          },
        },
      });
    }

    async function renderCharts(){
      try { await waitFor(()=> window.Chart?.registry); } catch(_) { return; }
      const deptBar     = document.getElementById('deptBar');
      const trendChart  = document.getElementById('trendChart');
      const typePie     = document.getElementById('typePie');
      const statusTrend = document.getElementById('statusTrend');

      if (statusTrend) makeStatusTrend(statusTrend);
      if (deptBar)     makeBarChart(deptBar);
      if (trendChart)  makeTrendBarChart(trendChart);
      if (typePie)     makePieChart(typePie);
    }

    const LOTTIE = {
      success: @json($lottieMap['success'] ?? null),
      info:    @json($lottieMap['info']    ?? null),
      warning: @json($lottieMap['warning'] ?? null),
      error:   @json($lottieMap['error']   ?? null),
    };
    const SVG = {
      success: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>',
      info:    '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
      warning: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
      error:   '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>',
    };

    function lottieReady(){
      if (window.customElements && window.customElements.whenDefined) {
        return window.customElements.whenDefined('lottie-player').catch(()=>{});
      }
      return Promise.resolve();
    }

    function makeIconEl(type){
      const wrap = document.createElement('div');
      wrap.className = 'toast-icon';
      const src = LOTTIE[type];
      const canUseLottie = !!src && window.customElements && !!window.customElements.get('lottie-player');
      if (canUseLottie) {
        wrap.innerHTML = `<lottie-player src="${src}" style="width:var(--toast-icon);height:var(--toast-icon)" background="transparent" speed="1" autoplay></lottie-player>`;
        setTimeout(() => {
          const lp = wrap.querySelector('lottie-player');
          if (!lp || !lp.clientWidth) {
            wrap.innerHTML = `<div style="width:var(--toast-icon);height:var(--toast-icon);display:grid;place-items:center;">${SVG[type] ?? ''}</div>`;
          }
        }, 800);
      } else {
        wrap.innerHTML = `<div style="width:var(--toast-icon);height:var(--toast-icon);display:grid;place-items:center;">${SVG[type] ?? ''}</div>`;
      }
      return wrap;
    }

    const FORCE_POSITION = null;

    function ensurePos(position){
      const overlay = document.querySelector('.toast-overlay');
      overlay.innerHTML = '';
      const posEl = document.createElement('div');
      posEl.className = 'toast-pos ' + position;
      overlay.appendChild(posEl);
      return { overlay, posEl };
    }

    function showToast({type='info', message='', position='tc', timeout=3200, size='lg'} = {}){
      position = FORCE_POSITION || position || 'tc';
      const allowed = ['tr','tl','br','bl','center','tc','bc'];
      if (!allowed.includes(position)) position = 'tc';
      timeout = Number(timeout) || 3200;

      const { posEl } = ensurePos(position);
      const card = document.createElement('section');
      const sizeClass = (['sm','md','lg'].includes(size) ? `toast--${size}` : 'toast--lg');
      card.className = `toast-card ${sizeClass} toast-${type}`;
      card.setAttribute('role','status');

      const icon = makeIconEl(type);
      const msg = document.createElement('div');
      msg.className = 'toast-msg';
      msg.textContent = message ?? '';

      const btn = document.createElement('button');
      btn.className = 'toast-close';
      btn.setAttribute('aria-label','Close');
      btn.innerHTML = '&times;';

      const bar = document.createElement('div');
      bar.className = 'toast-bar';
      const fill = document.createElement('div');
      fill.className = `toast-fill fill-${type}`;
      bar.appendChild(fill);

      card.append(icon, msg, btn, bar);
      posEl.appendChild(card);

      // แสดงการ์ด + แถบเวลา
      requestAnimationFrame(() => {
        card.classList.add('show');
        fill.style.transitionDuration = timeout + 'ms';
        requestAnimationFrame(() => {
          fill.style.width = '100%';
        });
      });

      let closed = false;
      function close() {
        if (closed) return;
        closed = true;
        card.classList.remove('show');
        setTimeout(() => {
          card.remove();
        }, 220);
      }

      btn.addEventListener('click', () => {
        close();
      });

      const autoTimer = setTimeout(close, timeout);
      card.addEventListener('mouseenter', () => {
        clearTimeout(autoTimer);
      });
    }

    // export ไว้เรียกจากที่อื่นได้ถ้าต้องการ
    window.showToast = showToast;
    window.AppToast  = { show: showToast };

    // โหลดกราฟ + แสดง Toast อัตโนมัติถ้ามี message จาก backend
    document.addEventListener('DOMContentLoaded', () => {
      // สร้างกราฟทั้งหมด
      renderCharts();

      const hasMessage = @json((bool) $message);
      if (hasMessage) {
        lottieReady().finally(() => {
          showToast({
            type: @json($type ?? 'info'),
            message: @json($message ?? ''),
            position: @json($position ?? 'tc'),
            timeout: @json($timeout ?? 3200),
            size: @json($size ?? 'lg'),
          });
        });
      }
    });

  })();
  </script>
@endsection

@section('footer')
  <div class="text-xs text-zinc-500">
    © {{ date('Y') }} {{ config('app.name','Asset Repair') }} — Asset Repair Dashboard
  </div>
@endsection
