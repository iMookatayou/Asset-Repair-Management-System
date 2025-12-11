@extends('layouts.app')
@section('title','ทรัพย์สิน')

@section('content')
@php
  use Illuminate\Support\Str;

  // ===== Sorting เฉพาะคอลัมน์เลขลำดับ =====
  $sortBy  = $sortBy  ?? request('sort_by', 'id');
  $sortDir = $sortDir ?? request('sort_dir', 'desc');

  $sortableId = function() use ($sortBy, $sortDir) {
      $isActive = $sortBy === 'id';
      $nextDir  = $isActive && $sortDir === 'asc' ? 'desc' : 'asc';

      $url = request()->fullUrlWithQuery([
          'sort_by'  => 'id',
          'sort_dir' => $nextDir,
      ]);

      $labelClass = 'text-[13px] font-semibold whitespace-nowrap ';
      $iconClass  = 'h-3.5 w-3.5';

      if ($isActive) {
          $labelClass .= 'text-emerald-700';
          $iconClass  .= ' text-emerald-600';
      } else {
          $labelClass .= 'text-zinc-700 group-hover:text-zinc-900';
          $iconClass  .= ' text-zinc-300 group-hover:text-zinc-400';
      }

      $iconPathAsc  = 'M12 7l-4 6h8l-4-6z';
      $iconPathDesc = 'M12 17l4-6H8l4 6z';
      $iconPath = $isActive && $sortDir === 'asc' ? $iconPathAsc : $iconPathDesc;

      return <<<HTML
<a href="{$url}" class="inline-flex items-center justify-center gap-1.5 group select-none">
  <span class="{$labelClass}">
    เลขลำดับ
  </span>
  <span class="inline-flex items-center">
    <svg viewBox="0 0 24 24" class="{$iconClass}">
      <path d="{$iconPath}" fill="currentColor" />
    </svg>
  </span>
</a>
HTML;
  };

  // ===== สี/ชื่อสถานะทรัพย์สิน =====
  $statusTextClass = fn(?string $s) => match(strtolower((string)$s)) {
    'active'    => 'text-emerald-700',
    'in_repair' => 'text-amber-700',
    'disposed'  => 'text-rose-700',
    default     => 'text-zinc-700',
  };

  $statusLabel = fn(?string $s) => match(strtolower((string)$s)) {
    'active'    => 'พร้อมใช้งาน',
    'in_repair' => 'อยู่ระหว่างซ่อม',
    'disposed'  => 'จำหน่ายแล้ว',
    default     => 'ไม่ทราบสถานะ',
  };

  // ค่าจาก Controller (fallback เป็น request ถ้าไม่ได้ส่งมา)
  $q          = $q          ?? request('q');
  $status     = $status     ?? request('status');
  $categoryId = $categoryId ?? request('category_id');
  $deptId     = $deptId     ?? request('department_id');
  $type       = $type       ?? request('type');
  $location   = $location   ?? request('location');

  $statuses = [
    ''          => 'ทั้งหมด',
    'active'    => 'พร้อมใช้งาน',
    'in_repair' => 'อยู่ระหว่างซ่อม',
    'disposed'  => 'จำหน่ายแล้ว',
  ];

  // ใช้ flag สำหรับ empty state
  $hasQ        = ($q ?? '') !== '';
  $hasStatus   = ($status ?? '') !== '';
  $hasCategory = ($categoryId ?? '') !== '';
  $hasDept     = ($deptId ?? '') !== '';
  $hasType     = ($type ?? '') !== '';
  $hasLocation = ($location ?? '') !== '';

  $hasFilter = $hasQ || $hasStatus || $hasCategory || $hasDept || $hasType || $hasLocation;
@endphp

{{-- ระยะใต้ navbar เหมือน Maintenance --}}
<div class="pt-6 md:pt-8 lg:pt-10"></div>

