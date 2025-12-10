{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.app')
@section('title','แก้ไขผู้ใช้ #'.$user->id)

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">

        {{-- Left: Title + user summary --}}
        <div class="flex items-start gap-3">

          {{-- Avatar --}}
          <div class="mt-1 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-white text-sm font-semibold shadow-sm">
            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
          </div>

          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none">
                  <path d="M3 17.25V21h3.75L17.81 9.94a1.5 1.5 0 0 0 0-2.12l-2.63-2.63a1.5 1.5 0 0 0-2.12 0L3 17.25Z"
                        stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  <path d="M14 6l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Edit User
              </h1>

              <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                #{{ $user->id }}
              </span>
            </div>

            <p class="mt-1 text-sm text-slate-600">
              แก้ไขข้อมูลบัญชีของ
              <span class="font-semibold text-slate-800">{{ $user->name }}</span>
            </p>

            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">

              {{-- citizen_id --}}
              @if($user->citizen_id)
                <span class="inline-flex items-center gap-1">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M12 8v4m0 3v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                  </svg>
                  เลขบัตร: {{ $user->citizen_id }}
                </span>
              @endif

              {{-- email (optional) --}}
              @if($user->email)
                <span class="inline-flex items-center gap-1">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                    <path d="M4 4h16v16H4V4Zm0 2.5 8 5 8-5" stroke="currentColor" stroke-width="1.6"
                          stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  {{ $user->email }}
                </span>
              @endif

              {{-- updated_at --}}
              <span class="inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="7" stroke="currentColor" stroke-width="1.6"/>
                  <path d="M12 8v3.2l2 1.1" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                แก้ไขล่าสุด {{ $user->updated_at?->format('Y-m-d H:i') ?? '-' }}
              </span>
            </div>
          </div>
        </div>

        {{-- Back button --}}
        <a href="{{ route('admin.users.index') }}"
           class="maint-btn maint-btn-outline">
          <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back
        </a>

      </div>
    </div>
  </div>
@endsection



@section('content')
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

    {{-- Error display --}}
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


    {{-- Edit Form --}}
    <form method="POST"
          action="{{ route('admin.users.update', $user) }}"
          class="maint-form rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6"
          novalidate>
      @csrf
      @method('PUT')

      {{-- Section title --}}
      <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-800">ข้อมูลบัญชีผู้ใช้</h2>
          <p class="text-xs text-slate-500">เลขบัตรประชาชน ชื่อ หน่วยงาน และบทบาทของผู้ใช้</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-0.5 text-[11px] font-medium text-slate-600">
          User ID: {{ $user->id }}
        </span>
      </div>

      {{-- Shared form fields --}}
      @include('admin.users._form', [
          'user'        => $user,
          'roles'       => $roles,
          'roleLabels'  => $roleLabels ?? \App\Models\User::roleLabels(),
          'departments' => $departments,
      ])

      <div class="pt-2 flex flex-wrap items-center gap-2 border-t border-slate-100 mt-2">
        <a href="{{ route('admin.users.index') }}"
           class="maint-btn maint-btn-outline">
          ยกเลิก
        </a>

        <button type="submit"
                class="maint-btn maint-btn-primary inline-flex items-center gap-1">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
            <path d="M5 12.75 9 16.5 19 7.5" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          อัปเดตข้อมูลผู้ใช้
        </button>
      </div>
    </form>

    {{-- Danger zone: Delete --}}
    @if ($user->id !== auth()->id())
      <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">

          <div class="flex items-start gap-2">
            <div class="mt-0.5 flex h-7 w-7 items-center justify-center rounded-full bg-rose-100 text-rose-700">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M12 16.5v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
              </svg>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-rose-800">ลบผู้ใช้</h3>
              <p class="mt-0.5 text-xs text-rose-700">
                การลบผู้ใช้จะไม่สามารถกู้คืนได้
              </p>
            </div>
          </div>

          <form method="POST"
                action="{{ route('admin.users.destroy', $user) }}"
                onsubmit="return confirm('ยืนยันลบผู้ใช้คนนี้หรือไม่?');"
                class="shrink-0">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 shadow-sm">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M3 6h18" stroke="currentColor" stroke-width="2"/>
                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2"/>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="2"/>
                <path d="M10 11v6" stroke="currentColor" stroke-width="2"/>
                <path d="M14 11v6" stroke="currentColor" stroke-width="2"/>
              </svg>
              ลบผู้ใช้
            </button>
          </form>

        </div>
      </div>
    @endif

  </div>
@endsection

{{-- ===========================
     Tom Select + Styling
     (ใช้ชุดเดียวกับหน้า Create User)
=========================== --}}
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
  /* ให้ input / select ปกติสูงเท่ากัน + font-size เท่ากัน */
  .maint-form input[type="text"],
  .maint-form input[type="email"],
  .maint-form input[type="password"],
  .maint-form input[type="date"],
  .maint-form input[type="number"],
  .maint-form select:not([multiple]) {
    height: 44px;
    border-radius: 0.75rem;
    box-sizing: border-box;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  /* ปุ่ม */
  .maint-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 1rem;
    height: 44px;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: 500;
    border: 1px solid rgb(148,163,184);
    background-color: #ffffff;
    color: rgb(51,65,85);
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    text-decoration: none;
    gap: 0.25rem;
  }
  .maint-btn svg { flex-shrink: 0; }
  .maint-btn:hover { background-color: rgb(248,250,252); }
  .maint-btn-primary {
    border-color: rgb(5,150,105);
    background-color: rgb(5,150,105);
    color: #ffffff;
  }
  .maint-btn-primary:hover {
    background-color: rgb(4,120,87);
    border-color: rgb(4,120,87);
  }

  /* TomSelect */
  .maint-form .ts-wrapper.ts-basic {
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    background: transparent;
  }

  .maint-form .ts-wrapper.ts-basic .ts-control {
    border-radius: 0.75rem;
    border: 1px solid rgb(226,232,240);
    padding: 0 0.75rem;
    box-shadow: none;
    min-height: 44px;
    background-color: #fff;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .maint-form .ts-wrapper.ts-basic.ts-with-icon .ts-control {
    padding-left: 2.6rem; /* เว้นที่ให้ไอคอนด้านซ้าย */
  }

  .maint-form .ts-wrapper.ts-basic .ts-control input {
    font-size: 0.875rem;
    line-height: 1.25rem;
    min-width: 0;
    flex: 1 1 auto;
  }

  .maint-form .ts-wrapper.ts-basic .ts-control.focus {
    border-color: rgb(5,150,105);
    box-shadow: none;
  }

  .maint-form .ts-wrapper.ts-basic .ts-dropdown {
    border-radius: 0.5rem;
    border-color: rgb(226,232,240);
    box-shadow: 0 10px 15px -3px rgba(15,23,42,0.15);
    z-index: 50;
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .maint-form .ts-wrapper.ts-basic.ts-error .ts-control {
    border-color: rgb(248,113,113) !important;
  }

  .maint-form .ts-wrapper.ts-with-icon {
    position: relative;
  }
  .maint-form .ts-wrapper.ts-with-icon .ts-select-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    color: rgb(148,163,184);
  }
  .maint-form .ts-wrapper.ts-with-icon .ts-select-icon svg {
    width: 16px;
    height: 16px;
  }

  .maint-form select.ts-hidden-accessible {
    display: none !important;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    function initTomSelectWithIcon(selector, placeholderText) {
      const el = document.querySelector(selector);
      if (!el) return;

      const ts = new TomSelect(selector, {
        create: false,
        allowEmptyOption: true,
        maxOptions: 500,
        sortField: { field: 'text', direction: 'asc' },
        placeholder: placeholderText,
        searchField: ['text'],
      });

      const wrapper = ts.wrapper;
      if (!wrapper) return;

      wrapper.classList.add('ts-with-icon');

      const icon = document.createElement('span');
      icon.className = 'ts-select-icon';
      icon.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="11" cy="11" r="5" stroke="currentColor" stroke-width="2"></circle>
          <path d="M15 15l4 4" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      `;
      wrapper.insertBefore(icon, wrapper.firstChild);
    }

    // ใช้ id ตาม _form.blade.php
    initTomSelectWithIcon('#department_id', '— เลือกหน่วยงาน —');
    initTomSelectWithIcon('#role', '— เลือกบทบาท —');
  });
</script>
