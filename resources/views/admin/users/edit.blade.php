{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.app')
@section('title','แก้ไขผู้ใช้ #'.$user->id)

@section('page-header')
  {{-- Header โทนอ่อน + ไอคอนดินสอ + ปุ่ม Back --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ไอคอนดินสอ (inline SVG) --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M3 17.25V21h3.75L17.81 9.94a1.5 1.5 0 0 0 0-2.12l-2.63-2.63a1.5 1.5 0 0 0-2.12 0L3 17.25Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M14 6l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Edit User
          </h1>
          <p class="mt-1 text-sm text-slate-600">ปรับข้อมูลผู้ใช้ บทบาท และสถานะการใช้งาน</p>
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

    {{-- Flash message --}}
    @if (session('status'))
      <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700">
        {{ session('status') }}
      </div>
    @endif

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

    {{-- Form wrapper (ไม่มี <form> ซ้อน) --}}
    <form method="POST"
          action="{{ route('admin.users.update', $user) }}"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6"
          novalidate>
      @csrf
      @method('PUT')

      {{-- ส่วนฟิลด์: ใช้ partial เดิมให้คงสไตล์ --}}
      @include('admin.users._form', ['user' => $user, 'roles' => $roles])

      {{-- Actions --}}
      <div class="mt-2 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.users.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          ยกเลิก
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          อัปเดต
        </button>
      </div>
    </form>

    {{-- Danger zone: ลบท้ายหน้า (ฟอร์มแยก ไม่ซ้อนฟอร์มหลัก) --}}
    @if ($user->id !== auth()->id())
      <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 class="text-sm font-semibold text-rose-800">ลบผู้ใช้</h3>
            <p class="text-sm text-rose-700">การลบเป็นการกระทำถาวรและไม่สามารถกู้คืนได้</p>
          </div>
          <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                onsubmit="return confirm('ยืนยันลบผู้ใช้คนนี้หรือไม่?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-white hover:bg-rose-700">
              ลบผู้ใช้
            </button>
          </form>
        </div>
      </div>
    @endif

  </div>
@endsection
