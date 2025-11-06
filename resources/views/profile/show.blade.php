{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.app')
@section('title','Profile')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  @if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
      {{ session('status') }}
    </div>
  @endif

  {{-- ===== Profile Summary ===== --}}
  <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-full bg-emerald-100 text-xl text-emerald-700">
          <x-app-icon name="user" class="w-6 h-6" />
        </div>
        <div>
          <div class="text-lg font-medium text-slate-900">{{ $user->name }}</div>
          <div class="text-sm text-slate-600">{{ $user->email }}</div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-slate-50 px-2.5 py-1 text-xs capitalize text-slate-700">
          <x-app-icon name="briefcase" class="w-3.5 h-3.5" />
          {{ $user->role }}
        </span>

        @if($user->email_verified_at)
          <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs text-emerald-700 ring-1 ring-emerald-200">
            ✔ ยืนยันอีเมลแล้ว
          </span>
        @else
          <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs text-amber-800 ring-1 ring-amber-200">
            ⏳ ยังไม่ยืนยันอีเมล
          </span>
        @endif
      </div>
    </div>

    <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div>
        <dt class="text-sm text-slate-500">แผนก</dt>
        <dd class="mt-1 text-slate-900">
          @if($user->departmentRef?->name)
            <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-sm">
              <x-app-icon name="users" class="w-4 h-4 text-slate-600" />
              {{ $user->departmentRef->name }}
            </span>
          @else
            —
          @endif
        </dd>
      </div>
      <div>
        <dt class="text-sm text-slate-500">อัปเดตล่าสุด</dt>
        <dd class="mt-1 text-slate-900">{{ $user->updated_at?->format('Y-m-d H:i') }}</dd>
      </div>
      <div>
        <dt class="text-sm text-slate-500">สร้างเมื่อ</dt>
        <dd class="mt-1 text-slate-900">{{ $user->created_at?->format('Y-m-d H:i') }}</dd>
      </div>
    </dl>

    {{-- Quick switches / secondary actions --}}
    <div class="mt-6 flex flex-wrap gap-2">
      @can('manage-users')
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
          <x-app-icon name="wrench" class="w-4 h-4 text-white" />
          แก้ไขโปรไฟล์
        </a>
      @endcan

      @if (Route::has('password.edit'))
        <a href="{{ route('password.edit') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          <x-app-icon name="shield" class="w-4 h-4 text-slate-600" />
          เปลี่ยนรหัสผ่าน
        </a>
      @endif

      @if(!$user->email_verified_at && Route::has('verification.send'))
        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button type="submit"
                  class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
            <x-app-icon name="mail" class="w-4 h-4 text-slate-600" />
            ส่งลิงก์ยืนยันอีเมลอีกครั้ง
          </button>
        </form>
      @endif
    </div>
  </section>

  {{-- ===== 2-Column: Account / Security ===== --}}
  <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Account details --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
            <x-app-icon name="clipboard-list" class="w-5 h-5 text-slate-700" />
            ข้อมูลบัญชี
          </h2>
          <p class="mt-1 text-sm text-slate-500">รายละเอียดระบุตัวตนในระบบ</p>
        </div>
      </div>

      <div class="mt-4 divide-y divide-slate-100">
        <div class="flex items-start justify-between gap-4 py-3">
          <div>
            <div class="text-sm text-slate-500">ชื่อ - สกุล</div>
            <div class="mt-1 text-slate-900">{{ $user->name }}</div>
          </div>
        </div>

        <div class="flex items-start justify-between gap-4 py-3">
          <div class="min-w-0">
            <div class="text-sm text-slate-500">อีเมล</div>
            <div class="mt-1 break-all text-slate-900">{{ $user->email }}</div>
          </div>
        </div>

        <div class="flex items-start justify-between gap-4 py-3">
          <div>
            <div class="text-sm text-slate-500">แผนก</div>
            <div class="mt-1 text-slate-900">{{ $user->departmentRef?->name ?? '—' }}</div>
          </div>
        </div>

        <div class="flex items-start justify-between gap-4 py-3">
          <div>
            <div class="text-sm text-slate-500">สิทธิ์การใช้งาน (Role)</div>
            <div class="mt-1">
              <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-sm capitalize">
                <x-app-icon name="briefcase" class="w-4 h-4 text-slate-600" />
                {{ $user->role }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Security --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
            <x-app-icon name="shield" class="w-5 h-5 text-slate-700" />
            ความปลอดภัย
          </h2>
          <p class="mt-1 text-sm text-slate-500">ตั้งค่าที่ช่วยป้องกันบัญชีของคุณ</p>
        </div>
        @if (Route::has('password.edit'))
          <a href="{{ route('password.edit') }}" class="text-sm text-emerald-700 hover:underline">จัดการ</a>
        @endif
      </div>

      <ul class="mt-4 space-y-3">
        <li class="rounded-lg border border-slate-200 p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2">
              <x-app-icon name="hammer" class="mt-0.5 w-4 h-4 text-slate-600" />
              <div>
                <div class="font-medium text-slate-900">รหัสผ่าน</div>
                <p class="text-sm text-slate-500">เปลี่ยนรหัสผ่านเป็นระยะเพื่อความปลอดภัย</p>
              </div>
            </div>
            @if (Route::has('password.edit'))
              <a href="{{ route('password.edit') }}" class="text-sm text-emerald-700 hover:underline">เปลี่ยน</a>
            @endif
          </div>
        </li>

        <li class="rounded-lg border border-slate-200 p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2">
              <x-app-icon name="mail" class="mt-0.5 w-4 h-4 text-slate-600" />
              <div>
                <div class="font-medium text-slate-900">อีเมลยืนยัน</div>
                <p class="text-sm text-slate-500">
                  @if($user->email_verified_at)
                    ยืนยันแล้วเมื่อ {{ $user->email_verified_at?->format('Y-m-d H:i') }}
                  @else
                    ยังไม่ได้ยืนยันอีเมล
                  @endif
                </p>
              </div>
            </div>
            @if(!$user->email_verified_at && Route::has('verification.send'))
              <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="text-sm text-emerald-700 hover:underline">ส่งลิงก์ใหม่</button>
              </form>
            @endif
          </div>
        </li>

        {{-- เผื่อหน้าจัดการ Sessions/Devices ในอนาคต --}}
        {{-- 
        <li class="rounded-lg border border-slate-200 p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2">
              <x-app-icon name="bar-chart-3" class="mt-0.5 w-4 h-4 text-slate-600" />
              <div>
                <div class="font-medium text-slate-900">อุปกรณ์ที่เคยเข้าสู่ระบบ</div>
                <p class="text-sm text-slate-500">ตรวจสอบและออกจากระบบบนอุปกรณ์อื่น</p>
              </div>
            </div>
            <a href="{{ route('sessions.index') }}" class="text-sm text-emerald-700 hover:underline">จัดการ</a>
          </div>
        </li>
        --}}
      </ul>
    </div>
  </section>
</div>
@endsection
