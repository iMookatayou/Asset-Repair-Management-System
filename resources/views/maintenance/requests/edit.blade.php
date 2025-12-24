{{-- resources/views/maintenance/requests/edit.blade.php --}}
@extends('layouts.app')

@php
  use App\Models\MaintenanceRequest;

  /** @var \App\Models\MaintenanceRequest $mr */
  $mr = $mr instanceof MaintenanceRequest ? $mr : new MaintenanceRequest();

  $opLog = $mr->operationLog;

  $user = auth()->user();
  $isTeam = $user && (
      $user->isAdmin() ||
      $user->isSupervisor() ||
      $user->isTechnician()
  );

  // ===== UI tokens (โทนเดียวกับ show) =====
  $line = 'border-slate-200';

  $input = "mt-2 w-full h-11 rounded-md border $line bg-white px-3 py-2 text-sm
            focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100";

  $textarea = "mt-2 w-full rounded-md border $line bg-white px-3 py-2 text-sm
              focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100";

  $headCls   = "flex items-start gap-3 pb-3 min-h-[56px]";
  $noCls     = "w-8 h-8 shrink-0 rounded-full border border-emerald-600 bg-emerald-600
                flex items-center justify-center text-sm font-bold text-white leading-none";
  $titleCls  = "text-base font-semibold text-slate-900 leading-tight";
  $subCls    = "text-sm text-slate-500 leading-snug";
  $accentWrap= "min-w-0 relative pl-3 pt-[1px]";
  $accentBar = "absolute left-0 top-[2px] w-[3px] h-9 rounded-full bg-emerald-600/90";

  // ===== team (read-only) =====
  $assignments = $mr->assignments ?? collect();
  $workers = $assignments->map(fn($a) => $a->user)->filter()->unique('id')->values();
@endphp

@section('title','Edit Maintenance #'.$mr->id)

