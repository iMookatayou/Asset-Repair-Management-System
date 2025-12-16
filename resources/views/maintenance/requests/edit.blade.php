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
@endphp

@section('title','Edit Maintenance #'.$mr->id)

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Edit Maintenance
          </h1>

          <p class="mt-1 text-sm text-slate-600">
            แก้ไขคำขอซ่อม — ปรับข้อมูลให้ถูกต้อง
          </p>

          <p class="mt-0.5 text-xs text-slate-500">
            คำขอซ่อม #{{ $mr->id }}
            @if ($mr->updated_at)
              · แก้ไขล่าสุด {{ $mr->updated_at->format('Y-m-d H:i') }}
            @endif
          </p>
        </div>

        <a href="{{ route('maintenance.requests.index') }}"
           class="maint-btn maint-btn-outline">
          ← กลับ
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="pt-4"></div>

  {{-- ===== Guard UX ===== --}}
  @cannot('update', $mr)
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
      <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        คุณไม่มีสิทธิ์แก้ไขใบงานนี้
      </div>
    </div>
    @return
  @endcannot

  {{-- ===== Error ===== --}}
  @if ($errors->any())
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 mb-4">
      <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <ul class="list-disc pl-5 text-sm space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- =========================
         กล่อง 1: ข้อมูลใบงาน
    ========================= --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <form method="POST"
            action="{{ route('maintenance.requests.update', $mr) }}"
            class="maint-form space-y-6"
            enctype="multipart/form-data"
            novalidate>
        @csrf
        @method('PUT')

        @include('maintenance.requests._form', [
          'req'         => $mr,
          'assets'      => $assets      ?? collect(),
          'depts'       => $depts       ?? collect(),
          'attachments' => $attachments ?? [],
        ])

        <div class="flex justify-end gap-2 border-t pt-4">
          <a href="{{ route('maintenance.requests.index') }}"
             class="maint-btn maint-btn-outline">
            ยกเลิก
          </a>
          <button type="submit" class="maint-btn maint-btn-primary">
            บันทึกการแก้ไข
          </button>
        </div>
      </form>
    </div>

    {{-- =========================
         กล่อง 2: Operation Log (เหมือน show)
         (เฉพาะ Team)
    ========================= --}}
    @if($isTeam)
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-3 border-b border-slate-200 pb-2">
          <h2 class="text-sm font-semibold text-slate-900">
            รายงานการปฏิบัติงานและค่าใช้จ่าย
          </h2>
          <p class="mt-0.5 text-xs text-slate-500">
            สำหรับทีมช่าง: ระบุใบเบิก/ค่าแรง/อื่น ๆ, เลข รพจ. และรายละเอียดประกอบ
          </p>
        </header>

        <form method="POST"
              action="{{ route('maintenance.requests.operation-log', ['maintenanceRequest' => $mr]) }}"
              class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4"
              novalidate>
          @csrf

          {{-- วันที่รายการซ่อม --}}
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">
              รายการซ่อมสำหรับวันที่
            </label>
            <input type="date"
                   name="operation_date"
                   value="{{ old('operation_date', optional($opLog?->operation_date)->format('Y-m-d')) }}"
                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
            @if(!$opLog)
              <p class="mt-1 text-[11px] text-slate-400">ยังไม่ได้บันทึก จะเริ่มบันทึกจากฟิลด์นี้</p>
            @endif
          </div>

          {{-- วิธีการปฏิบัติงาน --}}
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">
              วิธีการปฏิบัติ / การคิดค่าใช้จ่าย
            </label>
            @php $method = old('operation_method', $opLog->operation_method ?? null); @endphp
            <div class="space-y-1 text-sm">
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="operation_method" value="requisition" @checked($method==='requisition')>
                ตามใบเบิกครุภัณฑ์ / วัสดุ
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="operation_method" value="service_fee" @checked($method==='service_fee')>
                ค่าบริการ / ค่าแรงช่าง
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="operation_method" value="other" @checked($method==='other')>
                อื่น ๆ
              </label>
            </div>
          </div>

          {{-- รพจ --}}
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">
              ระบุรพจ. (รหัสครุภัณฑ์)
            </label>
            <input type="text"
                   name="property_code"
                   value="{{ old('property_code', $opLog->property_code ?? ($mr->asset->code ?? $mr->asset->asset_code ?? '')) }}"
                   placeholder="เช่น 68101068718"
                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
          </div>

          {{-- require_precheck --}}
          <div class="pt-1">
            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
              <input type="checkbox" name="require_precheck" value="1"
                     @checked(old('require_precheck', $opLog->require_precheck ?? false))>
              ยืนยันว่าได้แจ้งผู้ใช้งาน / หน่วยงาน และขออนุญาตก่อนปฏิบัติงาน/ปิดเครื่อง
            </label>
          </div>

          {{-- issue type --}}
          <div>
            <div class="mb-1 text-xs font-medium text-slate-700">ประเภทงานที่ปฏิบัติ</div>
            <div class="space-y-1 text-sm">
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="issue_software" value="1"
                       @checked(old('issue_software', $opLog->issue_software ?? false))>
                Software
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="issue_hardware" value="1"
                       @checked(old('issue_hardware', $opLog->issue_hardware ?? false))>
                Hardware
              </label>
            </div>
          </div>

          {{-- หมายเหตุ --}}
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">
              หมายเหตุ / รายละเอียดประกอบ
            </label>
            <textarea name="remark"
                      rows="3"
                      placeholder="เช่น ตรวจเช็คแล้วพบว่า..., ผู้ใช้ทดสอบแล้วเรียบร้อย"
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">{{ old('remark', $opLog->remark ?? '') }}</textarea>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="maint-btn maint-btn-primary">
              บันทึกรายงานการปฏิบัติงาน
            </button>
          </div>
        </form>

        @if($opLog)
          <p class="mt-2 text-xs text-slate-500">
            แก้ไขล่าสุดโดย {{ $opLog->user->name ?? '-' }}
            · {{ $opLog->updated_at?->format('Y-m-d H:i') }}
          </p>
        @endif
      </div>
    @else
      <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
        เฉพาะทีมช่างหรือผู้ดูแลระบบเท่านั้นที่สามารถบันทึกรายงานการปฏิบัติงานได้
      </div>
    @endif

  </div>
