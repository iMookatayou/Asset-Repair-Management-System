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

        <a href="{{ route('assets.index') }}"
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
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
      @csrf

      @include('assets._fields', [
        'asset'       => new \App\Models\Asset(),
        'categories'  => $categories ?? null,
        'departments' => $departments ?? null
      ])

      <div class="pt-2 flex justify-end gap-2">
        <a href="{{ route('assets.index') }}"
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
     Tom Select + Styling เหมือนหน้า Create Maintenance
     ใช้กับ select: #category_id, #department_id
=========================== --}}
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
  .ts-wrapper.ts-basic {
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    background: transparent;
  }

  .ts-wrapper.ts-basic .ts-control {
    border-radius: 0.75rem;
    border: 1px solid rgb(226,232,240);
    padding: 0.5rem 0.75rem;
    box-shadow: none;
    min-height: auto;
    background-color: #fff;
  }

  .ts-wrapper.ts-basic.ts-with-icon .ts-control {
    padding-left: 2.6rem;
  }

  .ts-wrapper.ts-basic .ts-control input {
    font-size: 0.875rem;
  }

  .ts-wrapper.ts-basic .ts-control.focus {
    border-color: rgb(5,150,105);
    box-shadow: none;
  }

  .ts-wrapper.ts-basic .ts-dropdown {
    border-radius: 0.5rem;
    border-color: rgb(226,232,240);
    box-shadow: 0 10px 15px -3px rgba(15,23,42,0.15);
  }

  .ts-wrapper.ts-basic.ts-error .ts-control {
    border-color: rgb(248,113,113) !important;
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
