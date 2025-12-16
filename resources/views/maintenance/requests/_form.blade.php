@php
  /** @var \App\Models\MaintenanceRequest|null $req */
  $req    = $req ?? null;
  $assets = is_iterable($assets ?? null) ? collect($assets) : collect();
  $depts  = is_iterable($depts ?? null) ? collect($depts) : collect();
  $attachments = is_iterable($attachments ?? null) ? $attachments : [];

  $user = auth()->user();
@endphp

<div class="space-y-8">

  {{-- ===========================
       SECTION 1 : ข้อมูลหลัก
  ============================ --}}
  <section class="space-y-4">
    <div>
      <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
      <p class="text-sm text-slate-500">ทรัพย์สิน หน่วยงาน ผู้แจ้ง และสถานที่</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- ทรัพย์สิน --}}
      @php $field = 'asset_id'; @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          ทรัพย์สิน (Asset)
        </label>
        <p class="mt-0.5 text-xs text-slate-500">ถ้าไม่ระบุ ระบบจะบันทึกเป็นงานซ่อมทั่วไป</p>

        <select id="{{ $field }}" name="{{ $field }}"
                class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                       focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 @enderror">
          <option value="">— ไม่ระบุ —</option>
          @foreach($assets as $a)
            @php
              $label = trim(($a->asset_code ? $a->asset_code.' - ' : '').($a->name ?? ''));
            @endphp
            <option value="{{ $a->id }}" @selected(old($field, $req->asset_id ?? null) == $a->id)>
              {{ $label ?: '—' }}
            </option>
          @endforeach
        </select>

        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- หน่วยงาน --}}
      @php $field = 'department_id'; @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          หน่วยงาน
        </label>
        <p class="mt-0.5 text-xs text-slate-500">เลือกหน่วยงานที่เกี่ยวข้องกับทรัพย์สิน/ผู้ใช้งาน</p>

        <select id="{{ $field }}" name="{{ $field }}"
                class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                       focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 @enderror">
          <option value="">— ไม่ระบุ —</option>
          @foreach($depts as $d)
            @php
              $deptName = $d->name_th ?: $d->name_en ?: '';
              $label = trim(($d->code ? $d->code.' - ' : '').$deptName);
            @endphp
            <option value="{{ $d->id }}" @selected(old($field, $req->department_id ?? null) == $d->id)>
              {{ $label ?: '—' }}
            </option>
          @endforeach
        </select>

        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      {{-- ผู้แจ้ง (lock) --}}
      @php
        $field = 'reporter_id';
        $reporterId = old($field, $req->reporter_id ?? $user?->id);
        $reporterName = $req?->reporter?->name ?? $user?->name ?? '-';
      @endphp
      <div>
        <label class="block text-sm font-medium text-slate-700">ผู้แจ้ง</label>
        <p class="mt-0.5 text-xs text-slate-500">ระบบจะล็อกผู้แจ้งตามผู้ใช้งานที่เข้าสู่ระบบ</p>

        <div class="mt-2 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
          {{ $reporterName }}
        </div>
        <input type="hidden" name="{{ $field }}" value="{{ $reporterId }}">
      </div>

      {{-- สถานที่ --}}
      @php $field = 'location_text'; @endphp
      <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
          สถานที่ / ตำแหน่งงาน
        </label>
        <p class="mt-0.5 text-xs text-slate-500">เช่น ห้อง 203 / ตึก IT / แผนกการเงิน</p>

        <input id="{{ $field }}" name="{{ $field }}" type="text"
               value="{{ old($field, $req->location_text ?? '') }}"
               class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                      focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 @enderror">

        @error($field)
          <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
      </div>
    </div>
  </section>

  <hr class="border-slate-200">

  {{-- ===========================
       SECTION 2 : รายละเอียดปัญหา
  ============================ --}}
  <section class="space-y-4">
    <div>
      <h2 class="text-base font-semibold text-slate-900">รายละเอียดปัญหา</h2>
      <p class="text-sm text-slate-500">หัวข้อและรายละเอียดอาการเสีย (หัวข้อจำเป็นต้องกรอก)</p>
    </div>

    @php $field = 'title'; @endphp
    <div>
      <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
        หัวข้อ <span class="text-rose-600">*</span>
      </label>
      <p class="mt-0.5 text-xs text-slate-500">สั้น กระชับ เช่น “คอมเปิดไม่ติด / Printer กระดาษติด”</p>

      <input id="{{ $field }}" name="{{ $field }}" type="text" required
             value="{{ old($field, $req->title ?? '') }}"
             class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                    focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 @enderror">

      @error($field)
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
      @enderror
    </div>

    @php $field = 'description'; @endphp
    <div>
      <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
        รายละเอียด / อาการเสีย
      </label>
      <p class="mt-0.5 text-xs text-slate-500">อธิบายอาการ จุดเกิดเหตุ และสิ่งที่ลองทำแล้ว</p>

      <textarea id="{{ $field }}" name="{{ $field }}" rows="5"
                class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                       focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 @enderror"
      >{{ old($field, $req->description ?? '') }}</textarea>

      @error($field)
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
      @enderror
    </div>
  </section>

  <hr class="border-slate-200">

  {{-- ===========================
       SECTION 3 : ความสำคัญ
  ============================ --}}
  <section class="space-y-3">
    <div>
      <h2 class="text-base font-semibold text-slate-900">ความสำคัญ</h2>
      <p class="text-sm text-slate-500">ช่วยจัดลำดับคิวงานได้ถูกต้อง</p>
    </div>

    @php
      $field = 'priority';
      $priorities = ['low'=>'ต่ำ','medium'=>'ปานกลาง','high'=>'สูง','urgent'=>'เร่งด่วน'];
      $selected = old($field, $req->priority ?? 'medium');
    @endphp

    <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
      ระดับความสำคัญ <span class="text-rose-600">*</span>
    </label>

    <select id="{{ $field }}" name="{{ $field }}" required
            class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                   focus:border-emerald-600 focus:ring-emerald-600">
      @foreach($priorities as $k=>$label)
        <option value="{{ $k }}" @selected($selected === $k)>{{ $label }}</option>
      @endforeach
    </select>
  </section>

  <hr class="border-slate-200">

  {{-- ===========================
       SECTION 4 : ไฟล์แนบ
  ============================ --}}
  <section class="space-y-3">
    <div>
      <h2 class="text-base font-semibold text-slate-900">ไฟล์แนบ</h2>
      <p class="text-sm text-slate-500">แนบรูป/เอกสารประกอบ (ถ้ามี)</p>
    </div>

    @if(count($attachments))
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-2">
        <div class="text-xs font-medium text-slate-600">ไฟล์ที่มีอยู่แล้ว</div>
        @foreach($attachments as $att)
          <label class="flex items-center justify-between gap-2 text-sm">
            <span class="truncate text-slate-700">{{ $att->original_name }}</span>
            <span class="inline-flex items-center gap-2 text-rose-600">
              <input type="checkbox" name="remove_attachments[]" value="{{ $att->id }}">
              ลบ
            </span>
          </label>
        @endforeach
      </div>
    @endif

    <div>
      <label class="block text-sm font-medium text-slate-700">อัปโหลดไฟล์ใหม่</label>
      <p class="mt-0.5 text-xs text-slate-500">รองรับรูปภาพและ PDF</p>
      <input type="file" name="files[]" multiple accept="image/*,application/pdf"
             class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
  </section>

</div>
