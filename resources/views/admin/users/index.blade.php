{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')
@section('title','Manage Users')

@php
  /** @var \Illuminate\Pagination\LengthAwarePaginator $list */

  use App\Models\User as UserModel;

  // roles / labels / filters
  $roles      = $roles      ?? UserModel::availableRoles();
  $roleLabels = $roleLabels ?? UserModel::roleLabels();
  $filters    = $filters    ?? ['s'=>'','role'=>'','department'=>''];

  // รายชื่อหน่วยงาน (controller ควรส่งมา ถ้าไม่มีก็เป็น collection ว่าง)
  /** @var \Illuminate\Support\Collection|\App\Models\Department[] $departments */
  $departments = $departments ?? collect();

  $BTN = 'h-10 text-xs md:text-sm inline-flex items-center gap-2 rounded-lg px-3 md:px-3.5 font-medium leading-5
          focus:outline-none focus:ring-2 whitespace-nowrap';

  // flags สำหรับ empty state
  $hasS      = ($filters['s'] ?? '') !== '';
  $hasRole   = ($filters['role'] ?? '') !== '';
  $hasDept   = ($filters['department'] ?? '') !== '';
  $hasFilter = $hasS || $hasRole || $hasDept;
@endphp

@section('content')
  {{-- ระยะใต้ navbar ให้เท่าหน้าอื่น ๆ --}}
  <div class="pt-6 md:pt-8 lg:pt-10"></div>

  {{-- ให้โครง wrapper / gap เท่ากับ Maintenance / My Jobs --}}
  <div class="w-full px-4 md:px-6 lg:px-8 flex flex-col gap-4 user-filter">

    {{-- ===== Sticky Header + Filter Card ===== --}}
    <div class="sticky top-[6rem] z-20 bg-slate-50/90 backdrop-blur">
      <div class="rounded-lg border border-zinc-300 bg-white shadow-sm">
        <div class="px-5 py-4">
          <div class="flex flex-wrap items-start justify-between gap-4">
            {{-- Left: Icon + Title --}}
            <div class="flex items-start gap-3">
              <div class="grid h-9 w-9 place-items-center rounded-md bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                  <rect x="4" y="3" width="16" height="18" rx="2"/>
                  <path d="M8 7h8M8 11h8M8 15h5"/>
                </svg>
              </div>
              <div>
                <h1 class="text-[17px] font-semibold text-zinc-900">Manage Users</h1>
                <p class="text-[13px] text-zinc-600">
                  เรียกดู กรอง และจัดการผู้ใช้ในระบบ
                </p>
              </div>
            </div>

            {{-- Right: ปุ่มสร้างผู้ใช้ --}}
            <div class="flex shrink-0 items-center">
              <a href="{{ route('admin.users.create') }}"
                 onclick="showLoader()"
                 class="{{ $BTN }} inline-flex items-center gap-1.5 rounded-md border border-emerald-700 bg-emerald-700 px-3.5 py-2 text-[13px] font-medium text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>
                สร้างผู้ใช้ใหม่
              </a>
            </div>
          </div>

          <div class="mt-4 h-px bg-zinc-200"></div>

          {{-- Filter Form --}}
          <form method="GET" class="mt-4 flex flex-col gap-3" onsubmit="showLoader()">

            {{-- แถวเดียว: ค้นหา + บทบาท + หน่วยงาน + ปุ่มค้นหา/ล้าง --}}
            <div class="flex flex-col lg:flex-row lg:flex-nowrap items-start lg:items-end gap-3 w-full">

              {{-- Search (ย่อให้สั้นลงหน่อย) --}}
              <div class="w-full lg:w-[32%] min-w-[220px]">
                <label class="mb-1 block text-[12px] text-zinc-600">คำค้นหา</label>
                <div class="relative">
                  <input name="s" value="{{ $filters['s'] }}"
                         placeholder="เช่น ชื่อผู้ใช้, อีเมล, หน่วยงาน"
                         class="w-full rounded-md border border-zinc-300 pl-10 pr-3 py-2 text-[13px]
                                focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                  </span>
                </div>
              </div>

              {{-- Role --}}
              <div class="w-full sm:flex-1 lg:w-[20%] min-w-[160px]">
                <label class="mb-1 block text-[12px] text-zinc-600">บทบาท</label>
                <select id="filter_role" name="role"
                        class="ts-basic w-full h-10 rounded-md border border-zinc-300 bg-white text-sm text-zinc-800"
                        data-placeholder="บทบาททั้งหมด">
                  <option value="">บทบาททั้งหมด</option>
                  @foreach ($roles as $r)
                    <option value="{{ $r }}" @selected($filters['role'] === $r)>
                      {{ $roleLabels[$r] }}
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- Department (ให้กว้างขึ้นหน่อย) --}}
              <div class="w-full sm:flex-1 lg:w-[28%] min-w-[180px]">
                <label class="mb-1 block text-[12px] text-zinc-600">หน่วยงาน</label>
                <select id="filter_department" name="department"
                        class="ts-basic w-full h-10 rounded-md border border-zinc-300 bg-white text-sm text-zinc-800"
                        data-placeholder="ทุกหน่วยงาน">
                  <option value="">ทุกหน่วยงาน</option>
                  @foreach ($departments as $dept)
                    <option value="{{ $dept->code ?? $dept->id }}" @selected($filters['department'] == ($dept->code ?? $dept->id))>
                      {{ $dept->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- ปุ่มค้นหา / ล้างค่า --}}
              <div class="flex justify-end gap-2 lg:ml-auto shrink-0">
                {{-- ล้างค่า --}}
                <a href="{{ route('admin.users.index') }}"
                   onclick="showLoader()"
                   aria-label="ล้างค่า"
                   title="ล้างค่า"
                   class="inline-flex h-11 w-11 items-center justify-center rounded-full
                          border border-emerald-300 bg-emerald-50
                          text-emerald-700 shadow-sm
                          hover:bg-emerald-100 hover:border-emerald-400 hover:text-emerald-800
                          focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1
                          transition-all duration-150">
                  <svg xmlns="http://www.w3.org/2000/svg"
                       class="h-5 w-5"
                       viewBox="0 0 24 24"
                       fill="none"
                       stroke="currentColor"
                       stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span class="sr-only">ล้างค่า</span>
                </a>

                {{-- ค้นหา --}}
                <button type="submit"
                        aria-label="ค้นหา"
                        title="ค้นหา"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full
                               border border-emerald-700 bg-emerald-700
                               text-white shadow-md
                               hover:bg-emerald-800 hover:border-emerald-800 hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1
                               transition-all duration-150">
                  <svg xmlns="http://www.w3.org/2000/svg"
                       class="h-5 w-5"
                       viewBox="0 0 24 24"
                       fill="none"
                       stroke="currentColor"
                       stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  <span class="sr-only">ค้นหา</span>
                </button>
              </div>
            </div>

          </form>

        </div>
      </div>
    </div>

    {{-- ===== Table ===== --}}
    <div class="mt-6 md:mt-8 lg:mt-10 overflow-x-auto rounded-lg border border-zinc-300 bg-white">
      <table class="min-w-full divide-y divide-zinc-200 text-[13px]">
        <thead class="bg-zinc-50 text-zinc-700">
          <tr>
            <th class="px-3 py-2 text-center font-semibold whitespace-nowrap">ชื่อ</th>
            <th class="px-3 py-2 text-center font-semibold whitespace-nowrap">อีเมล</th>
            <th class="px-3 py-2 text-center font-semibold whitespace-nowrap hidden lg:table-cell">หน่วยงาน</th>
            <th class="px-3 py-2 text-center font-semibold whitespace-nowrap hidden md:table-cell">บทบาท</th>
            <th class="px-3 py-2 text-center font-semibold whitespace-nowrap hidden xl:table-cell">สร้างเมื่อ</th>
            <th class="px-3 py-2 text-center font-semibold whitespace-nowrap min-w-[160px]">การดำเนินการ</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 text-sm">
          @forelse ($list as $u)
            <tr>
              <td class="px-3 py-2">
                <div class="flex items-center gap-2">
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-xs font-semibold text-white">
                    {{ strtoupper(mb_substr($u->name,0,1)) }}
                  </div>
                  <div>
                    <div class="truncate max-w-[180px] font-medium">{{ $u->name }}</div>
                    <div class="text-xs text-zinc-500">#{{ $u->id }}</div>
                  </div>
                </div>
              </td>

              <td class="px-3 py-2 truncate max-w-[240px]">
                {{ $u->email }}
              </td>

              <td class="px-3 py-2 hidden lg:table-cell truncate max-w-[200px]">
                @php
                  $depName = $u->departmentRef->name ?? $u->department ?? '-';
                @endphp
                {{ $depName ?: '-' }}
              </td>

              <td class="px-3 py-2 hidden md:table-cell">
                @php
                  $isSup = method_exists($u,'isSupervisor') ? $u->isSupervisor() : false;
                @endphp
                <span class="text-[12px] font-medium {{ $isSup ? 'text-emerald-700' : 'text-zinc-700' }}">
                  {{ $u->role_label ?? ($roleLabels[$u->role] ?? ucfirst($u->role)) }}
                </span>
              </td>

              <td class="px-3 py-2 hidden xl:table-cell text-zinc-700 whitespace-nowrap">
                {{ $u->created_at?->format('Y-m-d H:i') }}
              </td>

              <td class="px-3 py-2 text-center align-middle whitespace-nowrap">
                <div class="h-full flex items-center justify-center gap-1.5">
                  <a href="{{ route('admin.users.edit', $u) }}"
                     class="inline-flex items-center gap-1 rounded-md border border-emerald-300 px-2.5 py-1.5 text-[11px] md:text-xs font-medium text-emerald-700 hover:bg-emerald-50 whitespace-nowrap min-w-[64px] justify-center">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 20h9"/>
                      <path d="M16.5 3.5a2.121 2.121 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                    </svg>
                    <span class="hidden sm:inline">แก้ไข</span>
                    <span class="sm:hidden">แก้</span>
                  </a>

                  @if($u->id !== auth()->id())
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-rose-300 px-2.5 py-1.5 text-[11px] md:text-xs font-medium text-rose-600 hover:bg-rose-50 whitespace-nowrap min-w-[60px] justify-center"
                            onclick="return window.confirmDeleteUser('{{ route('admin.users.destroy', $u) }}');">
                      <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18"/>
                        <path d="M8 6V4h8v2"/>
                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                      </svg>
                      <span class="hidden sm:inline">ลบ</span>
                      <span class="sm:hidden">ลบ</span>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-3 py-10 text-center text-zinc-500">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-10 h-10 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>

                  @if($hasFilter)
                    @if($hasS && !$hasRole && !$hasDept)
                      <p class="text-[13px]">
                        ไม่พบผู้ใช้ตามคำค้นหาที่ระบุ
                      </p>
                    @elseif($hasRole && !$hasS && !$hasDept)
                      <p class="text-[13px]">
                        ไม่พบผู้ใช้ตามบทบาทที่เลือก
                      </p>
                    @elseif($hasDept && !$hasS && !$hasRole)
                      <p class="text-[13px]">
                        ไม่พบผู้ใช้ตามหน่วยงานที่เลือก
                      </p>
                    @else
                      <p class="text-[13px]">
                        ไม่พบผู้ใช้ตามเงื่อนไขที่เลือก
                      </p>
                    @endif
                  @else
                    <p class="text-[13px]">
                      ตอนนี้ยังไม่มีผู้ใช้ในระบบ
                    </p>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination ให้ margin ล่างเท่า Maintenance --}}
    <div class="mt-3 mb-6 md:mb-10 lg:mb-12">
      {{ $list->withQueryString()->links() }}
    </div>
  </div>
