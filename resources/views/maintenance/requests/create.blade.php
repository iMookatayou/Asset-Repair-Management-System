{{-- resources/views/maintenance/requests/create.blade.php --}}
@extends('layouts.app')
@section('title','Create Maintenance')

@section('page-header')
  {{-- Header โทนอ่อน + ไอคอน + ปุ่ม Back (เหมือนหน้า Asset) --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ไอคอน + --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create Maintenance
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            สร้างคำขอซ่อมใหม่ — โปรดระบุทรัพย์สิน ปัญหา และผู้ติดต่อให้ครบถ้วน
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

@section('content')
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

    {{-- Error summary (เหมือนหน้า Asset) --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <p class="font-medium">มีข้อผิดพลาดในการบันทึกข้อมูล:</p>
        <ul class="mt-2 list-disc pl-5 text-sm">
          @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- Form wrapper: การ์ดขาว ขนาดเดียวกับ Asset --}}
    <form method="POST" action="{{ route('maintenance.requests.store') }}"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" novalidate
          aria-label="แบบฟอร์มสร้างคำขอซ่อม">
      @csrf

      <div class="space-y-6">
        {{-- ===== ข้อมูลหลัก ===== --}}
        <div>
          <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
          <p class="text-sm text-slate-500">เลือกทรัพย์สิน และข้อมูลผู้แจ้ง</p>

          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- ทรัพย์สิน --}}
            @php $field='asset_id'; @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                ทรัพย์สิน <span class="text-rose-600">*</span>
              </label>
              <select id="{{ $field }}" name="{{ $field }}" required
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                      aria-invalid="@error($field) true @else false @enderror"
                      @error($field) aria-describedby="{{ $field }}_error" @enderror>
                <option value="">— เลือกทรัพย์สิน —</option>
                @foreach($assets as $a)
                  <option value="{{ $a->id }}" @selected(old($field) == $a->id)>
                    {{ $a->code ?? '—' }} — {{ $a->name }}
                  </option>
                @endforeach
              </select>
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- ผู้แจ้ง --}}
            @php $field='reporter_id'; @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                ผู้แจ้ง <span class="text-rose-600">*</span>
              </label>
              <select id="{{ $field }}" name="{{ $field }}" required
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                      aria-invalid="@error($field) true @else false @enderror"
                      @error($field) aria-describedby="{{ $field }}_error" @enderror>
                <option value="">— เลือกผู้แจ้ง —</option>
                @foreach($users as $u)
                  <option value="{{ $u->id }}" @selected(old($field, auth()->id()) == $u->id)>
                    {{ $u->name }}
                  </option>
                @endforeach
              </select>
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        {{-- ===== รายละเอียดปัญหา ===== --}}
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">รายละเอียดปัญหา</h2>
          <p class="text-sm text-slate-500">สรุปหัวข้อและอธิบายอาการ เพื่อการคัดแยกที่รวดเร็ว</p>

          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- หัวข้อ --}}
            @php $field='title'; @endphp
            <div class="md:col-span-2">
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                หัวข้อ <span class="text-rose-600">*</span>
              </label>
              <input id="{{ $field }}" name="{{ $field }}" type="text" required autocomplete="off"
                     placeholder="สรุปสั้น ๆ ชัดเจน (เช่น แอร์รั่วน้ำ ห้อง 302)"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                     value="{{ old($field) }}"
                     aria-invalid="@error($field) true @else false @enderror"
                     @error($field) aria-describedby="{{ $field }}_error" @enderror>
              <p class="mt-1 text-xs text-slate-500">ไม่เกิน 150 ตัวอักษร</p>
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- รายละเอียด --}}
            @php $field='description'; @endphp
            <div class="md:col-span-2">
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">รายละเอียด</label>
              <textarea id="{{ $field }}" name="{{ $field }}" rows="5"
                        placeholder="ใส่รายละเอียด (อาการ เกิดเมื่อไร มีรูป/ลิงก์ประกอบ ฯลฯ)"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                        aria-invalid="@error($field) true @else false @enderror"
                        @error($field) aria-describedby="{{ $field }}_error" @enderror>{{ old($field) }}</textarea>
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        {{-- ===== ความสำคัญ ===== --}}
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">ความสำคัญ</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @php $field='priority'; $priorities=['low'=>'ต่ำ','medium'=>'ปานกลาง','high'=>'สูง','urgent'=>'ด่วน']; @endphp
            <div>
              <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                ระดับความสำคัญ <span class="text-rose-600">*</span>
              </label>
              <select id="{{ $field }}" name="{{ $field }}" required
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600 @error($field) border-rose-400 ring-rose-200 @enderror"
                      aria-invalid="@error($field) true @else false @enderror"
                      @error($field) aria-describedby="{{ $field }}_error" @enderror>
                @foreach($priorities as $k=>$label)
                  <option value="{{ $k }}" @selected(old($field, 'medium') === $k)>{{ $label }}</option>
                @endforeach
              </select>
              @error($field) <p id="{{ $field }}_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>
      </div>

      {{-- Actions (เหมือนหน้า Asset) --}}
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
