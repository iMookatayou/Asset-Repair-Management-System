@extends('layouts.app')
@section('title','Edit Asset')

@section('page-header')
  {{-- Header โทนอ่อน + แยกส่วนชัดเจน --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ไอคอนดินสอแบบ inline (ไม่ต้องพึ่ง lib เพิ่ม) --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M4 20h4l10-10-4-4L4 16v4zM13 7l4 4M4 20l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Edit Asset
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            แก้ไขข้อมูลครุภัณฑ์:
            <span class="font-medium text-slate-800">{{ $asset->asset_code }}</span>
            <span class="text-slate-400">—</span>
            <span class="italic text-slate-500">{{ $asset->name }}</span>
          </p>
        </div>

        <a href="{{ route('assets.show', $asset) }}"
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
    {{-- Error summary --}}
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

    <form method="POST" action="{{ route('assets.update', $asset) }}"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
      @csrf
      @method('PUT')

      {{-- ฟิลด์ที่ใช้ร่วมกัน --}}
      @include('assets._fields', [
        'asset' => $asset,
        'categories' => $categories ?? null,
        'departments' => $departments ?? null
      ])

      {{-- Actions --}}
      <div class="pt-2 flex justify-end gap-2">
        <a href="{{ route('assets.show', $asset) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          Cancel
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          Update
        </button>
      </div>
    </form>
  </div>
@endsection
