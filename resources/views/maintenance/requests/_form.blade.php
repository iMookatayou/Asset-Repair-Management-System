@php
  /** @var \App\Models\MaintenanceRequest|null $req */
  $req = $req ?? null;

  $assets = is_iterable($assets ?? null) ? collect($assets) : collect();
  $depts  = is_iterable($depts  ?? null) ? collect($depts)  : collect();

  $user   = auth()->user();
  $isEdit = (bool) optional($req)->exists;

  $line = "border-slate-200";

  $input = "mt-2 w-full h-11 rounded-md border $line bg-white px-3 py-2 text-sm
            focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100";

  $textarea = "mt-2 w-full rounded-md border $line bg-white px-3 py-2 text-sm
              focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100";

  $headCls   = "flex items-start gap-3 pb-3 min-h-[56px]";
  $noCls     = "w-8 h-8 shrink-0 rounded-full border border-emerald-600 bg-emerald-600
                flex items-center justify-center text-sm font-bold text-white leading-none";
  $titleCls  = "text-base font-semibold text-slate-900 leading-tight";
  $subCls    = "text-sm text-slate-500 leading-snug";
  $accentWrap= "min-w-0 relative pl-3 pt-[1px]";
  $accentBar = "absolute left-0 top-[2px] w-[3px] h-9 rounded-full bg-emerald-600/90";

  $v = function ($key, $default = '') use ($req) {
        $old = old($key);
        if (!is_null($old)) return $old;

        $modelVal = data_get($req, $key, null);
        if (!is_null($modelVal)) return $modelVal;

        $queryVal = request()->query($key, null);
        if (!is_null($queryVal)) return $queryVal;

        return $default;
    };

@endphp

