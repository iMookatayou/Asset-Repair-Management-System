@extends('layouts.app')
@section('title','Create Maintenance')

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create Maintenance
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            สร้างคำขอซ่อมใหม่ — ระบุทรัพย์สิน หัวข้อ และรายละเอียดให้ครบถ้วน
          </p>
        </div>

        <a href="{{ route('maintenance.requests.index') }}"
           class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back
        </a>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Client-side assist: warn user immediately if required fields are missing
  (function(){
    const form = document.querySelector('form[aria-label="แบบฟอร์มสร้างคำขอซ่อม"]');
    if (!form) return;
    form.addEventListener('submit', function(e){
      const reqs = [
        { id: 'title', label: 'หัวข้อ' },
        { id: 'priority', label: 'ระดับความสำคัญ' }
      ];
      const missing = [];
      for (const r of reqs){
        const el = document.getElementById(r.id);
        const val = (el && (el.value ?? '').toString().trim());
        if (!val) missing.push(r.label);
      }
      if (missing.length){
        e.preventDefault(); // ป้องกัน POST และแจ้งเตือนแบบไม่ trigger global loader
        const msg = 'กรุณากรอกให้ครบ: ' + missing.join(', ') + ' • ช่องอื่นๆ เช่น รายละเอียด/ไฟล์แนบ ไม่บังคับ';
        if (window.showToast) window.showToast({ type:'warning', message: msg, position:'uc', timeout: 2600, size:'lg' });
        try{ reqs.find(r => missing.includes(r.label)) && document.getElementById(reqs.find(r => missing.includes(r.label)).id)?.focus(); }catch(_){ }
      }
    });
  })();
</script>
@endpush

@section('content')
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <p class="font-medium">มีข้อผิดพลาดในการบันทึกข้อมูล:</p>
        <ul class="mt-2 list-disc pl-5 text-sm">
          @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ route('maintenance.requests.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
          novalidate
          aria-label="แบบฟอร์มสร้างคำขอซ่อม">
      @csrf

      <div class="space-y-6">
        <section>
          <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
          <p class="text-sm text-slate-500">เลือกทรัพย์สิน และ (ถ้าจำเป็น) ผู้แจ้ง</p>

          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @php
              $field='asset_id';
              $assetList = is_iterable($assets ?? null) ? collect($assets) : collect();
              $assetItems = $assetList->map(function($a){
                $code = $a->asset_code ?? '';
                $name = $a->name ?? '';
                $label = trim(($code ? ($code.' - ') : '').$name);
                return ['id' => $a->id, 'display_name' => $label ?: ($code ?: $name ?: '—')];
              });
            @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                ทรัพย์สิน <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
              </label>
              <x-dynamic-search-dropdown
                name="asset_id"
                :endpoint="url('/api/search/assets')"
                label-field="name"
                value-field="id"
                placeholder="— เลือกทรัพย์สิน —"
                search-placeholder="พิมพ์เพื่อค้นหา..." />
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            @php
              $field='department_id';
              $deptList = is_iterable($depts ?? null) ? collect($depts) : collect();
              $deptItems = $deptList->map(function($d){
                $code = $d->code ?? '';
                $th   = $d->name_th ?? '';
                $en   = $d->name_en ?? '';
                $name = $th ?: $en ?: '';
                $label = trim(($code ? ($code.' - ') : '').$name);
                return ['id' => $d->id, 'display_name' => $label ?: ($code ?: $name ?: '—')];
              });
            @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                หน่วยงาน <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
              </label>
              <x-dynamic-search-dropdown
                name="department_id"
                :endpoint="url('/api/meta/departments')"
                label-field="display"
                value-field="id"
                placeholder="— เลือกหน่วยงาน —"
                search-placeholder="พิมพ์เพื่อค้นหา..." />
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
          <div class="mt-4">
            @php $field='location_text'; @endphp
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">สถานที่ / ตำแหน่ง <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span></label>
            <input id="{{ $field }}" name="{{ $field }}" type="text"
                   placeholder="เช่น อาคาร A ชั้น 3 ห้อง 302"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                   value="{{ old($field) }}">
            @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>
        </section>

        <section class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">รายละเอียดปัญหา</h2>
          <p class="text-sm text-slate-500">สรุปหัวข้อและอาการ เพื่อการคัดแยกที่รวดเร็ว</p>

          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @php $field='title'; @endphp
            <div class="md:col-span-2">
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                หัวข้อ <span class="text-rose-600">*</span> <span class="ml-1 text-xs text-slate-500">(จำเป็น)</span>
              </label>
              <input id="{{ $field }}" name="{{ $field }}" type="text" required aria-required="true" autocomplete="off"
                     placeholder="สรุปสั้น ๆ ชัดเจน (เช่น แอร์รั่วน้ำ ห้อง 302)"
                     maxlength="150"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                     value="{{ old($field) }}">
              <p class="mt-1 text-xs text-slate-500">ไม่เกิน 150 ตัวอักษร</p>
              @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            @php $field='description'; @endphp
            <div class="md:col-span-2">
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">รายละเอียด <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span></label>
              <textarea id="{{ $field }}" name="{{ $field }}" rows="5"
                        placeholder="อาการ เกิดเมื่อไร มีรูป/ลิงก์ประกอบ ฯลฯ"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">{{ old($field) }}</textarea>
              @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </section>

        <section class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">ความสำคัญ</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @php
              $field='priority';
              $priorities=['low'=>'ต่ำ','medium'=>'ปานกลาง','high'=>'สูง','urgent'=>'ด่วน'];
            @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                ระดับความสำคัญ <span class="text-rose-600">*</span> <span class="ml-1 text-xs text-slate-500">(จำเป็น)</span>
              </label>
              <select id="{{ $field }}" name="{{ $field }}" required aria-required="true"
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">
                @foreach($priorities as $k=>$label)
                  <option value="{{ $k }}" @selected(old($field, 'medium') === $k)>{{ $label }}</option>
                @endforeach
              </select>
              @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            @php $field='request_date'; @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                วันที่แจ้ง <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
              </label>
              <input id="{{ $field }}" name="{{ $field }}" type="date"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                     value="{{ old($field) }}">
              @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </section>

        <section class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">ไฟล์แนบ</h2>
          <p class="text-sm text-slate-500">รองรับรูปภาพและ PDF (สูงสุดไฟล์ละ 10MB)</p>

          <div class="mt-3">
            @php $field='files'; @endphp
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
              เลือกไฟล์ (Images / PDF) <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
            </label>
            <input id="{{ $field }}" name="{{ $field }}[]"
                   type="file" multiple
                   accept="image/*,application/pdf"
                   class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-slate-700 hover:file:bg-slate-200 focus:border-emerald-600 focus:ring-emerald-600 @error($field.'.*') border-rose-400 ring-rose-200 @enderror"
                   aria-describedby="{{ $field }}_help">
            <p id="{{ $field }}_help" class="mt-1 text-xs text-slate-500">
              ประเภทที่อนุญาต: รูปภาพทุกชนิด, PDF • ขนาดไม่เกิน 10MB ต่อไฟล์
            </p>
            @error($field.'.*')
              <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
          </div>
        </section>
      </div>
      <div class="mt-6 flex justify-end gap-2">
        <a href="{{ route('maintenance.requests.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          ยกเลิก
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          บันทึก
        </button>
      </div>
    </form>
  </div>
@endsection
