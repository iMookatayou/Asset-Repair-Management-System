@extends('layouts.app')
@section('title','{{ page_title or "Form Template" }}')

@section('page-header')
  {{-- ===== Header โทนอ่อนพร้อมไอคอน ===== --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ตัวอย่าง: เปลี่ยน path ด้านล่างให้เป็นไอคอนของหน้า --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ page_title ?? 'Form Title' }}
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            {{ subtitle ?? 'รายละเอียดสั้นๆ ของหน้า เช่น อธิบายการทำงานหรือคำแนะนำ' }}
          </p>
        </div>

        {{-- ปุ่ม Back --}}
        <a href="{{ back_route ?? '#' }}"
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

    {{-- ===== Global error summary ===== --}}
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

    {{-- ===== Form wrapper ===== --}}
    <form method="POST" action="{{ form_action ?? '#' }}"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" novalidate>
      @csrf
      @isset($method)
        @if (strtoupper($method) === 'PUT')
          @method('PUT')
        @endif
      @endisset

      {{-- =========================================================
        SECTION 1 : ตัวอย่างฟิลด์
        ---------------------------------------------------------
        - ใส่ฟิลด์จริงของหน้าไว้ในแต่ละ section
        - ใช้โครงนี้ซ้ำได้ในทุกหน้า
      ========================================================= --}}
      <div class="space-y-6">
        {{-- Section: ข้อมูลหลัก --}}
        <div>
          <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
          <p class="text-sm text-slate-500">คำอธิบายสั้น ๆ ของ section นี้</p>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

            {{-- ตัวอย่างฟิลด์ --}}
            <div>
              <label for="example_field" class="block text-sm font-medium text-slate-700">
                ชื่อฟิลด์ <span class="text-rose-600">*</span>
              </label>
              <input id="example_field" name="example_field" type="text" required autocomplete="off"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                            focus:border-emerald-600 focus:ring-emerald-600
                            @error('example_field') border-rose-400 ring-rose-200 @enderror"
                     value="{{ old('example_field', $model->example_field ?? '') }}"
                     aria-invalid="@error('example_field') true @else false @enderror"
                     @error('example_field') aria-describedby="example_field_error" @enderror>
              @error('example_field')
                <p id="example_field_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            {{-- อีกฟิลด์ --}}
            <div>
              <label for="example_select" class="block text-sm font-medium text-slate-700">ตัวเลือก</label>
              <select id="example_select" name="example_select"
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                             focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">— เลือก —</option>
                <option value="1" @selected(old('example_select') == 1)>Option A</option>
                <option value="2" @selected(old('example_select') == 2)>Option B</option>
              </select>
            </div>

          </div>
        </div>

        {{-- Section: ข้อมูลเพิ่มเติม --}}
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">ข้อมูลเพิ่มเติม</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="date_field" class="block text-sm font-medium text-slate-700">วันที่</label>
              <input id="date_field" name="date_field" type="date"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                            focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('date_field', optional($model->date_field ?? null)?->format('Y-m-d')) }}">
            </div>
          </div>
        </div>
      </div>

      {{-- ===== Actions ===== --}}
      <div class="mt-6 flex justify-end gap-2">
        <a href="{{ back_route ?? '#' }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          Cancel
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          {{ submit_label ?? 'Save' }}
        </button>
      </div>
    </form>
  </div>
@endsection

{{-- =========================================================
  Example usage:
  -----------------------------------------------------------
  @extends('layouts.app')
  @section('title','Create Asset')

  @section('page-header')
    (ใช้ header ตามเทมเพลตนี้)
  @endsection

  @section('content')
    (ใช้ฟอร์มตามเทมเพลตนี้ ปรับ fields ให้ตรง model)
  @endsection
========================================================= --}}
