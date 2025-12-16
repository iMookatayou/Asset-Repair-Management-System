{{-- resources/views/maintenance/requests/show.blade.php --}}
@extends('layouts.app')

@section('title', 'สรุปใบงานซ่อม #'.$req->id)

@section('page-header')
@php
    $workers     = $req->workers ?? collect();
    $workerCount = $workers->count();

    $statusLabel = [
      'pending'     => 'รอคิว',
      'accepted'    => 'รับงานแล้ว',
      'in_progress' => 'กำลังดำเนินงาน',
      'on_hold'     => 'พักงาน',
      'resolved'    => 'เสร็จสิ้น',
      'closed'      => 'ปิดงาน',
      'cancelled'   => 'ยกเลิก',
    ][$req->status] ?? $req->status;

    $prioLabel = [
      'low'    => 'ต่ำ',
      'medium' => 'ปานกลาง',
      'high'   => 'สูง',
      'urgent' => 'เร่งด่วน',
    ][$req->priority] ?? $req->priority;
@endphp

<div class="bg-slate-50 border-b border-slate-200">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5">
    <div class="flex flex-wrap justify-between items-start gap-6">

        <div class="space-y-2">
            <h1 class="text-[26px] font-semibold text-slate-900 flex items-center gap-3 leading-tight">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                      <path d="M4 7h16M4 12h10M4 17h6"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>

                <span class="flex flex-col">
                    <span class="text-[22px] sm:text-[24px] font-semibold">
                        Repair Summary Form
                    </span>

                    <span class="text-[14px] sm:text-[15px] text-slate-600 font-normal flex gap-2 flex-wrap">
                        หมายเลขใบงาน
                        <span id="rid" class="text-slate-900 font-semibold">#{{ $req->id }}</span>

                        @if($req->request_no)
                          <span class="text-slate-500">เลขอ้างอิง: {{ $req->request_no }}</span>
                        @endif
                    </span>
                </span>
            </h1>

            <p class="mt-2 text-[14.5px] text-slate-600 leading-relaxed">
                แบบฟอร์มนี้ใช้สำหรับบันทึกรายละเอียดงานซ่อม การมอบหมายทีมช่าง และข้อมูลประกอบงานทั้งหมด
            </p>
        </div>

        <div class="flex flex-col sm:flex-row flex-wrap gap-2 items-start sm:items-center">
            <button id="copyIdBtn"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-[14px] text-slate-800 hover:bg-slate-50">
                คัดลอกหมายเลขงาน
            </button>

            <a href="{{ route('maintenance.requests.work-order', ['req' => $req->id]) }}"
               target="_blank"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-[14px] text-slate-800 hover:bg-slate-50">
               พิมพ์ Work Order
            </a>

            <a href="{{ route('maintenance.requests.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-4 py-2 text-[14px] text-slate-700 hover:bg-slate-50">
               กลับหน้ารายการ
            </a>
        </div>

    </div>
  </div>
</div>
@endsection

