{{-- resources/views/maintenance/requests/_form.blade.php --}}
@php
  /** @var \App\Models\MaintenanceRequest|null $req */
  $req        = $req ?? null;
  $assets     = is_iterable($assets ?? null) ? collect($assets) : collect();
  $depts      = is_iterable($depts ?? null) ? collect($depts) : collect();
  $attachments = is_iterable($attachments ?? null) ? $attachments : [];
@endphp

<div class="space-y-6">
  {{-- ===========================
       SECTION 1 : ข้อมูลหลัก
  ============================ --}}
  <section>
    <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
    <p class="text-sm text-slate-500">เลือกทรัพย์สิน ผู้แจ้ง และหน่วยงาน</p>

    {{-- แถวบน: ทรัพย์สิน + หน่วยงาน --}}
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- ทรัพย์สิน (Asset) - TomSelect + แว่นขยาย --}}
      @php $field = 'asset_id'; @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          ทรัพย์สิน (Asset) <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <div class="relative mt-1">
          {{-- ไอคอนแว่นขยาย --}}
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 z-10">
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none">
              <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"/>
              <path d="M16 16l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </span>

          <select id="{{ $field }}" name="{{ $field }}"
                  class="ts-basic ts-with-icon w-full @error($field) ts-error @enderror"
                  placeholder="— เลือกทรัพย์สิน —">
            <option value="">— เลือกทรัพย์สิน —</option>
            @foreach($assets as $a)
              @php
                $code  = $a->asset_code ?? '';
                $name  = $a->name ?? '';
                $label = trim(($code ? $code.' - ' : '').$name);
                $selectedVal = old($field, $req->asset_id ?? null);
              @endphp
              <option value="{{ $a->id }}" @selected($selectedVal == $a->id)>
                {{ $label ?: '—' }}
              </option>
            @endforeach
          </select>
        </div>
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- หน่วยงาน - TomSelect + แว่นขยาย --}}
      @php $field = 'department_id'; @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          หน่วยงาน <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <div class="relative mt-1">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 z-10 text-slate-400">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"></circle>
              <line x1="16" y1="16" x2="20" y2="20"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
            </svg>
          </span>
          <select id="{{ $field }}" name="{{ $field }}"
                  placeholder="— เลือกหน่วยงาน —"
                  class="ts-basic ts-with-icon w-full @error($field) ts-error @enderror">
            <option value="">— เลือกหน่วยงาน —</option>
            @php $selectedDept = old($field, $req->department_id ?? null); @endphp
            @foreach($depts as $d)
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

    {{-- แถวล่าง: ผู้แจ้ง + สถานที่ --}}
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- ผู้แจ้ง (Reporter) - ล็อกเป็นผู้ใช้ปัจจุบันหรือของ req --}}
      @php
        $field       = 'reporter_id';
        $currentUser = auth()->user();
        $reporterId  = old($field, $req->reporter_id ?? $currentUser?->id);
        $reporterName = optional($req->reporter ?? $currentUser)->name ?? 'ผู้ใช้งานปัจจุบัน';
      @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700">
          ผู้แจ้ง (Reporter) <span class="ml-1 text-xs text-slate-500">(ล็อกเป็นผู้ใช้งาน/ผู้แจ้งเดิม)</span>
        </label>
        <div class="mt-1 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">
          {{ $reporterName }}
        </div>
        <input type="hidden"
               id="{{ $field }}"
               name="{{ $field }}"
               value="{{ $reporterId }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- สถานที่/ตำแหน่งงาน --}}
      @php $field = 'location_text'; @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          สถานที่/ตำแหน่งงาน <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <input id="{{ $field }}" name="{{ $field }}" type="text"
               placeholder="เช่น อาคาร A ชั้น 3 ห้อง 302"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, $req->location_text ?? '') }}">
        @error($field)
          <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>
  </section>

  {{-- ===========================
       SECTION 2 : รายละเอียดปัญหา
  ============================ --}}
  <section class="pt-4 border-t border-slate-200">
    <h2 class="text-base font-semibold text-slate-900">รายละเอียดปัญหา</h2>
    <p class="text-sm text-slate-500">สรุปหัวข้อและอาการ เพื่อการคัดแยกที่รวดเร็ว</p>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- หัวข้อ --}}
      @php $field = 'title'; @endphp
      <div class="md:col-span-2">
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          หัวข้อ <span class="text-rose-600">*</span> <span class="ml-1 text-xs text-slate-500">(จำเป็น)</span>
        </label>
        <input id="{{ $field }}" name="{{ $field }}" type="text"
               required aria-required="true" autocomplete="off"
               placeholder="สรุปสั้น ๆ ชัดเจน (เช่น แอร์รั่วน้ำ ห้อง 302)"
               maxlength="150"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, $req->title ?? '') }}">
        <p class="mt-1 text-xs text-slate-500">ไม่เกิน 150 ตัวอักษร</p>
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- รายละเอียด --}}
      @php $field = 'description'; @endphp
      <div class="md:col-span-2">
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          รายละเอียด <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <textarea id="{{ $field }}" name="{{ $field }}" rows="5"
                  placeholder="อาการ เกิดเมื่อไร มีรูป/ลิงก์ประกอบ ฯลฯ"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                         focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">{{ old($field, $req->description ?? '') }}</textarea>
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>
  </section>

  {{-- ===========================
       SECTION 3 : ความสำคัญ
  ============================ --}}
  <section class="pt-4 border-t border-slate-200">
    <h2 class="text-base font-semibold text-slate-900">ความสำคัญ</h2>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- ระดับความสำคัญ --}}
      @php
        $field = 'priority';
        $priorities = ['low'=>'ต่ำ','medium'=>'ปานกลาง','high'=>'สูง','urgent'=>'ด่วน'];
        $selectedPriority = old($field, $req->priority ?? 'medium');
      @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          ระดับความสำคัญ <span class="text-rose-600">*</span> <span class="ml-1 text-xs text-slate-500">(จำเป็น)</span>
        </label>
        <select id="{{ $field }}" name="{{ $field }}" required aria-required="true"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                       focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror">
          @foreach($priorities as $k => $label)
            <option value="{{ $k }}" @selected($selectedPriority === $k)>{{ $label }}</option>
          @endforeach
        </select>
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- วันที่แจ้ง --}}
      @php $field = 'request_date'; @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          วันที่แจ้ง <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
        </label>
        <input id="{{ $field }}" name="{{ $field }}" type="date"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
               value="{{ old($field, optional($req->request_date ?? null)->format('Y-m-d')) }}">
        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>
  </section>

  {{-- ===========================
       SECTION 4 : ไฟล์แนบ (ใช้ได้ทั้ง Create + Edit)
  ============================ --}}
  <section class="pt-4 border-t border-slate-200">
    <h2 class="text-base font-semibold text-slate-900">ไฟล์แนบ</h2>
    <p class="text-sm text-slate-500">รองรับรูปภาพและ PDF (สูงสุดไฟล์ละ 10MB)</p>

    {{-- ไฟล์แนบเดิม (เฉพาะตอน Edit ที่ส่ง $attachments มา) --}}
    @if(count($attachments))
      <div class="mt-3 rounded-lg border border-slate-200 divide-y divide-slate-200">
        @foreach($attachments as $att)
          @php
            $f    = optional($att->file);
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
    @endif

    {{-- เพิ่มไฟล์ใหม่ --}}
    <div class="mt-4">
      @php $field = 'files'; @endphp
      <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
        เพิ่มไฟล์ (Images / PDF) <span class="ml-1 text-xs text-slate-500">(ไม่บังคับ)</span>
      </label>
      <input id="{{ $field }}" name="{{ $field }}[]" type="file" multiple
             accept="image/*,application/pdf"
             class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                    file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-slate-700
                    hover:file:bg-slate-200 focus:border-emerald-600 focus:ring-emerald-600 @error($field.'.*') border-rose-400 ring-rose-200 @enderror"
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
