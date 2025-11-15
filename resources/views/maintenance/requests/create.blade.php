@extends('layouts.app')
@section('title','Create Maintenance')

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create Maintenance
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            สร้างคำขอซ่อมใหม่ — ระบุทรัพย์สิน หัวข้อ และรายละเอียดให้ครบถ้วน
          </p>
        </div>

        <a href="{{ route('maintenance.requests.index') }}"
           class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <p class="font-medium">มีข้อผิดพลาดในการบันทึกข้อมูล:</p>
        <ul class="mt-2 list-disc pl-5 text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
      action="{{ route('maintenance.requests.store') }}"
      enctype="multipart/form-data"
      class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
      novalidate
      aria-label="แบบฟอร์มสร้างคำขอซ่อม">
    @csrf

        @include('maintenance.requests._form', [
            'req'         => null,
            'assets'      => $assets ?? [],
            'depts'       => $depts ?? [],
            'attachments' => [],   // สร้างใหม่ยังไม่มีไฟล์
        ])

        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('maintenance.requests.index') }}"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
            ยกเลิก
            </a>
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
            บันทึก
            </button>
        </div>
    </form>
  </div>
@endsection

{{-- ===========================
     Tom Select + Styling ช่องเดียว
=========================== --}}
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
  /* ไม่ให้ wrapper มีกรอบซ้อน เพิ่มเฉพาะกรอบใน control */
  .ts-wrapper.ts-basic {
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    background: transparent;
  }

  .ts-wrapper.ts-basic .ts-control {
    border-radius: 0.75rem;               /* ใกล้ rounded-xl */
    border: 1px solid rgb(226,232,240);   /* slate-200 */
    padding: 0.5rem 0.75rem;              /* px-3 py-2 */
    box-shadow: none;
    min-height: auto;
    background-color: #fff;
  }

  /* เวลามีไอคอนแว่นขยาย ให้ขยับ text เข้าไปหน่อย */
  .ts-wrapper.ts-basic.ts-with-icon .ts-control {
    padding-left: 2.6rem;                 /* เผื่อที่ให้ไอคอนด้านซ้าย */
  }

  .ts-wrapper.ts-basic .ts-control input {
    font-size: 0.875rem;                  /* text-sm */
  }

  .ts-wrapper.ts-basic .ts-control.focus {
    border-color: rgb(5,150,105);         /* emerald-600 */
    box-shadow: none;
  }

  .ts-wrapper.ts-basic .ts-dropdown {
    border-radius: 0.5rem;
    border-color: rgb(226,232,240);       /* slate-200 */
    box-shadow: 0 10px 15px -3px rgba(15,23,42,0.15);
  }

  /* กรณี error ให้กรอบแดง */
  .ts-wrapper.ts-basic.ts-error .ts-control {
    border-color: rgb(248,113,113) !important; /* rose-400 */
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('asset_id')) {
      new TomSelect('#asset_id', {
        create: false,
        allowEmptyOption: true,
        plugins: ['dropdown_input'],
        sortField: { field: 'text', direction: 'asc' },
        placeholder: '— เลือกทรัพย์สิน —',
        maxOptions: 500,
      });
    }

    if (document.getElementById('department_id')) {
      new TomSelect('#department_id', {
        create: false,
        allowEmptyOption: true,
        plugins: ['dropdown_input'],
        sortField: { field: 'text', direction: 'asc' },
        placeholder: '— เลือกหน่วยงาน —',
        maxOptions: 500,
      });
    }
  });
</script>