@section('content')
  @php
    $status = strtolower((string) $req->status);
    $statusLabel = [
      'pending'     => 'รอคิว',
      'accepted'    => 'รับงานแล้ว',
      'in_progress' => 'ระหว่างดำเนินการ',
      'on_hold'     => 'พักไว้',
      'resolved'    => 'แก้ไขแล้ว',
      'closed'      => 'ปิดงาน',
      'cancelled'   => 'ยกเลิก',
    ][$status] ?? $status;

    $statusTone = match ($status) {
      'pending'     => 'bg-sky-50 text-sky-800 border-sky-300',
      'accepted'    => 'bg-indigo-50 text-indigo-800 border-indigo-300',
      'in_progress' => 'bg-sky-50 text-sky-800 border-sky-300',
      'on_hold'     => 'bg-amber-50 text-amber-800 border-amber-300',
      'resolved'    => 'bg-emerald-50 text-emerald-800 border-emerald-300',
      'closed'      => 'bg-emerald-50 text-emerald-800 border-emerald-300',
      'cancelled'   => 'bg-rose-50 text-rose-800 border-rose-300',
      default       => 'bg-slate-50 text-slate-700 border-slate-300',
    };

    $prio = strtolower((string) $req->priority);
    $prioLabel = [
      'low'    => 'ต่ำ',
      'medium' => 'ปานกลาง',
      'high'   => 'สูง',
      'urgent' => 'เร่งด่วน',
    ][$prio] ?? ($req->priority ?? '—');

    $prioTone = match ($prio) {
      'low'    => 'bg-white text-zinc-700 border-zinc-300',
      'medium' => 'bg-white text-sky-800 border-sky-300',
      'high'   => 'bg-white text-amber-800 border-amber-300',
      'urgent' => 'bg-white text-rose-800 border-rose-300',
      default  => 'bg-white text-zinc-700 border-zinc-300',
    };

    $requestedAt  = optional($req->request_date ?? $req->created_at);
    $assignedAt   = optional($req->assigned_date);
    $completedAt  = optional($req->completed_date);
    $contactEmail = $req->reporter?->email ?? $req->reporter_email;
    $contactPhone = $req->reporter_phone;
    $assetName    = $req->asset?->name ?? ($req->asset_id ? '#'.$req->asset_id : '—');
    $location     = $req->location_text ?: ($req->department?->name_th ?? $req->department?->name_en ?? '—');

    $workers      = $req->workers ?? collect();
    $allWorkers   = $techUsers ?? \App\Models\User::technicians()->orderBy('name')->get();

    $atts         = ($req->attachments ?? collect());
    $opLog        = $req->operationLog;

    // ✅ กันกรณี assignments ไม่ได้ load
    $assignments  = $req->assignments ?? collect();

    // ✅ route รับงาน: ใหม่ (maintenance) หรือ fallback (repairs)
    $acceptUrl = null;
    try {
      $acceptUrl = route('maintenance.requests.accept', $req);
    } catch (\Throwable $e) {
      $acceptUrl = route('repairs.accept', $req);
    }
  @endphp

  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

      {{-- HEADER STRIP --}}
      <div class="border-b border-slate-200 bg-slate-50 px-6 py-3.5">
        <div class="flex flex-col gap-3">
          <div class="flex flex-wrap items-center justify-between gap-3 text-[13px] sm:text-[14px]">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs sm:text-[13px] font-medium {{ $statusTone }}">
                สถานะปัจจุบัน: {{ $statusLabel }}
              </span>
              <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs sm:text-[13px] font-medium {{ $prioTone }}">
                ความสำคัญ: {{ $prioLabel }}
              </span>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-[12px] sm:text-[13px] text-slate-600">
              <span>สร้างใบงาน: <span class="font-medium text-slate-800">{{ $req->created_at?->format('Y-m-d H:i') ?? '—' }}</span></span>
              <span>อัปเดตล่าสุด: <span class="font-medium text-slate-800">{{ $req->updated_at?->format('Y-m-d H:i') ?? '—' }}</span></span>
            </div>
          </div>

          {{-- ✅ แถบ action สำคัญ --}}
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-xs sm:text-[13px] text-slate-600">
              ผู้รับผิดชอบหลัก:
              <span class="font-semibold text-slate-900">
                {{ $req->technician?->name ?? 'ยังไม่มีช่างรับงาน' }}
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              {{-- ✅ ช่างรับงานเอง --}}
              @can('accept', $req)
                <form method="POST" action="{{ $acceptUrl }}">
                  @csrf
                  <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs sm:text-[13px] font-semibold text-white hover:bg-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                      <path d="M20 6L9 17l-5-5"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    รับงานนี้
                  </button>
                </form>
              @endcan

              {{-- ✅ ปุ่มมอบหมายทีมช่าง (เฉพาะ assign) --}}
              @can('assign', $req)
                <button type="button"
                        id="openAssignModalBtn"
                        class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs sm:text-[13px] font-semibold text-white hover:bg-indigo-700">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  มอบหมาย / แก้ไขทีมช่าง
                </button>
              @endcan
            </div>
          </div>
        </div>
      </div>

      {{-- SECTION 1 --}}
      <section class="px-6 py-5 border-b border-slate-200">
        <header class="mb-3 border-b border-slate-200 pb-2">
          <h2 class="text-[15px] font-semibold text-slate-900">ส่วนที่ 1 — ข้อมูลงานซ่อมและผู้แจ้ง</h2>
          <p class="mt-0.5 text-xs sm:text-[13px] text-slate-500">รายละเอียดภาพรวมของใบงาน ผู้แจ้ง หน่วยงาน และเวลาเหตุการณ์หลัก</p>
        </header>

        <div class="text-sm">
          <dl class="grid gap-y-3 gap-x-8 md:grid-cols-2">
            <div class="flex flex-col sm:flex-row sm:items-center gap-1">
              <dt class="w-40 text-xs sm:text-[13px] font-medium text-slate-500">หมายเลขงาน</dt>
              <dd class="flex-1">
                <div class="text-[15px] font-semibold text-slate-900">#{{ $req->id }}</div>
                @if($req->request_no)
                  <div class="mt-0.5 text-xs sm:text-[13px] text-slate-500">เลขอ้างอิงภายใน: {{ $req->request_no }}</div>
                @endif
              </dd>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-1">
              <dt class="w-40 text-xs sm:text-[13px] font-medium text-slate-500">ผู้แจ้ง</dt>
              <dd class="flex-1">
                <div class="text-[15px] font-semibold text-slate-900">
                  {{ $req->reporter?->name ?? $req->reporter_name ?? '-' }}
                </div>
                @if($contactEmail || $contactPhone)
                  <div class="mt-0.5 space-y-0.5 text-xs sm:text-[13px] text-slate-500">
                    @if($contactEmail) <div>{{ $contactEmail }}</div> @endif
                    @if($contactPhone) <div>โทร. {{ $contactPhone }}</div> @endif
                  </div>
                @endif
              </dd>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-1">
              <dt class="w-40 text-xs sm:text-[13px] font-medium text-slate-500">ทรัพย์สิน</dt>
              <dd class="flex-1">
                <div class="text-[15px] font-semibold text-slate-900">{{ $assetName }}</div>
                @if($req->asset?->code)
                  <div class="mt-0.5 text-xs sm:text-[13px] text-slate-500">รหัสครุภัณฑ์: {{ $req->asset->code }}</div>
                @endif
              </dd>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-1">
              <dt class="w-40 text-xs sm:text-[13px] font-medium text-slate-500">หน่วยงาน / สถานที่ติดตั้ง</dt>
              <dd class="flex-1">
                <div class="text-[15px] font-semibold text-slate-900">{{ $location }}</div>
                @if($req->department?->code)
                  <div class="mt-0.5 text-xs sm:text-[13px] text-slate-500">รหัสหน่วยงาน: {{ $req->department->code }}</div>
                @endif
              </dd>
            </div>
          </dl>

          <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 text-xs sm:text-[13px]">
            <div class="grid md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200">
              <div class="px-3 py-2.5">
                <div class="text-[12px] sm:text-[13px] font-medium text-slate-500">รับคำขอ</div>
                <div class="mt-0.5 text-[14px] text-slate-900">
                  {{ $requestedAt ? $requestedAt->format('Y-m-d H:i') : '—' }}
                </div>
              </div>
              <div class="px-3 py-2.5">
                <div class="text-[12px] sm:text-[13px] font-medium text-slate-500">มอบหมายทีมช่าง</div>
                <div class="mt-0.5 text-[14px] text-slate-900">
                  {{ $assignedAt ? $assignedAt->format('Y-m-d H:i') : '—' }}
                </div>
              </div>
              <div class="px-3 py-2.5">
                <div class="text-[12px] sm:text-[13px] font-medium text-slate-500">เสร็จสิ้น</div>
                <div class="mt-0.5 text-[14px] text-slate-900">
                  {{ $completedAt ? $completedAt->format('Y-m-d H:i') : '—' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION 2 --}}
      <section class="px-6 py-5 border-b border-slate-200">
        <header class="mb-3 border-b border-slate-200 pb-2">
          <h2 class="text-[15px] font-semibold text-slate-900">ส่วนที่ 2 — รายละเอียดปัญหาและทีมช่าง</h2>
          <p class="mt-0.5 text-xs sm:text-[13px] text-slate-500">หัวข้อ อาการเสีย และรายชื่อทีมช่างที่เกี่ยวข้องกับงานนี้</p>
        </header>

        <div class="grid gap-5 lg:grid-cols-3">
          <div class="lg:col-span-2 space-y-3 text-sm">
            <div>
              <div class="text-xs sm:text-[13px] font-medium text-slate-500 mb-1">หัวข้อใบงาน</div>
              <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-[14px] sm:text-[15px] font-semibold text-slate-900 min-h-[40px]">
                {{ $req->title ?: '-' }}
              </div>
            </div>

            <div>
              <div class="text-xs sm:text-[13px] font-medium text-slate-500 mb-1">รายละเอียด / อาการเสีย</div>
              <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-[14px] sm:text-[15px] leading-relaxed text-slate-800 whitespace-pre-line min-h-[60px]">
                {{ $req->description ?: '—' }}
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <div class="text-xs sm:text-[13px] font-medium text-slate-500">ทีมช่างที่รับผิดชอบ</div>

            <div class="rounded-md border border-slate-200 bg-white text-xs sm:text-[13px] max-h-60 overflow-y-auto divide-y divide-slate-200">
              @if($workers->isEmpty())
                <div class="px-3 py-2 text-slate-500">
                  ยังไม่ได้มอบหมายงานให้ทีมช่าง
                </div>
              @else
                @foreach($workers as $worker)
                  @php
                    $assign    = $assignments->firstWhere('user_id', $worker->id);
                    $aStatus   = $assign?->status;
                    $badgeTone = 'bg-slate-100 text-slate-700 border-slate-200';
                    $badgeText = 'สถานะไม่ระบุ';

                    if ($aStatus === \App\Models\MaintenanceAssignment::STATUS_IN_PROGRESS) {
                        $badgeTone = 'bg-sky-50 text-sky-800 border-sky-300';
                        $badgeText = 'กำลังดำเนินการ';
                    } elseif ($aStatus === \App\Models\MaintenanceAssignment::STATUS_DONE) {
                        $badgeTone = 'bg-emerald-50 text-emerald-800 border-emerald-300';
                        $badgeText = 'ทำเสร็จแล้ว';
                    } elseif ($aStatus === \App\Models\MaintenanceAssignment::STATUS_CANCELLED) {
                        $badgeTone = 'bg-rose-50 text-rose-800 border-rose-300';
                        $badgeText = 'ยกเลิก';
                    }
                  @endphp
                  <div class="flex items-center justify-between gap-2 px-3 py-2">
                    <div class="flex min-w-0 items-center gap-2">
                      <div class="h-8 w-8 flex-shrink-0 overflow-hidden rounded-full border border-slate-200 bg-white">
                        <img src="{{ $worker->avatar_thumb_url }}" alt="{{ $worker->name }}" class="h-full w-full object-cover">
                      </div>
                      <div class="min-w-0">
                        <div class="truncate text-[13px] font-semibold text-slate-900">{{ $worker->name }}</div>
                        <div class="truncate text-[11px] sm:text-[12px] text-slate-500">{{ $worker->role_label }}</div>
                      </div>
                    </div>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] sm:text-[12px] font-medium {{ $badgeTone }}">
                      {{ $badgeText }}
                    </span>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION 3 --}}
      <section class="px-6 py-5 border-b border-slate-200">
        <header class="mb-3 border-b border-slate-200 pb-2">
          <h2 class="text-[15px] font-semibold text-slate-900">ส่วนที่ 3 — รายงานการปฏิบัติงานและค่าใช้จ่าย</h2>
          <p class="mt-0.5 text-xs sm:text-[13px] text-slate-500">สรุปการปฏิบัติงาน วิธีการคิดค่าใช้จ่าย และรายละเอียดประกอบ</p>
        </header>

        @can('update', $req)
          <form method="post"
                action="{{ route('maintenance.requests.operation-log', ['maintenanceRequest' => $req]) }}"
                class="space-y-3 rounded-md border border-slate-200 bg-slate-50 px-4 py-4"
                novalidate>
            @csrf

            <div>
              <label for="operation_date" class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
                รายการซ่อมสำหรับวันที่
              </label>
              <input id="operation_date" type="date" name="operation_date"
                     value="{{ old('operation_date', optional($opLog?->operation_date)->format('Y-m-d')) }}"
                     class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[14px] sm:text-[15px] text-slate-900 focus:border-emerald-600 focus:ring-emerald-600">
              @if(!$opLog)
                <p class="mt-1 text-[11px] sm:text-[12px] text-slate-400">ยังไม่ได้บันทึก จะเริ่มบันทึกจากฟิลด์นี้</p>
              @endif
            </div>

            <div>
              <span class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
                วิธีการปฏิบัติ / การคิดค่าใช้จ่าย
              </span>
              @php $method = old('operation_method', $opLog->operation_method ?? null); @endphp
              <div class="space-y-1.5 text-xs sm:text-[13px]">
                <label class="inline-flex items-center gap-2">
                  <input type="radio" name="operation_method" value="requisition" @checked($method === 'requisition')
                         class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>ตามใบเบิกครุภัณฑ์ / วัสดุ</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="radio" name="operation_method" value="service_fee" @checked($method === 'service_fee')
                         class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>ค่าบริการ / ค่าแรงช่าง</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="radio" name="operation_method" value="other" @checked($method === 'other')
                         class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>อื่น ๆ</span>
                </label>
              </div>
            </div>

            <div>
              <label for="property_code" class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
                ระบุรพจ. (รหัสครุภัณฑ์)
              </label>
              <input id="property_code" type="text" name="property_code"
                     value="{{ old('property_code', $opLog->property_code ?? ($req->asset?->code ?? '')) }}"
                     class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[14px] sm:text-[15px] text-slate-900 focus:border-emerald-600 focus:ring-emerald-600"
                     placeholder="เช่น 68101068718">
            </div>

            <div class="pt-1">
              <label class="inline-flex items-center gap-2 text-xs sm:text-[13px] text-slate-700">
                <input type="checkbox" name="require_precheck" value="1"
                       @checked(old('require_precheck', $opLog->require_precheck ?? false))
                       class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                ยืนยันว่าได้แจ้งผู้ใช้งาน / หน่วยงาน และขออนุญาตก่อนปฏิบัติงาน/ปิดเครื่อง
              </label>
            </div>

            <div>
              <div class="mb-1 text-xs sm:text-[13px] font-medium text-slate-700">ประเภทงานที่ปฏิบัติ</div>
              <div class="space-y-1 text-xs sm:text-[13px]">
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="issue_software" value="1"
                         @checked(old('issue_software', $opLog->issue_software ?? false))
                         class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  Software
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="issue_hardware" value="1"
                         @checked(old('issue_hardware', $opLog->issue_hardware ?? false))
                         class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  Hardware
                </label>
              </div>
            </div>

            <div>
              <label for="remark" class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
                หมายเหตุ / รายละเอียดประกอบ
              </label>
              <textarea id="remark" name="remark" rows="3"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[14px] sm:text-[15px] text-slate-900 focus:border-emerald-600 focus:ring-emerald-600"
                        placeholder="เช่น ตรวจเช็คแล้วพบว่า..., ผู้ใช้ทดสอบแล้วเรียบร้อย">{{ old('remark', $opLog->remark ?? '') }}</textarea>
            </div>

            <div class="flex items-center justify-end pt-1">
              <button type="submit"
                      class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-xs sm:text-[13px] font-semibold text-white hover:bg-emerald-700">
                บันทึกรายงานการปฏิบัติงาน
              </button>
            </div>
          </form>
        @else
          <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-4 text-xs sm:text-[13px] text-slate-600">
            คุณไม่มีสิทธิ์แก้ไขรายงานการปฏิบัติงาน (Operation Log)
          </div>
        @endcan

        @if($opLog)
          <p class="mt-2 text-[11px] sm:text-[12px] text-slate-500">
            บันทึกล่าสุดโดย {{ $opLog->user?->name ?? 'ไม่ระบุผู้บันทึก' }}
            เมื่อ {{ $opLog->updated_at?->format('Y-m-d H:i') ?? '-' }}
          </p>
        @endif
      </section>

      {{-- SECTION 4 --}}
      <section class="px-6 py-5">
        <header class="mb-3 border-b border-slate-200 pb-2">
          <h2 class="text-[15px] font-semibold text-slate-900">ส่วนที่ 4 — ไฟล์แนบ / รูปถ่ายประกอบ</h2>
          <p class="mt-0.5 text-xs sm:text-[13px] text-slate-500">เพิ่มรูปถ่ายหรือเอกสารที่เกี่ยวข้องกับงานซ่อม</p>
        </header>

        <div class="space-y-5 text-sm">

          @can('attach', $req)
            <form method="post" enctype="multipart/form-data"
                  action="{{ route('maintenance.requests.attachments', $req) }}"
                  class="space-y-4 rounded-md border border-slate-200 bg-slate-50 px-4 py-4"
                  novalidate>
              @csrf

              <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                  <label for="caption" class="mb-1 block text-sm text-slate-700">คำอธิบายไฟล์</label>
                  <input id="caption" type="text" name="caption"
                         class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                         value="{{ old('caption') }}"
                         placeholder="เช่น รูปก่อนซ่อม / รูปหลังซ่อม / ใบเสนอราคา">
                </div>

                <div class="md:col-span-1">
                  <label for="file" class="mb-1 block text-sm text-slate-700">เลือกไฟล์ <span class="text-rose-500">*</span></label>
                  <input id="file" type="file" name="file" required accept="image/*,application/pdf"
                         class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                  <p class="mt-1 text-[11px] sm:text-[12px] text-slate-500">
                    รองรับรูปภาพ และ PDF • สูงสุดไฟล์ละ 10MB
                  </p>
                </div>

                <div class="md:col-span-1">
                  <label for="alt_text" class="mb-1 block text-sm text-slate-700">Alt text (เพื่อการเข้าถึง)</label>
                  <input id="alt_text" type="text" name="alt_text"
                         class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"
                         value="{{ old('alt_text') }}"
                         placeholder="ข้อความอธิบายรูปภาพ">
                  <label class="mt-2 inline-flex items-center gap-2 text-xs sm:text-[13px] text-slate-700">
                    <input type="checkbox" name="is_private" value="1" class="rounded border-slate-300">
                    เก็บเป็นไฟล์ส่วนตัว
                  </label>
                </div>
              </div>

              <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">
                  อัปโหลดไฟล์
                </button>
              </div>
            </form>
          @else
            <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-4 text-xs sm:text-[13px] text-slate-600">
              คุณไม่มีสิทธิ์แนบไฟล์ในใบงานนี้
            </div>
          @endcan

          <div class="border-t border-slate-100 pt-4">
            @if($atts->count())
              <div class="mb-2 text-xs sm:text-[13px] font-medium text-slate-500">
                ไฟล์ที่แนบไว้แล้ว ({{ $atts->count() }} ไฟล์)
              </div>
              <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                @foreach($atts as $att)
                  @php
                    $name      = $att->filename;
                    $isPrivate = (bool) $att->is_private;
                    $openUrl   = $att->url ?? route('attachments.show', $att);
                    $isImg     = $att->is_image;

                    // ✅ private เปิดได้เฉพาะคนที่แก้ไขงานได้
                    $canOpenPrivate = auth()->check() && auth()->user()->can('update', $req);
                    $canOpen = !$isPrivate || $canOpenPrivate;
                  @endphp

                  <figure class="overflow-hidden rounded-lg border border-slate-200 bg-white text-xs sm:text-[13px]">
                    @if($isImg && !$isPrivate && $att->url)
                      <a href="{{ $openUrl }}" target="_blank" rel="noopener">
                        <img src="{{ $att->url }}" alt="{{ $att->alt_text ?? $name }}" class="h-32 w-full object-cover">
                      </a>
                    @else
                      <div class="grid h-32 w-full place-items-center text-slate-500 text-[13px]">
                        {{ strtoupper(pathinfo($name, PATHINFO_EXTENSION) ?: 'FILE') }}
                      </div>
                    @endif

                    <figcaption class="flex items-center justify-between gap-2 px-3 py-2">
                      <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] sm:text-[12px] font-medium
                                   {{ $isPrivate ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-200 bg-slate-50 text-slate-700' }}">
                        {{ $isPrivate ? 'private' : 'public' }}
                      </span>

                      <span class="truncate text-slate-600 text-[12px] sm:text-[13px]" title="{{ $name }}">{{ $name }}</span>

                      @if($canOpen)
                        <a href="{{ $openUrl }}" {{ $isPrivate ? '' : 'target=_blank rel=noopener' }}
                           class="inline-flex items-center rounded-md border border-sky-300 bg-sky-50 px-2 py-1 text-[11px] sm:text-[12px] font-medium text-sky-800 hover:bg-sky-100">
                          เปิด
                        </a>
                      @else
                        <span class="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] sm:text-[12px] font-medium text-slate-500">
                          ไม่อนุญาต
                        </span>
                      @endif
                    </figcaption>
                  </figure>
                @endforeach
              </div>
            @else
              <p class="text-xs sm:text-[13px] text-slate-500">ยังไม่มีไฟล์แนบในใบงานนี้</p>
            @endif
          </div>
        </div>
      </section>

    </div>
  </div>

  {{-- ✅ MODAL: มอบหมาย / แก้ไขทีมช่าง (ใช้ assign) --}}
  @can('assign', $req)
    <div id="assignModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/40">
      <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <div class="flex items-center gap-2">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="9" cy="7" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
            </span>
            <div>
              <div class="text-sm font-semibold text-slate-900">มอบหมาย / แก้ไขทีมช่าง</div>
              <p class="text-xs sm:text-[13px] text-slate-500">เลือกผู้ปฏิบัติงานที่สามารถรับผิดชอบงานซ่อมนี้</p>
            </div>
          </div>
          <button type="button" id="closeAssignModalBtn"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
              <path d="M6 6l12 12M18 6L6 18"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>

        {{-- ⚠️ ต้องมี route นี้จริง ไม่งั้น 404
            ถ้ายังไม่มี ให้ไปเพิ่ม route + controller สำหรับ assignments.store
        --}}
        <form method="POST"
              action="{{ route('maintenance.assignments.store', ['req' => $req->id]) }}"
              class="px-4 py-3 space-y-3">
          @csrf

          <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
            <div class="mb-2 flex items-center justify-between gap-2">
              <div class="text-xs sm:text-[13px] font-medium text-slate-700">รายชื่อทีมช่างทั้งหมด</div>
            </div>

            <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
              @forelse($allWorkers as $worker)
                @php $checked = $workers->contains('id', $worker->id); @endphp
                <label class="flex items-center justify-between gap-2 rounded-md px-2 py-1.5 text-xs sm:text-[13px] hover:bg-white">
                  <div class="flex min-w-0 items-center gap-2">
                    <input type="checkbox" name="user_ids[]" value="{{ $worker->id }}" @checked($checked)
                           class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div class="min-w-0">
                      <div class="truncate font-medium text-slate-900">{{ $worker->name }}</div>
                      <div class="truncate text-[11px] sm:text-[12px] text-slate-500">{{ $worker->role_label }}</div>
                    </div>
                  </div>
                </label>
              @empty
                <p class="text-xs sm:text-[13px] text-slate-500">ยังไม่มีผู้ใช้ที่เป็นทีมช่างในระบบ</p>
              @endforelse
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-1 pb-3">
            <button type="button" id="cancelAssignModalBtn"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs sm:text-[13px] font-medium text-slate-700 hover:bg-slate-50">
              ยกเลิก
            </button>
            <button type="submit"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-1.5 text-xs sm:text-[13px] font-semibold text-white hover:bg-indigo-700">
              บันทึกการมอบหมาย
            </button>
          </div>
        </form>
      </div>
    </div>
  @endcan
@endsection

@push('scripts')
<script>
  (function(){
    const btn = document.getElementById('copyIdBtn');
    if (!btn) return;
    btn.addEventListener('click', async () => {
      const idText = (document.getElementById('rid')?.textContent || '{{ '#'.$req->id }}').replace('#','');
      try {
        await navigator.clipboard.writeText(idText);
        const old = btn.textContent;
        btn.classList.add('bg-slate-900','text-white','border-slate-900');
        btn.textContent = 'คัดลอกแล้ว';
        setTimeout(()=> {
          btn.classList.remove('bg-slate-900','text-white','border-slate-900');
          btn.textContent = old;
        }, 1200);
      } catch(e) {}
    });
  })();

  (function() {
    const modal     = document.getElementById('assignModal');
    const openBtn   = document.getElementById('openAssignModalBtn');
    const closeBtn  = document.getElementById('closeAssignModalBtn');
    const cancelBtn = document.getElementById('cancelAssignModalBtn');

    function openModal() {
      if (!modal) return;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);

    modal?.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeModal();
    });
  })();
</script>
@endpush
