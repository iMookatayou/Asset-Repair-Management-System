@extends('layouts.app')
@section('title','Edit Maintenance')

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
        </div>

        <div class="flex items-center gap-2">
          <a href="{{ route('maintenance.requests.show', $mr) }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back
          </a>
          <a href="{{ route('maintenance.requests.index') }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            List
          </a>
        </div>
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
          @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ route('maintenance.requests.update', $mr) }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
          novalidate
          aria-label="แบบฟอร์มแก้ไขคำขอซ่อม">
      @csrf
      @method('PUT')

      {{-- ✅ ฟอร์มหลักที่ใช้ร่วมกับหน้า Create --}}
      @include('maintenance.requests._form', [
        'req'         => $mr,
        'assets'      => $assets ?? [],
        'depts'       => $depts ?? [],
        'attachments' => $attachments ?? [],
      ])

      {{-- 🟡 เฉพาะหน้า Edit: ผลการซ่อม + ค่าใช้จ่าย --}}
      <section class="pt-4 border-t border-slate-200 mt-6">
        <h2 class="text-base font-semibold text-slate-900">ผลการซ่อมและค่าใช้จ่าย</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
          @php $field='resolution_note'; @endphp
          <div class="md:col-span-2">
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
              ผลการซ่อม <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
            </label>
            <textarea id="{{ $field }}" name="{{ $field }}" rows="3"
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                             focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">{{ old($field, $mr->resolution_note) }}</textarea>
            @error($field)
              <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          @php $field='cost'; @endphp
          <div>
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
              ค่าใช้จ่าย (บาท) <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
            </label>
            <input id="{{ $field }}" name="{{ $field }}" type="number" step="0.01" min="0"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                          focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                   value="{{ old($field, $mr->cost) }}">
            @error($field)
              <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </section>

      <div class="mt-6 flex justify-end gap-2">
        <a href="{{ route('maintenance.requests.show', $mr) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          ยกเลิก
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          บันทึกการแก้ไข
        </button>
      </div>
    </form>
  </div>
@endsection
