@extends('layouts.app')
@section('title','Create Asset')

@section('page-header')
  {{-- Header โทนอ่อนพร้อมไอคอน --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ไอคอนเพิ่มข้อมูล (ไม่ต้องใช้ lib เพิ่ม) --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create Asset
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            เพิ่มครุภัณฑ์ใหม่เข้าสู่ระบบ — โปรดระบุข้อมูลให้ครบถ้วนเพื่อความถูกต้องในการจัดเก็บ
          </p>
        </div>

        <a href="{{ route('assets.index') }}"
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
    {{-- Global error summary --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <p class="font-medium">มีข้อผิดพลาดในการบันทึกข้อมูล:</p>
        <ul class="mt-2 list-disc pl-5 text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('assets.store') }}"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      @csrf

      {{-- Section: ข้อมูลหลัก --}}
      <div class="space-y-6">
        <div>
          <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
          <p class="text-sm text-slate-500">ระบุรหัสและชื่อครุภัณฑ์ให้ชัดเจน</p>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="asset_code" class="block text-sm font-medium text-slate-700">
                รหัสครุภัณฑ์ <span class="text-rose-600">*</span>
              </label>
              <input id="asset_code" name="asset_code" type="text" required autofocus autocomplete="off"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('asset_code') }}">
              @error('asset_code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
              <label for="name" class="block text-sm font-medium text-slate-700">
                ชื่อครุภัณฑ์ <span class="text-rose-600">*</span>
              </label>
              <input id="name" name="name" type="text" required
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('name') }}">
              @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        {{-- Section: การจัดประเภท / หน่วยงาน --}}
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">การจัดประเภท / หน่วยงาน</h2>
          <p class="text-sm text-slate-500">เลือกหมวดหมู่และหน่วยงาน (ถ้ามี)</p>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="type" class="block text-sm font-medium text-slate-700">ประเภท (type)</label>
              <input id="type" name="type" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('type') }}">
            </div>
            <div>
              <label for="category" class="block text-sm font-medium text-slate-700">หมวด (legacy)</label>
              <input id="category" name="category" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('category') }}">
            </div>

            @isset($categories)
              <div>
                <label for="category_id" class="block text-sm font-medium text-slate-700">หมวด (FK)</label>
                <select id="category_id" name="category_id"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600">
                  <option value="">— ไม่ระบุ —</option>
                  @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                  @endforeach
                </select>
              </div>
            @endisset

            @isset($departments)
              <div>
                <label for="department_id" class="block text-sm font-medium text-slate-700">หน่วยงาน</label>
                <select id="department_id" name="department_id"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600">
                  <option value="">— ไม่ระบุ —</option>
                  @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                  @endforeach
                </select>
              </div>
            @endisset
          </div>
        </div>

        {{-- Section: สเปก / ตำแหน่ง / สถานะ --}}
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">สเปก / ตำแหน่ง / สถานะ</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="brand" class="block text-sm font-medium text-slate-700">ยี่ห้อ</label>
              <input id="brand" name="brand" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('brand') }}">
            </div>
            <div>
              <label for="model" class="block text-sm font-medium text-slate-700">รุ่น</label>
              <input id="model" name="model" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('model') }}">
            </div>
            <div>
              <label for="serial_number" class="block text-sm font-medium text-slate-700">Serial</label>
              <input id="serial_number" name="serial_number" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('serial_number') }}">
            </div>
            <div>
              <label for="location" class="block text-sm font-medium text-slate-700">ที่ตั้ง</label>
              <input id="location" name="location" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('location') }}">
            </div>
            <div>
              <label for="status" class="block text-sm font-medium text-slate-700">สถานะ</label>
              <select id="status" name="status"
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600">
                @php $statuses = ['active'=>'ใช้งาน','in_repair'=>'ซ่อม','disposed'=>'จำหน่าย']; @endphp
                @foreach($statuses as $k=>$label)
                  <option value="{{ $k }}" @selected(old('status','active') === $k)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Section: วันที่ซื้อ / หมดประกัน --}}
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">อายุการใช้งาน</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="purchase_date" class="block text-sm font-medium text-slate-700">วันที่ซื้อ</label>
              <input id="purchase_date" name="purchase_date" type="date"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('purchase_date') }}">
            </div>
            <div>
              <label for="warranty_expire" class="block text-sm font-medium text-slate-700">หมดประกัน</label>
              <input id="warranty_expire" name="warranty_expire" type="date"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="{{ old('warranty_expire') }}">
            </div>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div class="mt-6 flex justify-end gap-2">
        <a href="{{ route('assets.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          Cancel
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          Save
        </button>
      </div>
    </form>
  </div>
@endsection
