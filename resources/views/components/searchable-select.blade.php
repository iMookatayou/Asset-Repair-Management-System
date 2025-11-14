@props([
  'name',
  'id' => null,
  'items' => [],
  'labelField' => 'name',
  'valueField' => 'id',
  'value' => null,
  'placeholder' => '— เลือก —',
  // variant: input (existing behavior) | dropdown (button shows value, search box inside panel)
  'variant' => 'input',
  // show search box inside panel for dropdown variant
  'searchPlaceholder' => 'พิมพ์เพื่อค้นหา...',
  // inline typing (dropdown variant only): input embedded in the button itself
  'inline' => false,
  // meta fields: extra fields shown under main label for each option
  'metaFields' => [],
])

@php
  $id = $id ?: $name;
  $collection = is_iterable($items) ? collect($items) : collect($items ?? []);
  $selectedVal = old($name, $value);
  $selectedLabel = '';
  if (!is_null($selectedVal) && $selectedVal !== '') {
    $selectedItem = $collection->firstWhere($valueField, $selectedVal);
    if ($selectedItem) {
      // Fallback label resolution to avoid blank options when labelField is missing
      $selectedLabel = data_get($selectedItem, $labelField)
        ?? data_get($selectedItem, 'display_name')
        ?? data_get($selectedItem, 'name')
        ?? '';
    }
  }
  // If nothing selected, default to first item to ensure visible data before interaction
  if (($selectedVal === null || $selectedVal === '') && $collection->count()) {
    $first = $collection->first();
    $selectedVal = data_get($first, $valueField);
    $selectedLabel = data_get($first, $labelField)
      ?? data_get($first, 'display_name')
      ?? data_get($first, 'name')
      ?? (string) $selectedVal;
  }
  $listId = $id.'-listbox';
  // Precompute items for JSON to avoid inline arrow function parsing issues in Blade
  $jsonItems = $collection->map(function ($item) use ($valueField, $labelField) {
    return [
      'value' => data_get($item, $valueField),
      'label' => data_get($item, $labelField),
    ];
  })->values();
@endphp

@php $inlineAttr = $inline && $variant==='dropdown' ? '1' : '0'; @endphp
<div class="relative" data-ss data-ss-id="{{ $id }}" aria-haspopup="listbox" data-ss-variant="{{ $variant }}" data-ss-inline="{{ $inlineAttr }}">
  <input type="hidden" name="{{ $name }}" value="{{ $selectedVal }}" data-ss-input>

  @if($variant === 'dropdown')
    @if($inline)
      <div id="{{ $id }}" data-ss-display aria-controls="{{ $listId }}" aria-expanded="false"
           class="mt-1 w-full flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus-within:border-emerald-600 focus-within:ring-emerald-600">
        <input type="text" data-ss-text
               class="flex-1 bg-transparent outline-none border-none p-0 m-0 text-sm"
               placeholder="{{ $placeholder }}"
               value="{{ $selectedLabel }}" autocomplete="off" aria-label="ค้นหาและเลือก">
        <button type="button" data-ss-inline-toggle class="shrink-0 text-slate-400 hover:text-slate-600" tabindex="-1" aria-label="เปิดตัวเลือก">
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    @else
      <button type="button" id="{{ $id }}" data-ss-display aria-controls="{{ $listId }}" aria-expanded="false"
              class="mt-1 w-full flex items-center justify-between rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-left focus:border-emerald-600 focus:outline-none focus:ring-emerald-600">
        <span data-ss-display-text class="truncate">{{ $selectedLabel ?: $placeholder }}</span>
        <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </button>
    @endif
  @else
    <div class="relative mt-1">
      <input id="{{ $id }}" type="text" role="combobox"
             aria-controls="{{ $listId }}" aria-expanded="false"
             placeholder="{{ $placeholder }}"
             value="{{ $selectedLabel }}"
             class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-emerald-600"
             autocomplete="off" data-ss-text>

      <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" tabindex="-1" data-ss-toggle>
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  @endif

  <div class="absolute top-full left-0 right-0 z-50 mt-1 rounded-lg border border-slate-200 bg-white shadow-lg hidden pointer-events-auto" data-ss-panel>
    @if($variant === 'dropdown' && !$inline)
      <div class="p-2 border-b border-slate-100">
        <input type="text" data-ss-text placeholder="{{ $searchPlaceholder }}"
               class="w-full rounded-md border border-slate-300 bg-white px-2 py-1.5 text-xs focus:border-emerald-600 focus:outline-none focus:ring-emerald-600" autocomplete="off" aria-label="ค้นหา">
      </div>
    @endif
    <ul id="{{ $listId }}" role="listbox" class="max-h-64 overflow-y-auto py-1 text-sm" data-ss-list aria-labelledby="{{ $id }}">
      @forelse($collection as $item)
        @php
          $val = data_get($item, $valueField);
          $label = data_get($item, $labelField)
            ?? data_get($item, 'display_name')
            ?? data_get($item, 'name')
            ?? (string) $val;
          $metaOut = [];
          foreach ((array) $metaFields as $mf) {
            $mv = data_get($item, $mf);
            if (!is_null($mv) && $mv !== '') $metaOut[] = $mv;
          }
        @endphp
        <li data-ss-option data-value="{{ $val }}" data-label="{{ $label }}"
            class="cursor-pointer px-3 py-2 hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none"
            role="option" aria-selected="{{ $selectedVal == $val ? 'true' : 'false' }}">
          <div class="flex flex-col">
            <span class="leading-5">{{ $label }}</span>
            @if(count($metaOut))
              <span class="mt-0.5 text-[11px] text-slate-500 leading-4">{{ implode(' • ', $metaOut) }}</span>
            @endif
          </div>
        </li>
      @empty
        <li class="px-3 py-2 text-slate-500">ไม่พบรายการ</li>
      @endforelse
    </ul>
    <div class="hidden px-3 py-2 text-slate-500" data-ss-empty>ไม่พบรายการที่ตรงกับการค้นหา</div>
  </div>
</div>

@push('scripts')
<script>
  // Fallback: ensure this instance is initialized even if the global setup ran earlier
  (function(){
    function tryInit(){
      if (window.initSearchSelects) { window.initSearchSelects(); return; }
      setTimeout(tryInit, 50);
    }
    tryInit();
  })();
</script>
@endpush
