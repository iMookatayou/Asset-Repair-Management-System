{{-- resources/views/assets/create.blade.php --}}
@extends('layouts.app')
@section('title','Create Asset')

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create Asset
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            เพิ่มครุภัณฑ์ใหม่เข้าสู่ระบบ — โปรดระบุข้อมูลให้ครบถ้วนเพื่อความถูกต้องในการจัดเก็บ
          </p>
        </div>

        {{-- ปุ่ม Back ใช้สไตล์เดียวกับปุ่มล่าง (เหมือน Maintenance) --}}
        <a href="{{ route('assets.index') }}"
           class="asset-btn asset-btn-outline">
          <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
      @push('scripts')
      <script>
        (function(){
          const msgs = @json($errors->all());
          const msg  = msgs.length ? ('มีข้อผิดพลาดในการบันทึก: ' + msgs.join(' • ')) : 'มีข้อผิดพลาดในการบันทึกข้อมูล';
          if (window.showToast) {
            window.showToast({ type:'error', message: msg, position:'uc', timeout: 3600, size:'lg' });
          } else {
            window.dispatchEvent(new CustomEvent('app:toast',{ detail:{ type:'error', message: msg, position:'uc', timeout:3600, size:'lg' } }));
          }
        })();
      </script>
      @endpush
    @endif

    <form method="POST" action="{{ route('assets.store') }}"
          onsubmit="window.dispatchEvent(new CustomEvent('app:toast',{detail:{type:'info',message:'กำลังบันทึก...'}}))"
          class="asset-form rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
          novalidate
          aria-label="แบบฟอร์มสร้างครุภัณฑ์">
      @csrf

      @include('assets._fields', [
        'asset'       => new \App\Models\Asset(),
        'categories'  => $categories ?? null,
        'departments' => $departments ?? null,
      ])

      <div class="mt-6 flex justify-end gap-2">
        <a href="{{ route('assets.index') }}"
           class="asset-btn asset-btn-outline">
          ยกเลิก
        </a>
        <button type="submit"
                class="asset-btn asset-btn-primary">
          บันทึก
        </button>
      </div>
    </form>
  </div>
@endsection

{{-- ===========================
     Tom Select + Styling ใช้กับ: #category_id, #department_id
=========================== --}}
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
  /* ====== Scope ทั้งหมดให้เฉพาะหน้า Create Asset ====== */

  /* ให้ input / select ปกติสูงเท่ากัน + font-size เท่ากัน (เหมือน maint-form) */
  .asset-form input[type="text"],
  .asset-form input[type="date"],
  .asset-form input[type="number"],
  .asset-form select:not([multiple]) {
      height: 44px;
      border-radius: 0.75rem;
      box-sizing: border-box;
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
      font-size: 0.875rem;   /* text-sm */
      line-height: 1.25rem;
  }

  /* --- ปุ่มทุกประเภท (Back / ยกเลิก / บันทึก) --- */
  .asset-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.25rem;
      padding: 0 1rem;
      height: 44px;              /* เท่ากับช่อง input */
      border-radius: 0.75rem;
      font-size: 0.875rem;       /* text-sm */
      line-height: 1.25rem;
      font-weight: 500;
      border: 1px solid rgb(148,163,184);  /* slate-400 */
      background-color: #ffffff;
      color: rgb(51,65,85);      /* slate-700 */
      transition: background-color 0.15s ease,
                  border-color 0.15s ease,
                  color 0.15s ease;
      text-decoration: none;
  }

  .asset-btn svg {
      flex-shrink: 0;
  }

  .asset-btn:hover {
      background-color: rgb(248,250,252);
  }

  .asset-btn-primary {
      border-color: rgb(5,150,105);
      background-color: rgb(5,150,105);
      color: white;
  }

  .asset-btn-primary:hover {
      background-color: rgb(4,120,87);
      border-color: rgb(4,120,87);
  }

  /* ============================================
     TomSelect Styling (เฉพาะภายใน asset-form)
     ============================================ */

  /* Wrapper */
  .asset-form .ts-wrapper.ts-basic {
      border: none !important;
      padding: 0 !important;
      box-shadow: none !important;
      background: transparent;
  }

  /* Control (กล่องหลัก) */
  .asset-form .ts-wrapper.ts-basic .ts-control {
      border-radius: 0.75rem;
      border: 1px solid rgb(226,232,240);  /* slate-200 */
      padding: 0 0.75rem;                  /* px-3 */
      box-shadow: none;
      min-height: 44px;                    /* เท่ากับ input */
      background-color: #fff;
      display: flex;
      align-items: center;
      font-size: 0.875rem;
      line-height: 1.25rem;
  }

  /* เผื่อไอคอนแว่นขยาย */
  .asset-form .ts-wrapper.ts-basic.ts-with-icon .ts-control {
      padding-left: 2.6rem;
  }

  /* Input ข้างใน control */
  .asset-form .ts-wrapper.ts-basic .ts-control input {
      font-size: 0.875rem;
      line-height: 1.25rem;
  }

  /* Focus */
  .asset-form .ts-wrapper.ts-basic .ts-control.focus {
      border-color: rgb(5,150,105);
      box-shadow: none;
  }

  /* Dropdown */
  .asset-form .ts-wrapper.ts-basic .ts-dropdown {
      border-radius: 0.5rem;
      border-color: rgb(226,232,240);
      box-shadow: 0 10px 15px -3px rgba(15,23,42,0.15);
      z-index: 50;
      font-size: 0.875rem;
      line-height: 1.25rem;
  }

  /* Error border */
  .asset-form .ts-wrapper.ts-basic.ts-error .ts-control {
      border-color: rgb(248,113,113) !important;
  }

  /* ============================================
     FIX: Search box ใน Dropdown ไม่ให้บวม
     ============================================ */

  .asset-form .ts-dropdown .ts-dropdown-input {
      padding: 0.25rem 0.75rem 0.5rem; /* บีบให้พอดี ไม่สูงเกิน */
  }

  .asset-form .ts-dropdown .ts-dropdown-input input {
      height: 32px !important;   /* เตี้ยกว่าฟิลด์หลักเล็กน้อย */
      padding-top: 0.25rem;
      padding-bottom: 0.25rem;
      font-size: 0.875rem;
      line-height: 1.25rem;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    ['category_id', 'department_id'].forEach(function (id) {
      if (document.getElementById(id)) {
        new TomSelect('#' + id, {
          create: false,
          allowEmptyOption: true,
          plugins: ['dropdown_input'],
          sortField: { field: 'text', direction: 'asc' },
          maxOptions: 500,
        });
      }
    });
  });
</script>
