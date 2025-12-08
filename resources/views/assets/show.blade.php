{{-- resources/views/assets/show.blade.php --}}
@extends('layouts.app')

@section('title', 'รายละเอียดครุภัณฑ์ #'.($asset->asset_code ?? $asset->id))

@php
  $status = strtolower((string) $asset->status);

  $statusLabel = [
    'active'    => 'ใช้งานปกติ',
    'in_repair' => 'อยู่ระหว่างซ่อม',
    'disposed'  => 'จำหน่ายออก',
  ][$status] ?? ($asset->status ?? 'ไม่ระบุ');

  $statusTone = match ($status) {
    'active'    => 'bg-emerald-50 text-emerald-800 border-emerald-300',
    'in_repair' => 'bg-amber-50 text-amber-800 border-amber-300',
    'disposed'  => 'bg-rose-50 text-rose-800 border-rose-300',
    default     => 'bg-slate-50 text-slate-700 border-slate-300',
  };

  $deptName = optional($asset->department)->name_th
      ?? optional($asset->department)->name_en
      ?? 'ไม่ระบุหน่วยงาน';

  $categoryName = optional($asset->categoryRef)->name ?? 'ไม่ระบุหมวดหมู่';

  $reqCount   = $asset->maintenance_requests_count ?? 0;
  $attCount   = $asset->attachments_count ?? 0;
  $attList    = $attachments ?? collect();
@endphp

