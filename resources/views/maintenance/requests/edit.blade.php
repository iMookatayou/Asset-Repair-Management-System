{{-- resources/views/maintenance/requests/edit.blade.php --}}
@extends('layouts.app')

@php
  use App\Models\MaintenanceRequest;

  /** @var \App\Models\MaintenanceRequest $mr */
  $mr   = $mr instanceof MaintenanceRequest ? $mr : new MaintenanceRequest();
  $opLog = $mr->operationLog; // รายงานการปฏิบัติงาน (ถ้ามี)
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
            แก้ไขคำขอซ่อม — ปรับข้อมูลให้ถูกต้องและบันทึกการเปลี่ยนแปลง
          </p>
          <p class="mt-0.5 text-xs text-slate-500">
            คำขอซ่อม #{{ $mr->id }}
            @if ($mr->updated_at)
              · แก้ไขล่าสุด {{ $mr->updated_at->format('Y-m-d H:i') }}
            @endif
          </p>
        </div>

        <div class="flex items-center gap-2">
          <a
            href="{{ route('maintenance.requests.index') }}"
            class="maint-btn maint-btn-outline"
          >
            <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            กลับ
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="pt-3 md:pt-4"></div>

  {{-- กล่อง error รวม --}}
  @if ($errors->any())
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 mb-4">
      <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <p class="font-medium text-sm">มีข้อผิดพลาดในการบันทึกข้อมูล:</p>
        <ul class="mt-2 list-disc pl-5 text-sm space-y-0.5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- กล่อง 1: แก้ไขใบงานหลัก --}}
    <div class="maint-form-wrapper rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <form
        method="POST"
        action="{{ route('maintenance.requests.update', $mr) }}"
        enctype="multipart/form-data"
        class="maint-form space-y-6"
        novalidate
      >
        @csrf
        @method('PUT')

        {{-- ใช้ฟอร์มเดียวกับหน้า Create --}}
        @include('maintenance.requests._form', [
            'mode'        => 'edit',
            'mr'          => $mr,
            'req'         => $mr,
            'assets'      => $assets      ?? collect(),
            'users'       => $users       ?? collect(),
            'attachments' => $attachments ?? collect(),
            'depts'       => $depts       ?? collect(),
            'departments' => $depts       ?? collect(),
        ])

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 mt-2">
          <a
            href="{{ route('maintenance.requests.index') }}"
            class="maint-btn maint-btn-outline"
          >
            ยกเลิก
          </a>
          <button
            type="submit"
            class="maint-btn maint-btn-primary"
          >
            บันทึกการแก้ไข
          </button>
        </div>
      </form>
    </div>

    {{-- กล่อง 2: รายงานการปฏิบัติงาน / ใบเบิก / รพจ. (มีชุดเดียว) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <header class="mb-3 border-b border-slate-200 pb-2">
        <h2 class="text-[15px] font-semibold text-slate-900">
          รายงานการปฏิบัติงานและค่าใช้จ่าย
        </h2>
        <p class="mt-0.5 text-xs sm:text-[13px] text-slate-500">
          บันทึกวิธีการซ่อม การคิดค่าใช้จ่าย และรหัสครุภัณฑ์ (รพจ.) สำหรับใบงานนี้
        </p>
      </header>

      <form method="POST"
            action="{{ route('maintenance.requests.operation-log', $mr) }}"
            class="space-y-3 rounded-md border border-slate-200 bg-slate-50 px-4 py-4"
            novalidate>
        @csrf

        {{-- วันที่รายการซ่อม --}}
        <div>
          <label for="operation_date" class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
            รายการซ่อมสำหรับวันที่
          </label>
          <input
            id="operation_date"
            type="date"
            name="operation_date"
            value="{{ old('operation_date', optional(optional($opLog)->operation_date)->format('Y-m-d')) }}"
            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[14px] sm:text-[15px] text-slate-900 focus:border-emerald-600 focus:ring-emerald-600"
          >
          @if(!$opLog)
            <p class="mt-1 text-[11px] sm:text-[12px] text-slate-400">ยังไม่ได้บันทึก จะเริ่มบันทึกจากฟิลด์นี้</p>
          @endif
        </div>

        {{-- วิธีการปฏิบัติ / การคิดค่าใช้จ่าย --}}
        <div>
          <span class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
            วิธีการปฏิบัติ / การคิดค่าใช้จ่าย
          </span>
          @php
            $method = old('operation_method', optional($opLog)->operation_method);
          @endphp
          <div class="space-y-1.5 text-xs sm:text-[13px]">
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="operation_method" value="requisition"
                     @checked($method === 'requisition')
                     class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              <span>ตามใบเบิกครุภัณฑ์ / วัสดุ</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="operation_method" value="service_fee"
                     @checked($method === 'service_fee')
                     class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              <span>ค่าบริการ / ค่าแรงช่าง</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="operation_method" value="other"
                     @checked($method === 'other')
                     class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              <span>อื่น ๆ</span>
            </label>
          </div>
        </div>

        {{-- รพจ. (รหัสครุภัณฑ์) --}}
        <div>
          <label for="property_code" class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
            ระบุรพจ. (รหัสครุภัณฑ์)
          </label>
          <input
            id="property_code"
            type="text"
            name="property_code"
            value="{{ old('property_code', optional($opLog)->property_code ?? optional($mr->asset)->asset_code ?? '') }}"
            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[14px] sm:text-[15px] text-slate-900 focus:border-emerald-600 focus:ring-emerald-600"
            placeholder="เช่น 68101068718"
          >
          <p class="mt-0.5 text-[11px] sm:text-[12px] text-slate-500">
            ระบุเลขทะเบียนครุภัณฑ์ (รพจ.) ที่ใช้ในการเบิก/ซ่อม หากมี
          </p>
        </div>

        {{-- ยืนยันแจ้งผู้ใช้งาน / หน่วยงาน --}}
        <div class="pt-1">
          <label class="inline-flex items-center gap-2 text-xs sm:text-[13px] text-slate-700">
            <input
              type="checkbox"
              name="require_precheck"
              value="1"
              @checked(old('require_precheck', optional($opLog)->require_precheck))
              class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            >
            ยืนยันว่าได้แจ้งผู้ใช้งาน / หน่วยงาน และขออนุญาตก่อนปฏิบัติงาน/ปิดเครื่อง
          </label>
          <p class="mt-0.5 text-[11px] sm:text-[12px] text-slate-500">
            ใช้บันทึกว่าช่างได้แจ้งผลกระทบและขออนุญาตผู้เกี่ยวข้องแล้ว
          </p>
        </div>

        {{-- ประเภทงานที่ปฏิบัติ --}}
        <div>
          <div class="mb-1 text-xs sm:text-[13px] font-medium text-slate-700">
            ประเภทงานที่ปฏิบัติ
          </div>
          <div class="space-y-1 text-xs sm:text-[13px]">
            <label class="inline-flex items-center gap-2">
              <input
                type="checkbox"
                name="issue_software"
                value="1"
                @checked(old('issue_software', optional($opLog)->issue_software))
                class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
              >
              Software
            </label>
            <label class="inline-flex items-center gap-2">
              <input
                type="checkbox"
                name="issue_hardware"
                value="1"
                @checked(old('issue_hardware', optional($opLog)->issue_hardware))
                class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
              >
              Hardware
            </label>
          </div>
        </div>

        {{-- หมายเหตุ --}}
        <div>
          <label for="remark" class="mb-1 block text-xs sm:text-[13px] font-medium text-slate-700">
            หมายเหตุ / รายละเอียดประกอบ
          </label>
          <textarea
            id="remark"
            name="remark"
            rows="3"
            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[14px] sm:text-[15px] text-slate-900 focus:border-emerald-600 focus:ring-emerald-600"
            placeholder="เช่น ตรวจเช็คเบื้องต้นแล้ว พบว่า..., ใช้อะไหล่จากใบเบิกเลขที่..., ผู้ใช้ทดสอบแล้วเรียบร้อย"
          >{{ old('remark', optional($opLog)->remark ?? '') }}</textarea>
        </div>

        <div class="flex items-center justify-end pt-1">
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-xs sm:text-[13px] font-semibold text-white hover:bg-emerald-700">
            <svg class="mr-1.5 h-4 w-4" viewBox="0 0 24 24" fill="none">
              <path d="M5 12h14M12 5l7 7-7 7"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            บันทึกรายงานการปฏิบัติงาน
          </button>
        </div>
      </form>

      @if($opLog)
        <p class="mt-2 text-[11px] sm:text-[12px] text-slate-500">
          บันทึกล่าสุดโดย {{ optional($opLog->user)->name ?? 'ไม่ระบุผู้บันทึก' }}
          เมื่อ {{ optional($opLog->updated_at)->format('Y-m-d H:i') ?? '-' }}
        </p>
      @endif
    </div>
  </div>
@endsection

{{-- ===========================
     Tom Select + Styling
     (เหมือนหน้า Create)
=========================== --}}
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

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

  .maint-btn svg {
    flex-shrink: 0;
  }

  .maint-btn:hover {
    background-color: rgb(248,250,252);
  }

  .maint-btn-primary {
    border-color: rgb(5,150,105);
    background-color: rgb(5,150,105);
    color: #ffffff;
  }

  .maint-btn-primary:hover {
    background-color: rgb(4,120,87);
    border-color: rgb(4,120,87);
  }

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

  .maint-form .ts-wrapper.ts-basic .ts-control input {
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

  .maint-form .ts-wrapper.ts-with-icon {
    position: relative;
  }

  .maint-form .ts-wrapper.ts-with-icon .ts-select-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    color: rgb(148,163,184);
  }

  .maint-form .ts-wrapper.ts-with-icon .ts-select-icon svg {
    width: 16px;
    height: 16px;
  }

  .maint-form .ts-wrapper.ts-with-icon .ts-control {
    padding-left: 2.6rem;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {

    function initTomSelectWithIcon(selector, placeholderText) {
      const el = document.querySelector(selector);
      if (!el) return;

      const ts = new TomSelect(selector, {
        create: false,
        allowEmptyOption: true,
        maxOptions: 500,
        sortField: { field: 'text', direction: 'asc' },
        placeholder: placeholderText,
        searchField: ['text'],
      });

      const wrapper = ts.wrapper;
      if (wrapper) {
        wrapper.classList.add('ts-with-icon');

        const icon = document.createElement('span');
        icon.className = 'ts-select-icon';
        icon.innerHTML = `
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15.5 15.5L20 20" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="11" cy="11" r="5"
                    stroke="currentColor" stroke-width="2" />
          </svg>
        `;
        wrapper.insertBefore(icon, wrapper.firstChild);
      }
    }

    initTomSelectWithIcon('#asset_id', '— เลือกทรัพย์สิน —');
    initTomSelectWithIcon('#department_id', '— เลือกหน่วยงาน —');
  });
</script>