@endsection

@push('styles')
<style>
  /* ====== Scope ทั้งหมดให้เฉพาะหน้า Edit/Create Maintenance ====== */
  .maint-form input[type="text"],
  .maint-form input[type="date"],
  .maint-form input[type="number"],
  .maint-form select:not([multiple]) {
    height: 44px;
    border-radius: 0.75rem;
    box-sizing: border-box;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .maint-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 1rem;
    height: 44px;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: 500;
    border: 1px solid rgb(148,163,184);
    background-color: #ffffff;
    color: rgb(51,65,85);
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    text-decoration: none;
    gap: 0.25rem;
  }

  .maint-btn:hover { background-color: rgb(248,250,252); }

  .maint-btn-primary {ฟ
    border-color: rgb(5,150,105);
    background-color: rgb(5,150,105);
    color: #ffffff;
  }
  .maint-btn-primary:hover {
    background-color: rgb(4,120,87);
    border-color: rgb(4,120,87);
  }

  /* TomSelect UI (ทำเฉพาะหน้าด้วย class maint-form) */
  .maint-form .ts-wrapper.ts-basic {
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    background: transparent;
  }
  .maint-form .ts-wrapper.ts-basic .ts-control {
    border-radius: 0.75rem;
    border: 1px solid rgb(226,232,240);
    padding: 0 0.75rem;
    box-shadow: none;
    min-height: 44px;
    background-color: #fff;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
  .maint-form .ts-wrapper.ts-basic .ts-control.focus {
    border-color: rgb(5,150,105);
    box-shadow: none;
  }
  .maint-form .ts-wrapper.ts-basic .ts-dropdown {
    border-radius: 0.5rem;
    border-color: rgb(226,232,240);
    box-shadow: 0 10px 15px -3px rgba(15,23,42,0.15);
    z-index: 50;
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
  .maint-form .ts-wrapper.ts-basic.ts-error .ts-control {
    border-color: rgb(248,113,113) !important;
  }
</style>
@endpush
