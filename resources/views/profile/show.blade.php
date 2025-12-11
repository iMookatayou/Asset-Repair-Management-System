@extends('layouts.app')
@section('title', 'Profile')

@section('page-header')
  <div class="border-b border-slate-200 bg-slate-50/80 backdrop-blur-sm">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-4">
      <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 hover:underline">Dashboard</a>
        <span class="text-slate-400">/</span>
        <span class="text-slate-700">Profile</span>
      </nav>

      <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-xl font-semibold text-slate-900 tracking-tight">
            My Profile
          </h1>
          <p class="mt-0.5 text-sm text-slate-500">
            ข้อมูลส่วนตัวและความปลอดภัยของบัญชี (Account & Security)
          </p>

          @php
            $deptName = optional($user->departmentRef)->display_name
              ?? optional($user->departmentRef)->name_th
              ?? optional($user->departmentRef)->name_en
              ?? optional($user->departmentRef)->name
              ?? '—';
          @endphp

          <div class="mt-3 flex flex-wrap items-center gap-2">

            {{-- Role (label) --}}
            <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-700">
              <x-app-icon name="briefcase" class="w-3.5 h-3.5 text-slate-600" />
              Role: {{ $user->role_label ?? $user->role }}
            </span>

            {{-- Department --}}
            <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-700">
              <x-app-icon name="users" class="w-3.5 h-3.5 text-slate-600" />
              Dept: {{ $deptName }}
            </span>

            {{-- Citizen ID --}}
            @if($user->citizen_id)
              <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-700">
                <x-app-icon name="id-card" class="w-3.5 h-3.5 text-slate-600" />
                CID: {{ $user->citizen_id }}
              </span>
            @endif

            {{-- Updated --}}
            <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-700">
              <x-app-icon name="bar-chart-3" class="w-3.5 h-3.5 text-slate-600" />
              Updated: {{ $user->updated_at?->format('Y-m-d H:i') ?? '—' }}
            </span>

            {{-- Email verify badge (โชว์ต่อเมื่อมีอีเมล) --}}
            @if($user->email)
              @if($user->email_verified_at)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs text-emerald-700 ring-1 ring-emerald-200">
                  ✔ Verified email
                </span>
              @else
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs text-amber-800 ring-1 ring-amber-200">
                  ⏳ Not verified
                </span>
              @endif
            @endif
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <a href="{{ route('dashboard') }}"
             class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
            <x-app-icon name="inbox" class="w-4 h-4 text-slate-600" />
            Back
          </a>

          @if (Route::has('password.edit'))
            <a href="{{ route('password.edit') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
              <x-app-icon name="shield" class="w-4 h-4 text-slate-600" />
              Change Password
            </a>
          @endif

          @if (Route::has('profile.edit'))
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
              <x-app-icon name="wrench" class="w-4 h-4 text-white" />
              Edit Profile
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  @if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
      {{ session('status') }}
    </div>
  @endif

  {{-- Summary card --}}
  <section class="rounded-xl border border-slate-200 bg-white p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-full bg-emerald-100 text-xl text-emerald-700">
          <x-app-icon name="user" class="w-6 h-6" />
        </div>
        <div class="min-w-0">
          <div class="truncate text-lg font-medium text-slate-900">{{ $user->name }}</div>

          {{-- แสดง citizen_id เป็นหลัก --}}
          @if($user->citizen_id)
            <div class="truncate text-sm text-slate-600">เลขบัตร: {{ $user->citizen_id }}</div>
          @endif

          {{-- อีเมล (ถ้ามี) --}}
          @if($user->email)
            <div class="truncate text-xs text-slate-500">{{ $user->email }}</div>
          @endif
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-slate-50 px-2.5 py-1 text-xs text-slate-700">
          <x-app-icon name="briefcase" class="w-3.5 h-3.5" />
          {{ $user->role_label ?? $user->role }}
        </span>
      </div>
    </div>

    <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div>
        <dt class="text-sm text-slate-500">แผนก</dt>
        <dd class="mt-1 text-slate-900">
          @if($deptName && $deptName !== '—')
            <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-sm">
              <x-app-icon name="users" class="w-4 h-4 text-slate-600" />
              {{ $deptName }}
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
  </section>

  <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Account Info --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5">
      <div>
        <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
          <x-app-icon name="clipboard-list" class="w-5 h-5 text-slate-700" />
          Account Information
        </h2>
        <p class="mt-1 text-sm text-slate-500">รายละเอียดระบุตัวตนในระบบ</p>
      </div>

      <div class="mt-4 divide-y divide-slate-100">

        <div class="py-3">
          <div class="text-sm text-slate-500">เลขบัตรประชาชน</div>
          <div class="mt-1 text-slate-900">
            {{ $user->citizen_id ?? '—' }}
          </div>
        </div>

        <div class="py-3">
          <div class="text-sm text-slate-500">ชื่อ - สกุล</div>
          <div class="mt-1 text-slate-900">{{ $user->name }}</div>
        </div>

        <div class="py-3">
          <div class="text-sm text-slate-500">อีเมล</div>
          <div class="mt-1 break-all text-slate-900">
            {{ $user->email ?? '—' }}
          </div>
        </div>

        <div class="py-3">
          <div class="text-sm text-slate-500">แผนก</div>
          <div class="mt-1 text-slate-900">{{ $deptName }}</div>
        </div>

        <div class="py-3">
          <div class="text-sm text-slate-500">สิทธิ์การใช้งาน</div>
          <div class="mt-1">
            <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-sm">
              <x-app-icon name="briefcase" class="w-4 h-4 text-slate-600" />
              {{ $user->role_label ?? $user->role }}
            </span>
          </div>
        </div>
      </div>
    </div>

    {{-- Security --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
            <x-app-icon name="shield" class="w-5 h-5 text-slate-700" />
            Security
          </h2>
          <p class="mt-1 text-sm text-slate-500">ตั้งค่าที่ช่วยป้องกันบัญชีของคุณ</p>
        </div>
      </div>

      <ul class="mt-4 space-y-3">
        <li class="rounded-lg border border-slate-200 p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2">
              <x-app-icon name="hammer" class="mt-0.5 w-4 h-4 text-slate-600" />
              <div>
                <div class="font-medium text-slate-900">Password</div>
                <p class="text-sm text-slate-500">แนะนำให้เปลี่ยนรหัสผ่านเป็นระยะ</p>
              </div>
            </div>
            @if (Route::has('password.edit'))
              <a href="{{ route('password.edit') }}" class="text-sm text-emerald-700 hover:underline">เปลี่ยน</a>
            @endif
          </div>
        </li>

        {{-- Email verification เฉพาะกรณีมีอีเมล --}}
        @if($user->email)
          <li class="rounded-lg border border-slate-200 p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-2">
                <x-app-icon name="mail" class="mt-0.5 w-4 h-4 text-slate-600" />
                <div>
                  <div class="font-medium text-slate-900">Email Verification</div>
                  <p class="text-sm text-slate-500">
                    @if($user->email_verified_at)
                      Verified on {{ $user->email_verified_at?->format('Y-m-d H:i') }}
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
        @endif
      </ul>
    </div>
  </section>
</div>
@endsection
