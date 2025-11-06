@extends('layouts.app')
@section('title', 'Edit Profile')

@section('page-header')
  {{-- ===== Header โทนอ่อนพร้อมไอคอน ===== --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ไอคอนโปรไฟล์ --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 
              1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 
              1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
              stroke="currentColor" stroke-width="1.5"
              stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            แก้ไขโปรไฟล์
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            ปรับข้อมูลส่วนตัวของคุณ เช่น ชื่อ อีเมล และแผนก
          </p>
        </div>

        {{-- ปุ่มย้อนกลับ --}}
        <a href="{{ route('profile.show') }}"
           class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="mx-auto max-w-3xl py-6 space-y-5">

    @if (session('status'))
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
        {{ session('status') }}
      </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
          <label for="name" class="block text-sm font-medium text-slate-700">ชื่อ-นามสกุล</label>
          <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                 class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 
                        focus:border-emerald-600 focus:ring-emerald-600 
                        @error('name') border-rose-400 ring-rose-200 @enderror">
          @error('name')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-slate-700">อีเมล</label>
          <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                 class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 
                        focus:border-emerald-600 focus:ring-emerald-600 
                        @error('email') border-rose-400 ring-rose-200 @enderror">
          @error('email')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
          @enderror
          <p class="mt-1 text-xs text-slate-500">เปลี่ยนอีเมลจะทำให้สถานะยืนยันอีเมลถูกรีเซ็ต</p>
        </div>

        <div>
          <label for="department" class="block text-sm font-medium text-slate-700">แผนก</label>
          <select id="department" name="department"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 
                         focus:border-emerald-600 focus:ring-emerald-600 
                         @error('department') border-rose-400 ring-rose-200 @enderror">
            <option value="">— เลือกแผนก —</option>
            @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
              <option value="{{ $dept->code }}" @selected(old('department', $user->department) == $dept->code)>
                {{ $dept->name }}
              </option>
            @endforeach
          </select>
          @error('department')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="pt-2">
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
            บันทึกการเปลี่ยนแปลง
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
