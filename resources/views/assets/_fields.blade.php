@php /** @var \App\Models\Asset|null $asset */ @endphp

<div class="grid gap-4">
  {{-- รหัสครุภัณฑ์ --}}
  <div>
    <label class="block text-sm font-medium text-slate-700" for="asset_code">รหัสครุภัณฑ์</label>
    <input id="asset_code" name="asset_code" type="text"
           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
           value="{{ old('asset_code', $asset->asset_code ?? '') }}" required>
    @error('asset_code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
  </div>

  {{-- ชื่อครุภัณฑ์ --}}
  <div>
    <label class="block text-sm font-medium text-slate-700" for="name">ชื่อ</label>
    <input id="name" name="name" type="text"
           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
           value="{{ old('name', $asset->name ?? '') }}" required>
    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
  </div>

  {{-- type + ช่องหลบ legacy --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-slate-700" for="type">ประเภท (type)</label>
      <input id="type" name="type" type="text"
             class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
             value="{{ old('type', $asset->type ?? '') }}">
      @error('type') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div class="hidden">
      <input type="text" disabled aria-hidden="true">
    </div>
  </div>

  {{-- หมวดหมู่ (category_id) – แบบเดียวกับ Maintenance --}}
  @isset($categories)
    @php $field = 'category_id'; @endphp
    <div>
      <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">หมวดหมู่</label>
      <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 z-10 text-slate-400">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"></circle>
            <path d="M16 16l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
          </svg>
        </span>

        <select id="{{ $field }}" name="{{ $field }}"
                class="ts-basic ts-with-icon w-full @error($field) ts-error @enderror"
                placeholder="ค้นหา / เลือกหมวดหมู่">
          <option value="">— เลือกหมวดหมู่ —</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}"
              @selected(old($field, $asset->$field ?? null) == $cat->id)>
              {{ $cat->name ?? $cat->name_th ?? $cat->name_en ?? '—' }}
            </option>
          @endforeach
        </select>
      </div>
      @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
  @endisset

  {{-- หน่วยงาน (department_id) – แบบเดียวกับ Maintenance --}}
  @isset($departments)
    @php $field = 'department_id'; @endphp
    <div>
      <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">หน่วยงาน</label>
      <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 z-10 text-slate-400">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"></circle>
            <line x1="16" y1="16" x2="20" y2="20"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
          </svg>
        </span>

        <select id="{{ $field }}" name="{{ $field }}"
                class="ts-basic ts-with-icon w-full @error($field) ts-error @enderror"
                placeholder="ค้นหา / เลือกหน่วยงาน">
          <option value="">— เลือกหน่วยงาน —</option>
          @foreach($departments as $d)
            @php
              $code  = $d->code ?? '';
              $name  = $d->name_th ?: $d->name_en ?: '';
              $label = trim(($code ? $code.' - ' : '').$name);
            @endphp
            <option value="{{ $d->id }}"
              @selected(old($field, $asset->$field ?? null) == $d->id)>
              {{ $label ?: '—' }}
            </option>
          @endforeach
        </select>
      </div>
      @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
  @endisset

  {{-- วันที่ซื้อ + หมดประกัน --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-slate-700" for="purchase_date">วันที่ซื้อ</label>
      <input id="purchase_date" name="purchase_date" type="date"
             class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
             value="{{ old('purchase_date', optional($asset->purchase_date ?? null)?->format('Y-m-d')) }}">
      @error('purchase_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700" for="warranty_expire">หมดประกัน</label>
      <input id="warranty_expire" name="warranty_expire" type="date"
             class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
             value="{{ old('warranty_expire', optional($asset->warranty_expire ?? null)?->format('Y-m-d')) }}">
      @error('warranty_expire') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
  </div>

  {{-- brand + model --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-slate-700" for="brand">ยี่ห้อ</label>
      <input id="brand" name="brand" type="text"
             class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
             value="{{ old('brand', $asset->brand ?? '') }}">
      @error('brand') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700" for="model">รุ่น</label>
      <input id="model" name="model" type="text"
             class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
             value="{{ old('model', $asset->model ?? '') }}">
      @error('model') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
  </div>

  {{-- serial + location --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-slate-700" for="serial_number">Serial</label>
      <input id="serial_number" name="serial_number" type="text"
             class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
             value="{{ old('serial_number', $asset->serial_number ?? '') }}">
      @error('serial_number') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700" for="location">ที่ตั้ง</label>
      <input id="location" name="location" type="text"
             class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
             value="{{ old('location', $asset->location ?? '') }}">
      @error('location') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
  </div>

  {{-- สถานะ --}}
  <div>
    <label class="block text-sm font-medium text-slate-700" for="status">สถานะ</label>
    <select id="status" name="status"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600">
      @php $statuses = ['active'=>'ใช้งาน','in_repair'=>'ซ่อม','disposed'=>'จำหน่าย']; @endphp
      @foreach($statuses as $k=>$label)
        <option value="{{ $k }}" @selected(old('status', $asset->status ?? 'active') === $k)>
          {{ $label }}
        </option>
      @endforeach
    </select>
    @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
  </div>
</div>