@section('page-header')
  <div class="bg-slate-50 border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex flex-wrap items-start justify-between gap-4">

        {{-- LEFT: Title + meta --}}
        <div class="space-y-2">
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                <path d="M4 7h16v10H4zM9 17V7"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <span class="flex flex-wrap items-baseline gap-2">
              <span class="text-[18px] sm:text-[20px]">สรุปข้อมูลครุภัณฑ์</span>
              <span class="text-sm text-slate-500 flex items-center gap-1">
                รหัส
                <span class="font-medium text-slate-800">
                  {{ $asset->asset_code ?? 'ไม่ระบุ' }}
                </span>
              </span>
            </span>
          </h1>

          <div class="flex flex-wrap items-center gap-2 text-xs sm:text-[13px]">
            {{-- สถานะ --}}
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 {{ $statusTone }}">
              <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
              {{ $statusLabel }}
            </span>

            {{-- หน่วยงาน --}}
            <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white px-2.5 py-1 text-slate-700">
              <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none">
                <path d="M4 12h16M4 7h16M4 17h10"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              {{ $deptName }}
            </span>

            {{-- หมวดหมู่ --}}
            <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white px-2.5 py-1 text-slate-700">
              <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none">
                <path d="M4 4h16v7H4zM4 15h10v5H4z"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              {{ $categoryName }}
            </span>
          </div>

          <p class="text-xs sm:text-sm text-slate-600">
            หน้านี้ใช้สำหรับสรุปข้อมูลครุภัณฑ์ ประวัติการซ่อม และไฟล์แนบที่เกี่ยวข้อง
          </p>
        </div>

        {{-- RIGHT: actions --}}
        <div class="ml-auto flex flex-wrap items-center gap-2">
          <a href="{{ route('assets.index') }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
             aria-label="Back to list">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
              <path d="M15 18l-6-6 6-6"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            กลับหน้ารายการ
          </a>

          <a href="{{ route('assets.edit', $asset) }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none">
              <path d="M4 21h4l10-10-4-4L4 17v4zM13 7l4 4"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            แก้ไขข้อมูล
          </a>

          <a href="{{ route('assets.print', $asset) }}"
             target="_blank"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none">
              <path d="M6 9V4h12v5M6 19h12v-6H6v6z"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            พิมพ์ PDF
          </a>

          <a href="{{ url('/api/assets/'.$asset->id.'?pretty=1') }}"
             target="_blank"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none">
              <path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            JSON
          </a>

          <form method="POST"
                action="{{ route('assets.destroy', $asset) }}"
                class="inline-flex"
                onsubmit="window.dispatchEvent(new CustomEvent('app:toast',{detail:{type:'info',message:'กำลังลบ...'}})); return confirm('ต้องการลบครุภัณฑ์นี้หรือไม่?')">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-white px-3 py-2 text-sm text-rose-700 hover:bg-rose-50">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M3 6h18M8 6v12M16 6v12M5 6l1 14a2 2 0 002 2h8a2 2 0 002-2l1-14M10 6V4h4v2"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              ลบ
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

      {{-- HEADER STRIP --}}
      <div class="border-b border-slate-200 bg-slate-50 px-6 py-3.5">
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm">
          <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] {{ $statusTone }}">
              สถานะปัจจุบัน: {{ $statusLabel }}
            </span>
            <span class="inline-flex items-center rounded-full border border-slate-300 bg-white px-2.5 py-0.5 text-[11px] text-slate-700">
              คำขอซ่อมที่เกี่ยวข้อง: {{ $reqCount }} งาน
            </span>
            <span class="inline-flex items-center rounded-full border border-slate-300 bg-white px-2.5 py-0.5 text-[11px] text-slate-700">
              ไฟล์แนบ: {{ $attCount }} ไฟล์
            </span>
          </div>
          <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-600">
            <span>สร้างรายการ: {{ $asset->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
            <span>อัปเดตล่าสุด: {{ $asset->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
          </div>
        </div>
      </div>

      {{-- SECTION 1: ข้อมูลครุภัณฑ์และการจัดหมวดหมู่ (แบบทางการ) --}}
      <section class="px-6 py-5 border-b border-slate-200">
        <header class="mb-3 border-b border-slate-200 pb-2">
          <h2 class="text-sm font-semibold text-slate-900">
            ส่วนที่ 1 — ข้อมูลครุภัณฑ์และการจัดหมวดหมู่
          </h2>
          <p class="mt-0.5 text-xs text-slate-500">
            รายละเอียดทะเบียนครุภัณฑ์ หน่วยงานเจ้าของ และข้อมูลจำเพาะหลัก ใช้สำหรับอ้างอิงในงานบริหารทรัพย์สิน
          </p>
        </header>

        <div class="rounded-xl border border-slate-200 overflow-hidden text-sm">
          {{-- หัวตารางเล็ก --}}
          <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between">
            <div class="text-[13px] font-medium text-slate-700">
              ข้อมูลทะเบียนครุภัณฑ์
            </div>
            <div class="text-[11px] text-slate-500">
              เลขทะเบียน: {{ $asset->asset_code ?? '—' }}
            </div>
          </div>

          {{-- ตารางข้อมูลแบบราชการ --}}
          <div class="divide-y divide-slate-200">
            <div class="grid md:grid-cols-[190px,minmax(0,1fr)]">
              <div class="bg-slate-50 px-4 py-2.5 text-[13px] font-medium text-slate-600">
                ชื่อครุภัณฑ์
              </div>
              <div class="px-4 py-2.5 text-slate-900">
                {{ $asset->name ?? '—' }}
              </div>
            </div>

            <div class="grid md:grid-cols-[190px,minmax(0,1fr)]">
              <div class="bg-slate-50 px-4 py-2.5 text-[13px] font-medium text-slate-600">
                หมวดหมู่
              </div>
              <div class="px-4 py-2.5 text-slate-900">
                {{ $categoryName }}
              </div>
            </div>

            <div class="grid md:grid-cols-[190px,minmax(0,1fr)]">
              <div class="bg-slate-50 px-4 py-2.5 text-[13px] font-medium text-slate-600">
                หน่วยงานเจ้าของ
              </div>
              <div class="px-4 py-2.5 text-slate-900">
                {{ $deptName }}
              </div>
            </div>

            <div class="grid md:grid-cols-[190px,minmax(0,1fr)]">
              <div class="bg-slate-50 px-4 py-2.5 text-[13px] font-medium text-slate-600">
                ที่ตั้ง / สถานที่ใช้งาน
              </div>
              <div class="px-4 py-2.5 text-slate-900">
                {{ $asset->location ?? '—' }}
              </div>
            </div>

            <div class="grid md:grid-cols-[190px,minmax(0,1fr)]">
              <div class="bg-slate-50 px-4 py-2.5 text-[13px] font-medium text-slate-600">
                ประเภท (Type)
              </div>
              <div class="px-4 py-2.5 text-slate-900">
                {{ $asset->type ?? '—' }}
              </div>
            </div>

            <div class="grid md:grid-cols-[190px,minmax(0,1fr)]">
              <div class="bg-slate-50 px-4 py-2.5 text-[13px] font-medium text-slate-600">
                ยี่ห้อ / รุ่น
              </div>
              <div class="px-4 py-2.5 text-slate-900">
                {{ trim(($asset->brand ?? '').' '.($asset->model ?? '')) ?: '—' }}
              </div>
            </div>

            <div class="grid md:grid-cols-[190px,minmax(0,1fr)]">
              <div class="bg-slate-50 px-4 py-2.5 text-[13px] font-medium text-slate-600">
                Serial Number
              </div>
              <div class="px-4 py-2.5 text-slate-900">
                {{ $asset->serial_number ?? '—' }}
              </div>
            </div>
          </div>

          {{-- แถบสรุปวันที่ด้านล่าง --}}
          <div class="bg-slate-50 border-t border-slate-200 px-4 py-2.5 text-[11px] text-slate-600 flex flex-wrap gap-3 justify-between">
            <span>
              วันที่ซื้อ:
              <span class="font-medium text-slate-800">
                {{ optional($asset->purchase_date)->format('Y-m-d') ?? '—' }}
              </span>
            </span>
            <span>
              วันหมดประกัน:
              <span class="font-medium text-slate-800">
                {{ optional($asset->warranty_expire)->format('Y-m-d') ?? '—' }}
              </span>
            </span>
            <span>
              อัปเดตข้อมูลล่าสุด:
              <span class="font-medium text-slate-800">
                {{ $asset->updated_at?->format('Y-m-d H:i') ?? '—' }}
              </span>
            </span>
          </div>
        </div>
      </section>

      {{-- SECTION 2: สรุปการใช้งาน / คำขอซ่อม --}}
      <section class="px-6 py-5 border-b border-slate-200">
        <header class="mb-3 border-b border-slate-200 pb-2">
          <h2 class="text-sm font-semibold text-slate-900">
            ส่วนที่ 2 — ภาพรวมการใช้งานและคำขอซ่อม
          </h2>
          <p class="mt-0.5 text-xs text-slate-500">
            จำนวนคำขอซ่อมที่เคยแจ้ง และลิงก์ไปยังประวัติการซ่อม
          </p>
        </header>

        <div class="grid gap-4 md:grid-cols-3 text-sm">
          <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <div class="text-[11px] font-medium text-slate-500">จำนวนคำขอซ่อม</div>
            <div class="mt-1 text-xl font-semibold text-slate-900">{{ $reqCount }}</div>
            <p class="mt-0.5 text-[11px] text-slate-500">
              รวมทุกสถานะที่เกี่ยวข้องกับครุภัณฑ์นี้
            </p>
          </div>

          <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <div class="text-[11px] font-medium text-slate-500">ไฟล์แนบ</div>
            <div class="mt-1 text-xl font-semibold text-slate-900">{{ $attCount }}</div>
            <p class="mt-0.5 text-[11px] text-slate-500">
              ไฟล์ที่แนบกับครุภัณฑ์นี้โดยตรง
            </p>
          </div>

          <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 flex flex-col justify-between">
            <div>
              <div class="text-[11px] font-medium text-slate-500">การดำเนินการด่วน</div>
            </div>
            <div class="mt-2 space-y-1.5">
              <a href="{{ url('/maintenance/requests/create?asset_id='.$asset->id) }}"
                 class="inline-flex w-full items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">
                <span>สร้างคำขอซ่อมใหม่</span>
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                  <path d="M9 18l6-6-6-6"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>
              <a href="{{ url('/maintenance/requests?asset_id='.$asset->id) }}"
                 class="inline-flex w-full items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">
                <span>ดูประวัติคำขอซ่อม</span>
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                  <path d="M9 18l6-6-6-6"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION 3: บันทึกการซ่อมล่าสุด (TIMELINE) --}}
      <section class="px-6 py-5 border-b border-slate-200">
        <header class="mb-3 border-b border-slate-200 pb-2 flex items-center justify-between gap-2">
          <div>
            <h2 class="text-sm font-semibold text-slate-900">
              ส่วนที่ 3 — บันทึกการซ่อมล่าสุด
            </h2>
            <p class="mt-0.5 text-xs text-slate-500">
              แสดงบันทึกการซ่อมที่เชื่อมกับครุภัณฑ์นี้ (เรียงจากล่าสุด)
            </p>
          </div>
          <span class="text-[11px] text-slate-500">{{ $logs->count() }} รายการ</span>
        </header>

        @if($logs->isEmpty())
          <p class="py-4 text-sm text-slate-500">
            ไม่มีบันทึกการซ่อมล่าสุดสำหรับครุภัณฑ์นี้
          </p>
        @else
          <div class="relative mt-1">
            {{-- เส้นไทม์ไลน์หลัก --}}
            <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-200"></div>

            <ol class="space-y-4">
              @foreach($logs as $log)
                @php
                  $act = strtolower((string) $log->action);
                  $dotTone = match ($act) {
                    'resolved', 'closed'   => 'bg-emerald-500 ring-emerald-100',
                    'on_hold'              => 'bg-amber-500 ring-amber-100',
                    'started'              => 'bg-sky-500 ring-sky-100',
                    'accepted', 'assigned' => 'bg-indigo-500 ring-indigo-100',
                    default                => 'bg-slate-400 ring-slate-200',
                  };
                @endphp

                <li class="relative pl-10">
                  {{-- จุดบนไทม์ไลน์ --}}
                  <span class="absolute left-2 top-2 h-3.5 w-3.5 rounded-full border-2 border-white shadow-sm {{ $dotTone }}"></span>

                  <div class="flex flex-col gap-0.5 sm:flex-row sm:items-baseline sm:justify-between">
                    <div class="text-xs font-medium text-slate-500">
                      {{ $log->created_at?->format('Y-m-d') ?? '—' }}
                      @if($log->created_at)
                        <span class="ml-1 text-[11px] text-slate-400">
                          {{ $log->created_at->format('H:i') }}
                        </span>
                      @endif
                    </div>
                    @if($loop->first)
                      <span class="text-[11px] uppercase tracking-wide text-emerald-600">
                        รายการล่าสุด
                      </span>
                    @endif
                  </div>

                  <div class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ ucfirst(str_replace('_',' ', $log->action)) }}
                  </div>

                  @if($log->note)
                    <div class="mt-0.5 text-xs leading-relaxed text-slate-600 whitespace-pre-line">
                      {{ $log->note }}
                    </div>
                  @endif
                </li>
              @endforeach
            </ol>
          </div>
        @endif
      </section>

      {{-- SECTION 4: ไฟล์แนบของครุภัณฑ์ --}}
      <section class="px-6 py-5">
        <header class="mb-3 border-b border-slate-200 pb-2 flex items-center justify-between gap-2">
          <div>
            <h2 class="text-sm font-semibold text-slate-900">
              ส่วนที่ 4 — ไฟล์แนบของครุภัณฑ์
            </h2>
            <p class="mt-0.5 text-xs text-slate-500">
              ไฟล์ที่แนบไว้กับครุภัณฑ์นี้โดยตรง เช่น รูปถ่าย สัญญา หรือเอกสารอื่น ๆ
            </p>
          </div>
          <span class="text-[11px] text-slate-500">{{ $attList->count() }} ไฟล์</span>
        </header>

        @if($attList->count())
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($attList as $att)
              @php
                $name    = $att->original_name ?? ('Attachment #'.$att->id);
                $openUrl = $att->url ?? null;
              @endphp
              <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                <div class="min-w-0">
                  <div class="truncate text-slate-800" title="{{ $name }}">
                    {{ $name }}
                  </div>
                  <div class="text-[11px] text-slate-500">
                    {{ $att->created_at?->format('Y-m-d H:i') ?? '' }}
                  </div>
                </div>
                @if($openUrl)
                  <a href="{{ $openUrl }}" target="_blank"
                     class="inline-flex items-center rounded-md border border-sky-300 bg-sky-50 px-2 py-1 text-[11px] font-medium text-sky-800 hover:bg-sky-100">
                    เปิด
                  </a>
                @else
                  <span class="text-[11px] text-slate-400">ไม่มีลิงก์</span>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <p class="text-xs text-slate-500">
            ยังไม่มีไฟล์แนบสำหรับครุภัณฑ์นี้
          </p>
        @endif
      </section>

    </div>
  </div>

  <style>
    @media print {
      nav, aside, .no-print { display:none !important; }
      main { padding:0 !important; }
      body { background:white !important; }
    }
  </style>
@endsection