<div class="w-full px-4 md:px-6 lg:px-8 flex flex-col gap-4">
  {{-- ===== Sticky Header + Filter Card ===== --}}
  <div class="sticky top-[6rem] z-20 bg-slate-50/90 backdrop-blur">
    <div class="rounded-lg border border-zinc-300 bg-white shadow-sm">
      <div class="px-5 py-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
          {{-- Left: Icon + Title --}}
          <div class="flex items-start gap-3">
            <div class="grid h-9 w-9 place-items-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M6 11h8m-8 4h12M4 5v14a2 2 0 0 0 2 2h12l2-2V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2Z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-[17px] font-semibold text-zinc-900">Assets</h1>
              <p class="text-[13px] text-zinc-600">
                ข้อมูลทรัพย์สิน / ครุภัณฑ์ • ค้นหา กรอง และจัดการรายการ
              </p>
            </div>
          </div>

          {{-- Right: Create button --}}
          <div class="flex shrink-0 items-center">
            <a href="{{ route('assets.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-emerald-700 bg-emerald-700 px-4 py-2 text-[13px] font-medium text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600"
               onclick="showLoader()">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
              </svg>
              เพิ่มทรัพย์สิน
            </a>
          </div>
        </div>

        <div class="mt-4 h-px bg-zinc-200"></div>

        {{-- Filters --}}
        <form method="GET"
              action="{{ route('assets.index') }}"
              class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
              onsubmit="showLoader()">

          {{-- ค่าการ sort ปัจจุบัน --}}
          <input type="hidden" name="sort_by"  value="{{ $sortBy }}">
          <input type="hidden" name="sort_dir" value="{{ $sortDir }}">

          {{-- คำค้น --}}
          <div class="md:col-span-7">
            <label class="mb-1 block text-[12px] text-zinc-600">คำค้นหา</label>
            <div class="relative">
              <input name="q" value="{{ $q }}"
                     class="w-full rounded-md border border-zinc-300 pl-12 pr-3 py-2 text-[13px] placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600"
                     placeholder="เช่น รหัสทรัพย์สิน / ชื่อทรัพย์สิน / Serial number">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-zinc-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </span>
            </div>
          </div>

          {{-- สถานะ --}}
          <div class="md:col-span-3">
            <label class="mb-1 block text-[12px] text-zinc-600">สถานะการใช้งาน</label>
            <select name="status"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-[13px] text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              @foreach($statuses as $k => $v)
                <option value="{{ $k }}" @selected(($status ?? '') === $k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>

          {{-- ปุ่มค้นหา (แบบกลม icon-only, สีเดียวกับ My Jobs / Maintenance) --}}
          <div class="md:col-span-2 flex items-end justify-end gap-2">
            {{-- ล้างตัวกรอง: ล้างทุกค่ากลับ assets.index โล่ง ๆ --}}
            <a href="{{ route('assets.index') }}"
               onclick="showLoader()"
               aria-label="ล้างตัวกรอง"
               title="ล้างตัวกรอง"
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
              <span class="sr-only">ล้างตัวกรอง</span>
            </a>

            {{-- ค้นหา: ต้องกดปุ่มนี้ถึงจะ apply filters --}}
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

          {{-- ประเภท --}}
          <div class="md:col-span-3">
            <label class="mb-1 block text-[12px] text-zinc-600">ประเภททรัพย์สิน</label>
            <input name="type" value="{{ $type }}"
                   class="w-full rounded-md border border-zinc-300 px-3 py-2 text-[13px]">
          </div>

          {{-- ที่ตั้ง --}}
          <div class="md:col-span-3">
            <label class="mb-1 block text-[12px] text-zinc-600">ที่ตั้ง / ห้อง</label>
            <input name="location" value="{{ $location }}"
                   class="w-full rounded-md border border-zinc-300 px-3 py-2 text-[13px]">
          </div>

          {{-- หมวดหมู่ --}}
          <div class="md:col-span-3">
            <label class="mb-1 block text-[12px] text-zinc-600">หมวดหมู่ทรัพย์สิน</label>
            <select name="category_id"
                    class="w-full rounded-md border border-zinc-300 px-3 py-2 text-[13px]">
              <option value="">ทั้งหมด</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(($categoryId ?? null) == $c->id)>
                  {{ $c->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- หน่วยงาน --}}
          <div class="md:col-span-3">
            <label class="mb-1 block text-[12px] text-zinc-600">หน่วยงานเจ้าของทรัพย์สิน</label>
            <select name="department_id"
                    class="w-full rounded-md border border-zinc-300 px-3 py-2 text-[13px]">
              <option value="">ทั้งหมด</option>
              @foreach($departments as $d)
                <option value="{{ $d['id'] }}" @selected(($deptId ?? null) == $d['id'])>
                  {{ $d['display_name'] }}
                </option>
              @endforeach
            </select>
          </div>

        </form>
      </div>
    </div>
  </div>

  {{-- ===== Table Card (Desktop) ===== --}}
  <div class="mt-6 md:mt-8 lg:mt-10 hidden md:block rounded-lg border border-zinc-300 bg-white overflow-hidden">
    <div class="relative overflow-x-auto">
      <table class="min-w-full text-[13px]">
        <thead class="bg-zinc-50 border-b border-zinc-200">
          <tr class="text-zinc-700">
            <th class="p-3 text-center font-semibold">{!! $sortableId() !!}</th>
            <th class="p-3 text-center font-semibold">รหัสทรัพย์สิน</th>
            <th class="p-3 text-center font-semibold">ชื่อทรัพย์สิน</th>
            <th class="p-3 text-center font-semibold hidden xl:table-cell">หมวดหมู่</th>
            <th class="p-3 text-center font-semibold hidden lg:table-cell">ที่ตั้ง</th>
            <th class="p-3 text-center font-semibold">สถานะ</th>
            <th class="p-3 text-center font-semibold min-w-[200px]">การดำเนินการ</th>
          </tr>
        </thead>

        <tbody class="bg-white">
        @forelse($assets as $a)
          <tr class="hover:bg-zinc-50 border-b last:border-0">
            <td class="p-3 text-center align-middle text-zinc-700 whitespace-nowrap">
              {{ $a->id }}
            </td>
            <td class="p-3 text-center font-medium text-zinc-900">
              {{ $a->asset_code }}
            </td>
            <td class="p-3">
              <a href="{{ route('assets.show',$a) }}"
                 class="block text-center md:text-left max-w-full truncate font-medium text-emerald-700 hover:underline"
                 onclick="showLoader()">
                {{ $a->name }}
              </a>
              <div class="text-[11px] text-zinc-500 text-center md:text-left">
                S/N: {{ $a->serial_number ?? '—' }}
              </div>
            </td>
            <td class="p-3 text-center hidden xl:table-cell text-zinc-700">
              {{ optional($a->categoryRef)->name ?? '—' }}
            </td>
            <td class="p-3 text-center hidden lg:table-cell text-zinc-700">
              {{ $a->location ?? '—' }}
            </td>
            <td class="p-3 text-center align-middle">
              <span class="text-[12px] font-medium {{ $statusTextClass($a->status) }}">
                {{ $statusLabel($a->status) }}
              </span>
            </td>
            <td class="p-3 text-center align-middle">
              <div class="flex justify-center gap-2">
                <a href="{{ route('assets.show',$a) }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 bg-white px-3 py-1.5 text-[12px] font-medium text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                   onclick="showLoader()">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                  </svg>
                  ดูรายละเอียด
                </a>
                <a href="{{ route('assets.edit',$a) }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-[12px] font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                   onclick="showLoader()">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                  </svg>
                  แก้ไข
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="p-12 text-center text-zinc-600">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>

                @if($hasFilter)
                  @if($hasQ && !$hasStatus && !$hasCategory && !$hasDept && !$hasType && !$hasLocation)
                    <p class="text-[13px]">
                      ไม่พบทรัพย์สินตามคำค้นหาที่ระบุ
                    </p>
                  @elseif($hasStatus && !$hasQ && !$hasCategory && !$hasDept && !$hasType && !$hasLocation)
                    <p class="text-[13px]">
                      ไม่พบทรัพย์สินตามสถานะการใช้งานที่เลือก
                    </p>
                  @elseif($hasCategory && !$hasQ && !$hasStatus && !$hasDept && !$hasType && !$hasLocation)
                    <p class="text-[13px]">
                      ไม่พบทรัพย์สินตามหมวดหมู่ที่เลือก
                    </p>
                  @elseif($hasDept && !$hasQ && !$hasStatus && !$hasCategory && !$hasType && !$hasLocation)
                    <p class="text-[13px]">
                      ไม่พบทรัพย์สินตามหน่วยงานที่เลือก
                    </p>
                  @elseif($hasType && !$hasQ && !$hasStatus && !$hasCategory && !$hasDept && !$hasLocation)
                    <p class="text-[13px]">
                      ไม่พบทรัพย์สินตามประเภทที่เลือก
                    </p>
                  @elseif($hasLocation && !$hasQ && !$hasStatus && !$hasCategory && !$hasDept && !$hasType)
                    <p class="text-[13px]">
                      ไม่พบทรัพย์สินตามที่ตั้งที่ระบุ
                    </p>
                  @else
                    <p class="text-[13px]">
                      ไม่พบข้อมูลทรัพย์สินตามเงื่อนไขที่เลือก
                    </p>
                  @endif
                @else
                  <p class="text-[13px]">
                    ตอนนี้ยังไม่มีข้อมูลทรัพย์สินในระบบ
                  </p>
                @endif
              </div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ===== Mobile Cards ===== --}}
  <div class="mt-6 md:hidden grid gap-3">
    @forelse($assets as $a)
      <div class="rounded-lg border border-zinc-300 bg-white p-4">
        <div class="flex justify-between">
          <div>
            <div class="text-[11px] text-zinc-500">#{{ $a->id }} — {{ $a->asset_code }}</div>
            <a class="font-medium text-zinc-900 hover:underline"
               href="{{ route('assets.show',$a) }}"
               onclick="showLoader()">
              {{ $a->name }}
            </a>
            <div class="text-[11px] text-zinc-500">
              S/N: {{ $a->serial_number ?? '—' }}
            </div>
          </div>
          <span class="text-[11px] font-medium {{ $statusTextClass($a->status) }}">
            {{ $statusLabel($a->status) }}
          </span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-[13px]">
          <div class="text-zinc-500">หมวดหมู่</div>
          <div>{{ optional($a->categoryRef)->name ?? '—' }}</div>
          <div class="text-zinc-500">ที่ตั้ง</div>
          <div>{{ $a->location ?? '—' }}</div>
        </div>

        <div class="mt-3 flex justify-end gap-2">
          <a href="{{ route('assets.show',$a) }}"
             class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 px-3 py-2 text-[12px] text-indigo-700 hover:bg-indigo-50"
             onclick="showLoader()">
            ดู
          </a>
          <a href="{{ route('assets.edit',$a) }}"
             class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 px-3 py-2 text-[12px] text-emerald-700 hover:bg-emerald-50"
             onclick="showLoader()">
            แก้ไข
          </a>
        </div>
      </div>
    @empty
      <div class="rounded-lg border border-zinc-300 bg-white p-8 text-center text-zinc-600 text-[13px]">
        @if($hasFilter)
          ไม่พบข้อมูลทรัพย์สินตามเงื่อนไขที่เลือก
        @else
          ตอนนี้ยังไม่มีข้อมูลทรัพย์สินในระบบ
        @endif
      </div>
    @endforelse
  </div>

  {{-- ===== Pagination (เหมือน Maintenance) ===== --}}
  @if($assets->hasPages())
    <div class="mt-3 mb-6 md:mb-10 lg:mb-12">
      {{ $assets->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection

@section('after-content')
<div id="loaderOverlay" class="loader-overlay">
  <div class="loader-spinner"></div>
</div>
<style>
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:38px;height:38px;border:4px solid #0E2B51;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>
<script>
  function showLoader(){document.getElementById('loaderOverlay').classList.add('show')}
  function hideLoader(){document.getElementById('loaderOverlay').classList.remove('show')}
  document.addEventListener('DOMContentLoaded', hideLoader);
</script>
@endsection
