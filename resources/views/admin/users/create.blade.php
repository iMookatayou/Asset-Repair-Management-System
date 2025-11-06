{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.app')
@section('title','สร้างผู้ใช้ใหม่')

@section('page-header')
  {{-- Header โทนอ่อน + ไอคอน + ปุ่ม Back (มาตรฐาน) --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ไอคอน + (inline SVG) --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create User
          </h1>
          <p class="mt-1 text-sm text-slate-600">ระบุข้อมูลผู้ใช้และกำหนดบทบาทให้ถูกต้อง</p>
        </div>

        <a href="{{ route('admin.users.index') }}"
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

    {{-- Error summary (มาตรฐาน) --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <p class="font-medium">มีข้อผิดพลาดในการบันทึกข้อมูล:</p>
        <ul class="mt-2 list-disc pl-5 text-sm">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Form wrapper (ไม่มี <form> ซ้อนใน partial) --}}
    <form method="POST"
          action="{{ route('admin.users.store') }}"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6"
          novalidate>
      @csrf

      {{-- ส่วนฟิลด์: ใช้ partial เดิมให้คงสไตล์ label/input/error --}}
      @include('admin.users._form', ['user' => null, 'roles' => $roles])

      {{-- Actions --}}
      <div class="mt-2 flex justify-end gap-2">
        <a href="{{ route('admin.users.index') }}"
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
