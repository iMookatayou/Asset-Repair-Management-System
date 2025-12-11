{{-- resources/views/maintenance/requests/create.blade.php --}}
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

        {{-- ปุ่ม Back ใช้สไตล์เดียวกับปุ่มด้านล่าง --}}
        <a href="{{ route('maintenance.requests.index') }}"
           class="maint-btn maint-btn-outline">
          <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          กลับ
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
      class="maint-form rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
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
           class="maint-btn maint-btn-outline">
          ยกเลิก
        </a>
        <button type="submit"
                class="maint-btn maint-btn-primary">
          บันทึก
        </button>
      </div>
    </form>
  </div>
@endsection

{{-- ===========================
     Tom Select + Styling
=========================== --}}
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
  /* ====== Scope ทั้งหมดให้เฉพาะหน้า Create Maintenance ====== */

  /* ให้ input / select ปกติสูงเท่ากัน + font-size เท่ากัน */
  .maint-form input[type="text"],
  .maint-form input[type="date"],
  .maint-form input[type="number"],
  .maint-form select:not([multiple]) {
    height: 44px;
    border-radius: 0.75rem;
    box-sizing: border-box;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    font-size: 0.875rem;       /* text-sm */
    line-height: 1.25rem;
  }

  /* ========== ปุ่ม (Back / ยกเลิก / บันทึก) ========== */
  .maint-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 1rem;
    height: 44px;              /* ให้เท่ากับช่องกรอก */
    border-radius: 0.75rem;
    font-size: 0.875rem;       /* text-sm */
    line-height: 1.25rem;
    font-weight: 500;
    border: 1px solid rgb(148,163,184);
    background-color: #ffffff;
    color: rgb(51,65,85);
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    text-decoration: none;
    gap: 0.25rem;              /* ช่องระหว่าง icon กับ text ให้เท่ากัน */
  }

  .maint-btn svg {
    flex-shrink: 0;
  }

  .maint-btn:hover {
    background-color: rgb(248,250,252);
  }

  .maint-btn-outline {
    /* ใช้ค่า default ด้านบน */
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

  /* ========== TomSelect เฉพาะในฟอร์มนี้ ========== */
  .maint-form .ts-wrapper.ts-basic {
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    background: transparent;
  }

  .maint-form .ts-wrapper.ts-basic .ts-control {
    border-radius: 0.75rem;               /* ใกล้ rounded-xl */
    border: 1px solid rgb(226,232,240);   /* slate-200 */
    padding: 0 0.75rem;                   /* ให้เหมือน input */
    box-shadow: none;
    min-height: 44px;                     /* สูงเท่าช่องกรอกอื่น */
    background-color: #fff;
    display: flex;
    align-items: center;
    font-size: 0.875rem;                  /* text-sm */
    line-height: 1.25rem;
  }

  /* เวลามีไอคอนแว่นขยาย ให้ขยับ text เข้าไปหน่อย */
  .maint-form .ts-wrapper.ts-basic.ts-with-icon .ts-control {
    padding-left: 2.6rem;                 /* เผื่อที่ให้ไอคอนด้านซ้าย */
  }

  .maint-form .ts-wrapper.ts-basic .ts-control input {
    font-size: 0.875rem;                  /* text-sm */
    line-height: 1.25rem;
  }

  .maint-form .ts-wrapper.ts-basic .ts-control.focus {
    border-color: rgb(5,150,105);         /* emerald-600 */
    box-shadow: none;
  }

  .maint-form .ts-wrapper.ts-basic .ts-dropdown {
    border-radius: 0.5rem;
    border-color: rgb(226,232,240);       /* slate-200 */
    box-shadow: 0 10px 15px -3px rgba(15,23,42,0.15);
    z-index: 50;
    font-size: 0.875rem;                  /* text-sm ให้ dropdown text เท่ากันด้วย */
    line-height: 1.25rem;
  }

  /* กรณี error ให้กรอบแดง */
  .maint-form .ts-wrapper.ts-basic.ts-error .ts-control {
    border-color: rgb(248,113,113) !important; /* rose-400 */
  }

  /* ===== ไอคอนแว่นขยายบนกล่องหลัก ===== */
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
    color: rgb(148,163,184);   /* text-slate-400 */
  }

  .maint-form .ts-wrapper.ts-with-icon .ts-select-icon svg {
    width: 16px;
    height: 16px;
  }

  .maint-form select.ts-hidden-accessible {
    display: none !important;
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
        // ❗ ไม่มี plugin 'dropdown_input' แล้ว
        // search จะเกิดในกล่องหลักเลย
        maxOptions: 500,
        sortField: { field: 'text', direction: 'asc' },
        placeholder: placeholderText,
        searchField: ['text'],   // ให้ค้นจาก text ของ option
      });

      // เพิ่ม class + icon ลงใน wrapper ให้กลายเป็น select แบบมีแว่นขยาย
      const wrapper = ts.wrapper; // <div class="ts-wrapper ...">
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

    // ทรัพย์สิน: กล่องเดียว = เลือก + ค้นหาในตัว
    initTomSelectWithIcon('#asset_id', '— เลือกทรัพย์สิน —');

    // หน่วยงาน: กล่องเดียว = เลือก + ค้นหาในตัว
    initTomSelectWithIcon('#department_id', '— เลือกหน่วยงาน —');
  });
</script>
