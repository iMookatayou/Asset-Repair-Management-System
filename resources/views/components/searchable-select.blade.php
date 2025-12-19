@props([
  'name',
  'id' => null,
  'items' => collect(),
  'labelField' => 'name',
  'valueField' => 'id',
  'value' => null,
  'placeholder' => '— ไม่ระบุ —',
  'variant' => 'dropdown',
  'inline' => true,
])

@php
  $id = $id ?: $name;
  $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
  $current = old($name, $value);
  $current = is_null($current) ? '' : (string)$current;

  $selected = $items->first(fn($it) =>
    (string)data_get($it, $valueField) === $current
  );

  $selectedLabel = $selected ? (string)data_get($selected, $labelField) : '';
@endphp

<div class="relative" data-ss data-ss-variant="dropdown" data-ss-inline="1">
  <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $current }}" data-ss-input>

  <div data-ss-display class="relative">
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
        <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2"/>
      </svg>
    </span>

    <input
      type="text"
      data-ss-text
      class="w-full h-11 rounded-md border border-slate-300 bg-white pl-10 pr-10 px-3 text-sm
             focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
      autocomplete="off"
      placeholder="{{ $selectedLabel ? '' : $placeholder }}"
      value="{{ $selectedLabel }}"
    />

    <button type="button"
            data-ss-inline-toggle
            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2"/>
      </svg>
    </button>
  </div>

  <div data-ss-panel
       class="hidden absolute z-30 mt-2 w-full rounded-lg border border-slate-200 bg-white shadow-lg">

    <ul data-ss-list class="max-h-64 overflow-auto py-1">
      <li data-ss-option
          data-value=""
          data-label="{{ $placeholder }}"
          class="cursor-pointer px-3 py-2 text-sm hover:bg-slate-50">
        {{ $placeholder }}
      </li>

      @foreach($items as $it)
        @php
          $val = (string)data_get($it, $valueField);
          $lbl = trim((string)data_get($it, $labelField));
        @endphp
        <li data-ss-option
            data-value="{{ $val }}"
            data-label="{{ $lbl }}"
            class="cursor-pointer px-3 py-2 text-sm hover:bg-slate-50">
          {{ $lbl }}
        </li>
      @endforeach
    </ul>

    <div data-ss-empty class="hidden px-3 py-2 text-sm text-slate-500">
      ไม่พบรายการ
    </div>
  </div>
</div>
