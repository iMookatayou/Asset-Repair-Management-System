@props([
  'name',
  'endpoint', // e.g. route('meta.departments') or url('/api/meta/departments')
  'labelField' => 'name',
  'valueField' => 'id',
  'placeholder' => '— เลือก —',
  'searchPlaceholder' => 'พิมพ์เพื่อค้นหา...',
  'minChars' => 0,
  'debounce' => 180,
  'panelMaxHeight' => '18rem',
  'value' => null,      // optional preselected value
  'valueLabel' => null, // optional label for preselected value
  'withIcon' => true,   // แสดงไอคอนแว่นซ้ายมือ
])

@php
  $id = $id ?? $name;
  $hasValue = !is_null($value) && $value !== '';
@endphp

<div
  class="relative"
  data-dsd
  data-dsd-endpoint="{{ $endpoint }}"
  data-dsd-label-field="{{ $labelField }}"
  data-dsd-value-field="{{ $valueField }}"
  data-dsd-min-chars="{{ $minChars }}"
  data-dsd-debounce="{{ $debounce }}"
  data-dsd-id="{{ $id }}"
  @if($hasValue) data-dsd-initial="1" @endif
>
  {{-- hidden real value --}}
  <input type="hidden" name="{{ $name }}" value="{{ $hasValue ? $value : '' }}" data-dsd-input>

  {{-- ปุ่มแสดงค่า + ไอคอนแว่นอยู่ในปุ่มเลย ไม่ต้องวาง absolute ข้างนอก --}}
  <button
    type="button"
    data-dsd-display
    aria-haspopup="listbox"
    aria-expanded="false"
    class="mt-1 w-full flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
           text-left focus:outline-none focus:ring-emerald-600 focus:border-emerald-600"
  >
    @if($withIcon)
      <span class="mr-2 flex items-center text-slate-400">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2" />
          <path d="M16 16l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
      </span>
    @endif

    <span
      data-dsd-display-text
      class="flex-1 truncate text-slate-700 {{ $hasValue ? '' : 'opacity-70' }}"
    >
      {{ $hasValue ? ($valueLabel ?? $value) : $placeholder }}
    </span>

    <svg class="ml-2 h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
      <path fill-rule="evenodd"
            d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
            clip-rule="evenodd" />
    </svg>
  </button>

  {{-- แผง dropdown --}}
  <div
    data-dsd-panel
    class="absolute top-full left-0 right-0 z-50 mt-1 rounded-lg border border-slate-200 bg-white shadow-lg hidden"
    style="max-height: {{ $panelMaxHeight }}"
  >
    {{-- ช่อง search ด้านใน panel --}}
    <div class="p-2 border-b border-slate-100">
      <input
        type="text"
        data-dsd-search
        placeholder="{{ $searchPlaceholder }}"
        autocomplete="off"
        class="w-full rounded-md border border-slate-300 bg-white px-2 py-1.5 text-xs
               focus:border-emerald-600 focus:outline-none focus:ring-emerald-600"
        aria-label="ค้นหา"
      >
    </div>

    <ul
      data-dsd-list
      role="listbox"
      class="overflow-y-auto text-sm"
      style="max-height: calc({{ $panelMaxHeight }} - 46px)"
    ></ul>

    <div data-dsd-empty class="hidden px-3 py-2 text-xs text-slate-500">
      ไม่พบรายการ
    </div>
    <div data-dsd-loading class="hidden px-3 py-2 text-xs text-slate-500">
      กำลังโหลด...
    </div>
    <div data-dsd-error class="hidden px-3 py-2 text-xs text-red-600">
      เกิดข้อผิดพลาดในการดึงข้อมูล
    </div>
  </div>
</div>
