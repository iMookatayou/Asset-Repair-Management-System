{{-- resources/views/assets/_fields.blade.php --}}
@php
  /** @var \App\Models\Asset|null $asset */
  $asset       = $asset ?? null;
  $categories  = is_iterable($categories ?? null) ? collect($categories) : collect();
  $departments = is_iterable($departments ?? null) ? collect($departments) : collect();
@endphp

<div class="space-y-6">
  {{-- ===========================
       SECTION 1 : ข้อมูลหลัก
  ============================ --}}
  <section>
    <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลักของครุภัณฑ์</h2>
    <p class="text-sm text-slate-500">รหัสครุภัณฑ์ ชื่อ และประเภท</p>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- รหัสครุภัณฑ์ --}}
      <div>
        <label class="block text-sm font-medium text-slate-700" for="asset_code">
          รหัสครุภัณฑ์ <span class="text-rose-600">*</span>
        </label>
        <input id="asset_code" name="asset_code" type="text"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error('asset_code') border-rose-400 ring-rose-200 @enderror"
               value="{{ old('asset_code', $asset->asset_code ?? '') }}" required>
        @error('asset_code')
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- ชื่อครุภัณฑ์ --}}
      <div>
        <label class="block text-sm font-medium text-slate-700" for="name">
          ชื่อครุภัณฑ์ <span class="text-rose-600">*</span>
        </label>
        <input id="name" name="name" type="text"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error('name') border-rose-400 ring-rose-200 @enderror"
               value="{{ old('name', $asset->name ?? '') }}" required>
        @error('name')
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- ประเภท (type) --}}
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700" for="type">
          ประเภท (Type) <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <input id="type" name="type" type="text"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error('type') border-rose-400 ring-rose-200 @enderror"
               value="{{ old('type', $asset->type ?? '') }}">
        @error('type')
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>
  </section>

  {{-- ===========================
       SECTION 2 : หมวดหมู่ & หน่วยงาน
  ============================ --}}
  <section class="pt-4 border-t border-slate-200">
    <h2 class="text-base font-semibold text-slate-900">หมวดหมู่ และหน่วยงานรับผิดชอบ</h2>
    <p class="text-sm text-slate-500">ใช้สำหรับจัดกลุ่ม และระบุหน่วยงานเจ้าของครุภัณฑ์</p>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- หมวดหมู่ (category_id) --}}
      @php $field = 'category_id'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">หมวดหมู่</label>
        <div class="relative mt-1">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 z-10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
              <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"/>
              <path d="M16 16l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </span>
          <select id="{{ $field }}" name="{{ $field }}"
                  class="ts-basic ts-with-icon w-full @error($field) ts-error @enderror"
                  placeholder="ค้นหา / เลือกหมวดหมู่">
            <option value="">— เลือกหมวดหมู่ —</option>
            @foreach($categories as $cat)
              @php $label = $cat->name ?? $cat->name_th ?? $cat->name_en ?? '—'; @endphp
              <option value="{{ $cat->id }}"
                      @selected(old($field, $asset->$field ?? null) == $cat->id)>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- หน่วยงาน (department_id) --}}
      @php $field = 'department_id'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">หน่วยงาน</label>
        <div class="relative mt-1">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 z-10">
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
            @php $selectedDept = old($field, $asset->$field ?? null); @endphp
            @foreach($departments as $d)
              @php
                $code  = $d->code ?? '';
                $name  = $d->name_th ?: $d->name_en ?: '';
                $label = trim(($code ? $code.' - ' : '').$name);
              @endphp
              <option value="{{ $d->id }}" @selected($selectedDept == $d->id)>
                {{ $label ?: '—' }}
              </option>
            @endforeach
          </select>
        </div>
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>
  </section>

  {{-- ===========================
       SECTION 3 : ข้อมูลเพิ่มเติม
  ============================ --}}
  <section class="pt-4 border-t border-slate-200">
    <h2 class="text-base font-semibold text-slate-900">ข้อมูลเพิ่มเติม</h2>
    <p class="text-sm text-slate-500">รายละเอียดด้านการจัดซื้อ และคุณลักษณะของครุภัณฑ์</p>

    {{-- วันที่ซื้อ + หมดประกัน --}}
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      @php $field = 'purchase_date'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">
          วันที่ซื้อ <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <input id="{{ $field }}" name="{{ $field }}" type="date"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, optional($asset->purchase_date ?? null)?->format('Y-m-d')) }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      @php $field = 'warranty_expire'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">
          หมดประกัน <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <input id="{{ $field }}" name="{{ $field }}" type="date"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, optional($asset->warranty_expire ?? null)?->format('Y-m-d')) }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>

    {{-- brand + model --}}
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      @php $field = 'brand'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">ยี่ห้อ</label>
        <input id="{{ $field }}" name="{{ $field }}" type="text"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, $asset->brand ?? '') }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      @php $field = 'model'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">รุ่น</label>
        <input id="{{ $field }}" name="{{ $field }}" type="text"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, $asset->model ?? '') }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>

    {{-- serial + location --}}
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      @php $field = 'serial_number'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">Serial</label>
        <input id="{{ $field }}" name="{{ $field }}" type="text"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, $asset->serial_number ?? '') }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      @php $field = 'location'; @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">ที่ตั้ง</label>
        <input id="{{ $field }}" name="{{ $field }}" type="text"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, $asset->location ?? '') }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>
  </section>

  {{-- ===========================
       SECTION 4 : สถานะ
  ============================ --}}
  <section class="pt-4 border-t border-slate-200">
    <h2 class="text-base font-semibold text-slate-900">สถานะของครุภัณฑ์</h2>
    <p class="text-sm text-slate-500">เลือกสถานะปัจจุบันของครุภัณฑ์ในระบบ</p>

    <div class="mt-4">
      @php
        $field = 'status';
        $statuses = ['active'=>'ใช้งาน','in_repair'=>'ซ่อม','disposed'=>'จำหน่าย'];
      @endphp
      <label class="block text-sm font-medium text-slate-700" for="{{ $field }}">สถานะ</label>
      <select id="{{ $field }}" name="{{ $field }}"
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                     focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">
        @foreach($statuses as $k => $label)
          <option value="{{ $k }}" @selected(old($field, $asset->status ?? 'active') === $k)>
            {{ $label }}
          </option>
        @endforeach
      </select>
      @error($field)
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
      @enderror
    </div>
  </section>
</div>
