@extends('layouts.app')
@section('title','Edit Maintenance')

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Edit Maintenance
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            แก้ไขคำขอซ่อม — ปรับข้อมูลให้ถูกต้องและบันทึกการเปลี่ยนแปลง
          </p>
        </div>

        <div class="flex items-center gap-2">
          <a href="{{ route('maintenance.requests.show', $mr) }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back
          </a>
          <a href="{{ route('maintenance.requests.index') }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            List
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

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
          action="{{ route('maintenance.requests.update', $mr) }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
          novalidate
          aria-label="แบบฟอร์มแก้ไขคำขอซ่อม">
      @csrf
      @method('PUT')

      <div class="space-y-6">
        <section>
          <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
          <p class="text-sm text-slate-500">เลือกทรัพย์สิน และ (ถ้าจำเป็น) ผู้แจ้ง</p>

          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

            @php $field='asset_id'; $assetList = is_iterable($assets ?? null) ? collect($assets) : collect(); @endphp
              @php
                $field='asset_id';
                $assetList = is_iterable($assets ?? null) ? collect($assets) : collect();
                $assetItems = $assetList->map(function($a){
                  $code = $a->asset_code ?? '';
                  $name = $a->name ?? '';
                  $label = trim(($code ? ($code.' - ') : '').$name);
                  return ['id' => $a->id, 'display_name' => $label ?: ($code ?: $name)];
                });
              @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                ทรัพย์สิน
              </label>
              @php
                $initialAsset = $assetList->firstWhere('id', old('asset_id', $mr->asset_id));
                $initialAssetLabel = $initialAsset['display_name'] ?? null;
              @endphp
              <x-dynamic-search-dropdown
                name="asset_id"
                :endpoint="url('/api/search/assets')"
                label-field="name"
                value-field="id"
                :value="old('asset_id', $mr->asset_id)"
                :value-label="$initialAssetLabel"
                placeholder="— เลือกทรัพย์สิน —"
                search-placeholder="พิมพ์เพื่อค้นหา..." />
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            @php $field='department_id'; $deptList = is_iterable($depts ?? null) ? collect($depts) : collect(); @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                หน่วยงาน <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
              </label>
              @php
                // Build display labels for departments: CODE - ThaiName (or English)
                $deptItems = $deptList->map(function($d){
                  $code = $d->code ?? '';
                  $th   = $d->name_th ?? '';
                  $en   = $d->name_en ?? '';
                  $name = $th ?: $en ?: '';
                  $label = trim(($code ? ($code.' - ') : '').$name);
                  return ['id' => $d->id, 'display_name' => $label ?: ($code ?: $name ?: '—')];
                });
              @endphp
              @php
                $initialDept = $deptItems->firstWhere('id', old('department_id', $mr->department_id));
                $initialDeptLabel = $initialDept['display_name'] ?? null;
              @endphp
              <x-dynamic-search-dropdown
                name="department_id"
                :endpoint="url('/api/meta/departments')"
                label-field="display"
                value-field="id"
                :value="old('department_id', $mr->department_id)"
                :value-label="$initialDeptLabel"
                placeholder="— เลือกหน่วยงาน —"
                search-placeholder="พิมพ์เพื่อค้นหา..." />
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @php $field='location_text'; @endphp
            <div class="md:col-span-2">
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">สถานที่ / ตำแหน่ง <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span></label>
              <input id="{{ $field }}" name="{{ $field }}" type="text"
                     placeholder="เช่น อาคาร A ชั้น 3 ห้อง 302"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                     value="{{ old($field, $mr->location_text) }}">
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
          </div>
        </section>

        <section class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">รายละเอียดปัญหา</h2>
          <p class="text-sm text-slate-500">สรุปหัวข้อและอาการ เพื่อการคัดแยกที่รวดเร็ว</p>

          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @php $field='title'; @endphp
            <div class="md:col-span-2">
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                หัวข้อ <span class="text-rose-600">*</span>
              </label>
              <input id="{{ $field }}" name="{{ $field }}" type="text" required autocomplete="off"
                     placeholder="สรุปสั้น ๆ ชัดเจน (เช่น แอร์รั่วน้ำ ห้อง 302)"
                     maxlength="150"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                     value="{{ old($field, $mr->title) }}">
              <p class="mt-1 text-xs text-slate-500">ไม่เกิน 150 ตัวอักษร</p>
              @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            @php $field='description'; @endphp
            <div class="md:col-span-2">
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">รายละเอียด <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span></label>
              <textarea id="{{ $field }}" name="{{ $field }}" rows="5"
                        placeholder="อาการ เกิดเมื่อไร มีรูป/ลิงก์ประกอบ ฯลฯ"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">{{ old($field, $mr->description) }}</textarea>
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
                ระดับความสำคัญ <span class="text-rose-600">*</span>
              </label>
              <select id="{{ $field }}" name="{{ $field }}" required
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">
                @foreach($priorities as $k=>$label)
                  <option value="{{ $k }}" @selected(old($field, $mr->priority ?? 'medium') === $k)>{{ $label }}</option>
                @endforeach
              </select>
              @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            @php $field='request_date'; @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                วันที่แจ้ง
              </label>
              <input id="{{ $field }}" name="{{ $field }}" type="date"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                     value="{{ old($field, optional($mr->request_date)->format('Y-m-d')) }}">
              @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </section>

        <section class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">ไฟล์แนบ</h2>
          <p class="text-sm text-slate-500">ดู/ลบไฟล์แนบเดิม และอัปโหลดไฟล์ใหม่ (สูงสุดไฟล์ละ 10MB)</p>

          @php $existing = is_iterable($attachments ?? null) ? $attachments : []; @endphp
          @if (count($existing))
            <div class="mt-3 rounded-lg border border-slate-200 divide-y divide-slate-200">
              @foreach($existing as $att)
                @php
                  $f = optional($att->file);
                  $path = $f->path ?? '';
                  $mime = $f->mime ?? 'file';
                  $size = $f->size ?? null;
                @endphp
                <div class="flex items-center justify-between px-3 py-2">
                  <div class="min-w-0">
                    <p class="truncate text-sm text-slate-800">
                      {{ $att->original_name ?? basename($path) }}
                    </p>
                    <p class="text-xs text-slate-500">
                      {{ $mime }} @if($size) • {{ number_format($size/1024, 0) }} KB @endif
                    </p>
                  </div>
                  <label class="inline-flex items-center gap-2 text-sm text-rose-700">
                    <input type="checkbox" name="remove_attachments[]"
                           value="{{ $att->id }}"
                           class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-600">
                    ลบไฟล์นี้
                  </label>
                </div>
              @endforeach
            </div>
          @else
            <p class="mt-2 text-sm text-slate-500">ยังไม่มีไฟล์แนบ</p>
          @endif

          <div class="mt-4">
            @php $field='files'; @endphp
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
              เพิ่มไฟล์ (Images / PDF)
            </label>
            <input id="{{ $field }}" name="{{ $field }}[]" type="file" multiple
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

      <section class="pt-4 border-t border-slate-200 mt-6">
        <h2 class="text-base font-semibold text-slate-900">ผลการซ่อมและค่าใช้จ่าย</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
          @php $field='resolution_note'; @endphp
          <div class="md:col-span-2">
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">ผลการซ่อม <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span></label>
            <textarea id="{{ $field }}" name="{{ $field }}" rows="3"
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">{{ old($field, $mr->resolution_note) }}</textarea>
            @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          @php $field='cost'; @endphp
          <div>
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">ค่าใช้จ่าย (บาท) <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span></label>
            <input id="{{ $field }}" name="{{ $field }}" type="number" step="0.01" min="0"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                   value="{{ old($field, $mr->cost) }}">
            @error($field) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>
        </div>
      </section>

      <div class="mt-6 flex justify-end gap-2">
        <a href="{{ route('maintenance.requests.show', $mr) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          ยกเลิก
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          บันทึกการแก้ไข
        </button>
      </div>
    </form>
  </div>
@endsection