<div class="mx-auto max-w-screen-2xl px-3 sm:px-6 lg:px-8">
  <div class="space-y-10">

    <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-10">
      <div class="hidden lg:block absolute inset-y-0 left-1/2 w-px bg-slate-200"></div>

      <section>
        <div class="{{ $headCls }}">
          <div class="{{ $noCls }}">1</div>
          <div class="{{ $accentWrap }}">
            <span class="{{ $accentBar }}"></span>
            <div class="{{ $titleCls }}">ข้อมูลหลัก</div>
            <div class="{{ $subCls }}">ทรัพย์สิน / หน่วยงาน / สถานที่</div>
          </div>
        </div>

        <label class="block text-sm font-medium text-slate-700">ทรัพย์สิน</label>
        <select name="asset_id"
                class="ts-basic mt-2 w-full"
                data-placeholder="— เลือกทรัพย์สิน —">
          <option value="">— ไม่ระบุ —</option>
          @foreach($assets as $a)
            @php $label = trim(($a->asset_code ? $a->asset_code.' - ' : '').($a->name ?? '')); @endphp
            <option value="{{ $a->id }}" @selected((string)$v('asset_id') === (string)$a->id)>
              {{ $label ?: '—' }}
            </option>
          @endforeach
        </select>

        <label class="block text-sm font-medium text-slate-700 mt-4">หน่วยงาน</label>
        <select name="department_id"
                class="ts-basic mt-2 w-full"
                data-placeholder="— เลือกหน่วยงาน —">
          <option value="">— ไม่ระบุ —</option>
          @foreach($depts as $d)
            @php
              $deptName = $d->name_th ?: ($d->name_en ?? '');
              $label = trim(($d->code ? $d->code.' - ' : '').$deptName);
            @endphp
            <option value="{{ $d->id }}" @selected((string)$v('department_id') === (string)$d->id)>
              {{ $label ?: '—' }}
            </option>
          @endforeach
        </select>

        <label class="block text-sm font-medium text-slate-700 mt-4">สถานที่ / ตำแหน่งงาน</label>
        <input type="text"
               name="location_text"
               value="{{ $v('location_text') }}"
               autocomplete="off"
               class="{{ $input }}">
      </section>

      <section>
        <div class="{{ $headCls }}">
          <div class="{{ $noCls }}">2</div>
          <div class="{{ $accentWrap }}">
            <span class="{{ $accentBar }}"></span>
            <div class="{{ $titleCls }}">รายละเอียดปัญหา</div>
            <div class="{{ $subCls }}">หัวข้อและอาการเสีย</div>
          </div>
        </div>

        <label class="block text-sm font-medium text-slate-700">
          หัวข้อ <span class="text-rose-600">*</span>
        </label>
        <input type="text"
               name="title"
               value="{{ $v('title') }}"
               autocomplete="off"
               class="{{ $input }}"
               required>

        <label class="block text-sm font-medium text-slate-700 mt-4">รายละเอียด / อาการเสีย</label>
        <textarea name="description" rows="6" class="{{ $textarea }}">{{ $v('description') }}</textarea>
      </section>
    </div>

    <div class="border-t {{ $line }}"></div>

    <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-10">
      <div class="hidden lg:block absolute inset-y-0 left-1/2 w-px bg-slate-200"></div>

      <section>
        <div class="{{ $headCls }}">
          <div class="{{ $noCls }}">3</div>
          <div class="{{ $accentWrap }}">
            <span class="{{ $accentBar }}"></span>
            <div class="{{ $titleCls }}">ผู้แจ้ง &amp; ความสำคัญ</div>
            <div class="{{ $subCls }}">ข้อมูลผู้แจ้ง + ระดับความสำคัญ</div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
          @if($user)
            <div>
              <label class="block text-sm font-medium text-slate-700">ผู้แจ้ง</label>
              <div class="mt-2 h-11 rounded-md border {{ $line }} bg-slate-50 px-3 flex items-center text-sm text-slate-700">
                {{ $user->name }}
              </div>
              <input type="hidden" name="reporter_name" value="{{ $v('reporter_name', $user->name) }}">
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">เบอร์โทร (ถ้ามี)</label>
              <input type="text" name="reporter_phone" value="{{ $v('reporter_phone') }}" class="{{ $input }}">
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">อีเมล (ถ้ามี)</label>
              <input type="email" name="reporter_email" value="{{ $v('reporter_email', $user->email) }}" class="{{ $input }}">
            </div>
          @else
            <div>
              <label class="block text-sm font-medium text-slate-700">ชื่อผู้แจ้ง</label>
              <input type="text" name="reporter_name" value="{{ $v('reporter_name') }}" class="{{ $input }}">
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">เบอร์โทร</label>
              <input type="text" name="reporter_phone" value="{{ $v('reporter_phone') }}" class="{{ $input }}">
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">อีเมล</label>
              <input type="email" name="reporter_email" value="{{ $v('reporter_email') }}" class="{{ $input }}">
            </div>
          @endif
        </div>

        <label class="block text-sm font-medium text-slate-700 mt-4">
          ระดับความสำคัญ <span class="text-rose-600">*</span>
        </label>
        @php $priority = $v('priority', 'medium'); @endphp
        <select name="priority" class="{{ $input }}" required>
          <option value="low"    @selected($priority === 'low')>ต่ำ</option>
          <option value="medium" @selected($priority === 'medium')>ปานกลาง</option>
          <option value="high"   @selected($priority === 'high')>สูง</option>
          <option value="urgent" @selected($priority === 'urgent')>เร่งด่วน</option>
        </select>
      </section>

      <section>
        <div class="{{ $headCls }}">
          <div class="{{ $noCls }}">4</div>
          <div class="{{ $accentWrap }}">
            <span class="{{ $accentBar }}"></span>
            <div class="{{ $titleCls }}">ไฟล์แนบ</div>
            <div class="{{ $subCls }}">รูป / เอกสารประกอบ</div>
          </div>
        </div>

        @if(!$isEdit)
          <div class="rounded-md border {{ $line }} bg-slate-50 px-3 py-2 text-sm text-slate-700">
            แนบไฟล์ได้หลังจาก “สร้างคำขอ” แล้ว (ในหน้าแก้ไข/รายละเอียด)
          </div>
        @else
          @php $attachments = is_iterable($attachments ?? null) ? $attachments : []; @endphp

          @if(!empty($attachments) && count($attachments))
            <div class="mb-4">
              <div class="text-xs font-medium text-slate-600">ไฟล์ที่มีอยู่แล้ว</div>
              <div class="mt-2 divide-y divide-slate-200 rounded-md border {{ $line }}">
                @foreach($attachments as $att)
                  <label class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                    <span class="truncate text-slate-700">{{ $att->original_name }}</span>
                    <span class="inline-flex items-center gap-2 text-rose-600">
                      <input type="checkbox" name="remove_attachments[]" value="{{ $att->id }}">
                      ลบ
                    </span>
                  </label>
                @endforeach
              </div>
            </div>
          @endif

          <input type="file"
                 name="files[]"
                 multiple
                 accept="image/*,application/pdf"
                 class="block w-full rounded-md border {{ $line }} bg-white px-3 py-2 text-sm">
          <p class="mt-1 text-xs text-slate-500">รองรับรูปภาพ และ PDF</p>
        @endif
      </section>
    </div>

  </div>
</div>