@section('page-header')
  <div class="w-full bg-slate-50 border-b {{ $line }}">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

        {{-- LEFT --}}
        <div class="min-w-0">
          <div class="flex items-start gap-3">
            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl text-emerald-700">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>

            <div class="min-w-0">
              <h1 class="text-[20px] sm:text-[22px] font-semibold text-slate-900 leading-tight">
                Edit Maintenance
                <span class="ml-2 text-slate-500 text-[13px] sm:text-[14px] font-semibold">#{{ $mr->id }}</span>
              </h1>

              <div class="mt-1 text-xs sm:text-[13px] text-slate-600 flex flex-wrap gap-x-4 gap-y-1">
                <span>แก้ไขข้อมูลคำขอซ่อม</span>
                @if($mr->updated_at)
                  <span>อัปเดต: <span class="font-medium text-slate-900">{{ $mr->updated_at->format('Y-m-d H:i') }}</span></span>
                @endif
                <span>ผู้รับผิดชอบหลัก:
                  <span class="font-semibold text-slate-900">{{ $mr->technician?->name ?? 'ยังไม่มีช่างรับงาน' }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        {{-- RIGHT --}}
        <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2">
          <a href="{{ route('maintenance.requests.index') }}"
             class="inline-flex items-center gap-2 rounded-lg border {{ $line }} bg-white px-4 py-2 text-[13px] font-medium text-slate-700 hover:bg-slate-50">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back
          </a>
        </div>

      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 pb-8 pt-6">

    @cannot('update', $mr)
      <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        คุณไม่มีสิทธิ์แก้ไขใบงานนี้
      </div>
      @return
    @endcannot

    @if ($errors->any())
      <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <ul class="list-disc pl-5 text-sm space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- ===== FORM หลัก (แก้ข้อมูลคำขอ: 1-4) ===== --}}
    <form method="POST"
          action="{{ route('maintenance.requests.update', $mr) }}"
          enctype="multipart/form-data"
          class="space-y-8"
          novalidate>
      @csrf
      @method('PUT')

      @include('maintenance.requests._form', [
        'req'         => $mr,
        'assets'      => $assets      ?? collect(),
        'depts'       => $depts       ?? collect(),
        'attachments' => $attachments ?? [],
      ])

      <div class="flex justify-end gap-2 pt-4 border-t {{ $line }}">
        <a href="{{ route('maintenance.requests.index') }}"
           class="inline-flex items-center justify-center h-11 px-5 rounded-xl border {{ $line }} bg-white
                  text-sm font-medium text-slate-700 hover:bg-slate-50">
          ยกเลิก
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center h-11 px-5 rounded-xl bg-emerald-700
                       text-sm font-medium text-white hover:bg-emerald-800 focus:ring-2 focus:ring-emerald-200">
          บันทึกการแก้ไข
        </button>
      </div>
    </form>

    {{-- ===== งานช่าง: 5 ซ้าย | 6 ขวา ===== --}}
    @if($isTeam)
      <div class="mt-10 border-t {{ $line }}"></div>

      <div class="mt-10 relative grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div class="hidden lg:block absolute inset-y-0 left-1/2 w-px bg-slate-200"></div>

        {{-- LEFT: SECTION 5 --}}
        <section>
          <div class="{{ $headCls }}">
            <div class="{{ $noCls }}">5</div>
            <div class="{{ $accentWrap }}">
              <span class="{{ $accentBar }}"></span>
              <div class="{{ $titleCls }}">รายงานการปฏิบัติงานและค่าใช้จ่าย</div>
              <div class="{{ $subCls }}">สำหรับทีมช่าง: ระบุวิธีคิดค่าใช้จ่าย, รพจ. และรายละเอียดประกอบ</div>
            </div>
          </div>

          <form method="POST"
                action="{{ route('maintenance.requests.operation-log', ['maintenanceRequest' => $mr]) }}"
                class="space-y-4"
                novalidate>
            @csrf

            <div>
              <label class="block text-sm font-medium text-slate-700">รายการซ่อมสำหรับวันที่</label>
              <input type="date" name="operation_date"
                     value="{{ old('operation_date', optional($opLog?->operation_date)->format('Y-m-d')) }}"
                     class="{{ $input }}">
            </div>

            <div>
              <div class="block text-sm font-medium text-slate-700">วิธีการปฏิบัติ / การคิดค่าใช้จ่าย</div>
              @php $method = old('operation_method', $opLog->operation_method ?? null); @endphp
              <div class="mt-2 space-y-2 text-sm">
                <label class="inline-flex items-center gap-2">
                  <input type="radio" name="operation_method" value="requisition" @checked($method==='requisition')
                         class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>ตามใบเบิกครุภัณฑ์ / วัสดุ</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="radio" name="operation_method" value="service_fee" @checked($method==='service_fee')
                         class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>ค่าบริการ / ค่าแรงช่าง</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="radio" name="operation_method" value="other" @checked($method==='other')
                         class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>อื่น ๆ</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">ระบุรพจ. (รหัสครุภัณฑ์)</label>
              <input type="text" name="property_code"
                     value="{{ old('property_code', $opLog->property_code ?? ($mr->asset?->asset_code ?? '')) }}"
                     class="{{ $input }}"
                     placeholder="เช่น 68101068718">
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
              <input type="checkbox" name="require_precheck" value="1"
                     @checked(old('require_precheck', $opLog->require_precheck ?? false))
                     class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              ยืนยันว่าได้แจ้งผู้ใช้งาน / หน่วยงาน และขออนุญาตก่อนปฏิบัติงาน/ปิดเครื่อง
            </label>

            <div>
              <div class="text-sm font-medium text-slate-700">ประเภทงานที่ปฏิบัติ</div>
              <div class="mt-2 flex items-center gap-6 text-sm text-slate-700">
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="issue_software" value="1"
                         @checked(old('issue_software', $opLog->issue_software ?? false))
                         class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  Software
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="issue_hardware" value="1"
                         @checked(old('issue_hardware', $opLog->issue_hardware ?? false))
                         class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  Hardware
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">หมายเหตุ / รายละเอียดประกอบ</label>
              <textarea name="remark" rows="4" class="{{ $textarea }}"
                        placeholder="เช่น ตรวจเช็คแล้วพบว่า..., ผู้ใช้ทดสอบแล้วเรียบร้อย">{{ old('remark', $opLog->remark ?? '') }}</textarea>
            </div>

            <div class="flex justify-end">
              <button type="submit"
                      class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-xs sm:text-[13px] font-semibold text-white
                             hover:bg-emerald-800 focus:ring-2 focus:ring-emerald-200">
                บันทึกรายงานการปฏิบัติงาน
              </button>
            </div>

            @if($opLog)
              <p class="text-xs text-slate-500">
                บันทึกล่าสุดโดย {{ $opLog->user?->name ?? '-' }} · {{ $opLog->updated_at?->format('Y-m-d H:i') }}
              </p>
            @endif
          </form>
        </section>

        {{-- RIGHT: SECTION 6 --}}
        <section>
          <div class="{{ $headCls }}">
            <div class="{{ $noCls }}">6</div>
            <div class="{{ $accentWrap }}">
              <span class="{{ $accentBar }}"></span>
              <div class="{{ $titleCls }}">ทีมช่างที่รับผิดชอบ</div>
              <div class="{{ $subCls }}">ผู้ปฏิบัติงาน</div>
            </div>
          </div>

          <div class="rounded-md border {{ $line }} bg-white px-4 py-4 text-sm text-slate-700 space-y-2">
            <div>
              <span class="text-slate-500">ช่างหลัก:</span>
              <span class="font-semibold text-slate-900">{{ $mr->technician?->name ?? 'ยังไม่มีช่างรับงาน' }}</span>
            </div>

            <div class="text-slate-500">ทีมช่าง:</div>
            @if($workers->isEmpty())
              <div class="text-slate-500">— ยังไม่ได้มอบหมายทีมช่าง —</div>
            @else
              <ul class="list-disc pl-5">
                @foreach($workers as $w)
                  <li class="text-slate-800">{{ $w->name }}</li>
                @endforeach
              </ul>
            @endif

            {{-- ✅ ปุ่มมอบหมาย: เว้นว่างไว้ก่อน --}}
            <div class="pt-2">
              {{-- (เว้นไว้) --}}
            </div>
          </div>
        </section>

      </div>
    @endif

  </div>
@endsection