@endsection

{{-- ===========================
     Tom Select + Styling
     (สไตล์เดียวกับ Create User แต่ scope เป็น .user-filter)
=========================== --}}
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
  /* ========== TomSelect เฉพาะใน header นี้ ========== */
  .user-filter .ts-wrapper.ts-basic {
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    background: transparent;
    width: 100%;
  }

  .user-filter .ts-wrapper.ts-basic .ts-control {
    position: relative;
    border-radius: 0.375rem;
    border: 1px solid rgb(212 212 216);
    padding: 0 0.75rem;
    box-shadow: none;
    min-height: 40px;
    background-color: #fff;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    line-height: 1.25rem;
    white-space: nowrap;
    overflow: hidden;
  }

  .user-filter .ts-wrapper.ts-basic.ts-with-icon .ts-control {
    padding-left: 2.6rem;
  }

  .user-filter .ts-wrapper.ts-basic .ts-control .item {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }

  .user-filter .ts-wrapper.ts-basic .ts-control input {
    font-size: 0.875rem;
    line-height: 1.25rem;
    min-width: 0;
    flex: 1 1 auto;
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    background: transparent;
  }

  .user-filter .ts-wrapper.ts-basic .ts-control.focus {
    border-color: rgb(5,150,105);
    box-shadow: none;
  }

  .user-filter .ts-wrapper.ts-basic .ts-dropdown {
    border-radius: 0.5rem;
    border-color: rgb(226,232,240);
    box-shadow: 0 10px 15px -3px rgba(15,23,42,0.15);
    z-index: 60;
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .user-filter .ts-wrapper.ts-basic .ts-dropdown .option {
    padding: 0.35rem 0.75rem;
    color: rgb(63,63,70);
  }

  .user-filter .ts-wrapper.ts-basic .ts-dropdown .option:hover {
    background-color: rgb(244,244,245);
  }

  .user-filter .ts-wrapper.ts-basic .ts-dropdown .selected {
    background-color: rgb(226,232,240);
  }

  .user-filter .ts-select-icon {
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

  .user-filter .ts-select-icon svg {
    width: 16px;
    height: 16px;
  }

  .user-filter select.ts-hidden-accessible {
    display: none !important;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {

    function initTomSelectWithIcon(selector, placeholderText) {
      const el = document.querySelector(selector);
      if (!el) return;

      const ts = new TomSelect(el, {
        create: false,
        allowEmptyOption: true,
        maxOptions: 500,
        sortField: { field: 'text', direction: 'asc' },
        placeholder: placeholderText,
        searchField: ['text'],
      });

      setTimeout(function () {
        const wrapper = ts.wrapper;
        const control = ts.control;
        if (!wrapper || !control) return;

        wrapper.classList.add('ts-basic', 'ts-with-icon');

        const oldIcon = control.querySelector('.ts-select-icon');
        if (oldIcon) oldIcon.remove();

        const icon = document.createElement('span');
        icon.className = 'ts-select-icon';
        icon.innerHTML = `
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="5" stroke="currentColor" stroke-width="2"></circle>
            <path d="M15 15l4 4" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        `;
        control.insertBefore(icon, control.firstChild);
      }, 0);
    }

    initTomSelectWithIcon('#filter_role', 'บทบาททั้งหมด');
    initTomSelectWithIcon('#filter_department', 'ทุกหน่วยงาน');

    window.confirmDeleteUser = function(url){
      if(!confirm('ยืนยันการลบผู้ใช้นี้?')) return false;
      const f = document.getElementById('delete-user-form');
      if(!f) return true;
      f.action = url;
      f.submit();
      return false;
    };
  });
</script>

<form id="delete-user-form" method="POST" class="hidden">
  @csrf
  @method('DELETE')
</form>
