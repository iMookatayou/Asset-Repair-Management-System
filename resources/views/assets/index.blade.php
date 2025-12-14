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
        // 🔵 สี active = น้ำเงินธีมคุณ
        $labelClass .= 'text-[#0F2D5C]';
        $iconClass  .= ' text-[#0F2D5C]';
    } else {
        $labelClass .= 'text-zinc-700 group-hover:text-zinc-900';
        $iconClass  .= ' text-zinc-300 group-hover:text-zinc-400';
    }

    // asc = เลขน้อย → มาก (ลูกศรขึ้น)
    // desc = เลขมาก → น้อย (ลูกศรลง)
    $iconPathAsc  = 'M12 7l-4 6h8l-4-6z';
    $iconPathDesc = 'M12 17l4-6H8l4 6z';
    $iconPath     = ($isActive && $sortDir === 'asc')
        ? $iconPathAsc
        : $iconPathDesc;

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

  // ===== สี/ชื่อสถานะทรัพย์สิน (ไม่ทำกรอบ) =====
  $statusTextClass = fn(?string $s) => match(strtolower((string)$s)) {
    'active'    => 'text-emerald-700',
    'in_repair' => 'text-amber-700',
    'disposed'  => 'text-rose-700',
    default     => 'text-slate-700',
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

  $hasQ        = ($q ?? '') !== '';
  $hasStatus   = ($status ?? '') !== '';
  $hasCategory = ($categoryId ?? '') !== '';
  $hasDept     = ($deptId ?? '') !== '';
  $hasType     = ($type ?? '') !== '';
  $hasLocation = ($location ?? '') !== '';

  $hasFilter = $hasQ || $hasStatus || $hasCategory || $hasDept || $hasType || $hasLocation;
@endphp

<div class="pt-6 md:pt-8 lg:pt-10"></div>

<div class="w-full flex flex-col">

  {{-- Sticky Header + Filters --}}
  <div class="sticky top-[6rem] z-20 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="px-4 md:px-6 lg:px-8 py-4">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-[17px] font-semibold text-slate-900">Assets</h1>
          <p class="text-[13px] text-slate-600">ข้อมูลทรัพย์สิน / ครุภัณฑ์ • ค้นหา กรอง และจัดการรายการ</p>
        </div>

        <a href="{{ route('assets.create') }}"
           class="inline-flex items-center gap-2 rounded-md bg-[#0F2D5C] px-4 py-2 text-[13px] font-medium text-white hover:bg-[#0F2D5C]/90 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/40"
           onclick="showLoader()">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
          </svg>
          เพิ่มทรัพย์สิน
        </a>
      </div>

      <form method="GET"
            action="{{ route('assets.index') }}"
            class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
            onsubmit="showLoader()">

        {{-- ✅ เก็บค่าการ sort ไว้เสมอ --}}
        <input type="hidden" name="sort_by"  value="{{ $sortBy }}">
        <input type="hidden" name="sort_dir" value="{{ $sortDir }}">

        <div class="md:col-span-7">
          <label class="mb-1 block text-[12px] text-slate-600">คำค้นหา</label>
          <div class="relative">
            <input name="q" value="{{ $q }}"
                   class="w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 py-2 text-[13px] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35"
                   placeholder="เช่น รหัสทรัพย์สิน / ชื่อทรัพย์สิน / Serial number">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
          </div>
        </div>

        <div class="md:col-span-3">
          <label class="mb-1 block text-[12px] text-slate-600">สถานะการใช้งาน</label>
          <select name="status"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            @foreach($statuses as $k => $v)
              <option value="{{ $k }}" @selected(($status ?? '') === $k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-2 flex items-end justify-end gap-2">
          <a href="{{ route('assets.index') }}"
             onclick="showLoader()"
             class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/30 focus:ring-offset-1"
             title="ล้างตัวกรอง" aria-label="ล้างตัวกรอง">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </a>

          <button type="submit"
                  class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#0F2D5C] text-white hover:bg-[#0F2D5C]/90 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/45 focus:ring-offset-1"
                  title="ค้นหา" aria-label="ค้นหา">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>
        </div>

        <div class="md:col-span-3">
          <label class="mb-1 block text-[12px] text-slate-600">ประเภททรัพย์สิน</label>
          <input name="type" value="{{ $type }}"
                 class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
        </div>

        <div class="md:col-span-3">
          <label class="mb-1 block text-[12px] text-slate-600">ที่ตั้ง / ห้อง</label>
          <input name="location" value="{{ $location }}"
                 class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
        </div>

        <div class="md:col-span-3">
          <label class="mb-1 block text-[12px] text-slate-600">หมวดหมู่ทรัพย์สิน</label>
          <select name="category_id"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            <option value="">ทั้งหมด</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}" @selected(($categoryId ?? null) == $c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-3">
          <label class="mb-1 block text-[12px] text-slate-600">หน่วยงานเจ้าของทรัพย์สิน</label>
          <select name="department_id"
                  class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F2D5C]/35 focus:border-[#0F2D5C]/35">
            <option value="">ทั้งหมด</option>
            @foreach($departments as $d)
              <option value="{{ $d['id'] }}" @selected(($deptId ?? null) == $d['id'])>{{ $d['display_name'] }}</option>
            @endforeach
          </select>
        </div>
      </form>
    </div>
  </div>

  <div class="px-4 md:px-6 lg:px-8 py-2 bg-slate-50 border-b border-slate-200">
    <div class="text-[12px] font-semibold uppercase tracking-[0.16em] text-slate-600">
      รายการทรัพย์สิน
    </div>
  </div>

  {{-- Table Desktop --}}
  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full text-[13px]">
      <thead class="bg-white">
        <tr class="text-slate-600">
          <th class="p-3 text-center font-semibold border-b border-slate-200 w-[10%] whitespace-nowrap">
            {!! $sortableId() !!}
          </th>
          <th class="p-3 text-center font-semibold border-b border-slate-200 whitespace-nowrap">รหัสทรัพย์สิน</th>
          <th class="p-3 text-center font-semibold border-b border-slate-200">ชื่อทรัพย์สิน</th>
          <th class="p-3 text-center font-semibold border-b border-slate-200 hidden xl:table-cell">หมวดหมู่</th>
          <th class="p-3 text-center font-semibold border-b border-slate-200 hidden lg:table-cell">ที่ตั้ง</th>
          <th class="p-3 text-center font-semibold border-b border-slate-200 whitespace-nowrap">สถานะ</th>
          <th class="p-3 text-center font-semibold border-b border-slate-200 min-w-[200px] whitespace-nowrap">การดำเนินการ</th>
        </tr>
      </thead>

      <tbody class="bg-white">
      @forelse($assets as $a)
        <tr class="hover:bg-slate-50/60 border-b border-slate-100 last:border-0">
          <td class="p-3 text-center align-middle whitespace-nowrap font-semibold text-slate-900">{{ $a->id }}</td>
          <td class="p-3 text-center align-middle whitespace-nowrap font-semibold text-slate-900">{{ $a->asset_code }}</td>

          <td class="p-3 align-middle">
            <a href="{{ route('assets.show',$a) }}"
               class="block max-w-full truncate font-semibold text-slate-900 hover:underline"
               onclick="showLoader()">
              {{ $a->name }}
            </a>
            <div class="text-[11px] text-slate-500">S/N: {{ $a->serial_number ?? '—' }}</div>
          </td>

          <td class="p-3 text-center hidden xl:table-cell align-middle text-slate-700">
            {{ optional($a->categoryRef)->name ?? '—' }}
          </td>

          <td class="p-3 text-center hidden lg:table-cell align-middle text-slate-700">
            {{ $a->location ?? '—' }}
          </td>

          <td class="p-3 text-center align-middle whitespace-nowrap">
            <span class="text-[12px] font-semibold {{ $statusTextClass($a->status) }}">
              {{ $statusLabel($a->status) }}
            </span>
          </td>

          <td class="p-3 text-center align-middle whitespace-nowrap">
            <div class="flex justify-center gap-2">
              <a href="{{ route('assets.show',$a) }}"
                 class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 bg-white px-3 py-1.5 text-[12px] font-medium text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                 onclick="showLoader()">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                </svg>
                ดูรายละเอียด
              </a>

              <a href="{{ route('assets.edit',$a) }}"
                 class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-[12px] font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                 onclick="showLoader()">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                </svg>
                แก้ไข
              </a>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="py-16 text-center text-slate-600">
            <div class="flex flex-col items-center gap-2">
              <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              @if($hasFilter)
                <p class="text-[13px]">ไม่พบข้อมูลทรัพย์สินตามเงื่อนไขที่เลือก</p>
              @else
                <p class="text-[13px]">ตอนนี้ยังไม่มีข้อมูลทรัพย์สินในระบบ</p>
              @endif
            </div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile Cards --}}
  <div class="mt-6 md:hidden grid gap-3 px-4">
    @forelse($assets as $a)
      <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex justify-between gap-3">
          <div class="min-w-0">
            <div class="text-[11px] text-slate-500">#{{ $a->id }} — {{ $a->asset_code }}</div>
            <a class="block truncate font-semibold text-slate-900 hover:underline"
               href="{{ route('assets.show',$a) }}"
               onclick="showLoader()">
              {{ $a->name }}
            </a>
            <div class="text-[11px] text-slate-500">S/N: {{ $a->serial_number ?? '—' }}</div>
          </div>
          <span class="text-[12px] font-semibold {{ $statusTextClass($a->status) }}">
            {{ $statusLabel($a->status) }}
          </span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-[13px]">
          <div class="text-slate-500">หมวดหมู่</div>
          <div class="text-slate-800">{{ optional($a->categoryRef)->name ?? '—' }}</div>
          <div class="text-slate-500">ที่ตั้ง</div>
          <div class="text-slate-800">{{ $a->location ?? '—' }}</div>
        </div>

        <div class="mt-3 flex justify-end gap-2">
          <a href="{{ route('assets.show',$a) }}"
             class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 bg-white px-3 py-2 text-[12px] font-medium text-indigo-700 hover:bg-indigo-50"
             onclick="showLoader()">ดูรายละเอียด</a>
          <a href="{{ route('assets.edit',$a) }}"
             class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 bg-white px-3 py-2 text-[12px] font-medium text-emerald-700 hover:bg-emerald-50"
             onclick="showLoader()">แก้ไข</a>
        </div>
      </div>
    @empty
      <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-600 text-[13px]">
        @if($hasFilter) ไม่พบข้อมูลทรัพย์สินตามเงื่อนไขที่เลือก @else ตอนนี้ยังไม่มีข้อมูลทรัพย์สินในระบบ @endif
      </div>
    @endforelse
  </div>

  @if($assets->hasPages())
    <div class="px-4 md:px-6 lg:px-8 mt-4 mb-6 md:mb-10 lg:mb-12">
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
  function showLoader(){document.getElementById('loaderOverlay')?.classList.add('show')}
  function hideLoader(){document.getElementById('loaderOverlay')?.classList.remove('show')}
  document.addEventListener('DOMContentLoaded', hideLoader);
</script>
@endsection
