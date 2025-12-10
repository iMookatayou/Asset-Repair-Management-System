{{-- resources/views/admin/users/_form.blade.php --}}
@php
  use App\Models\User as UserModel;
  /** @var \App\Models\User|null $user */

  // ถ้า $user เป็น null (หน้า create) ให้สร้าง instance เปล่า ๆ
  $user = $user instanceof UserModel ? $user : new UserModel();

  $roles       = $roles       ?? UserModel::availableRoles();
  $roleLabels  = $roleLabels  ?? UserModel::roleLabels();
  $departments = $departments ?? collect();
  $isEdit      = $user->exists;

  // role ปัจจุบัน
  $currentRole = old('role');
  if ($currentRole === null) {
      $currentRole = $user->role ?: UserModel::ROLE_COMPUTER_OFFICER;
  }

  // base class ของ input ปกติ (ไม่ใช่ select ที่เป็น TomSelect)
  $CTL = 'mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
          focus:border-emerald-500 focus:ring-emerald-500 bg-white';
@endphp

<div class="grid gap-6 md:grid-cols-2">

  {{-- ชื่อผู้ใช้ --}}
  <div class="space-y-1.5">
    <label for="name" class="block text-sm font-medium text-slate-700">
      ชื่อผู้ใช้ <span class="text-rose-500">*</span>
    </label>
    <input
      id="name"
      name="name"
      type="text"
      class="{{ $CTL }}"
      value="{{ old('name', $user->name) }}"
      required
    >
    @error('name')
      <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- เลขบัตรประชาชน 13 หลัก --}}
  <div class="space-y-1.5">
    <label for="citizen_id" class="block text-sm font-medium text-slate-700">
      เลขบัตรประชาชน <span class="text-rose-500">*</span>
    </label>
    <input
      id="citizen_id"
      name="citizen_id"
      type="text"
      inputmode="numeric"
      maxlength="13"
      class="{{ $CTL }}"
      value="{{ old('citizen_id', $user->citizen_id) }}"
      required
    >
    @error('citizen_id')
      <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- อีเมล (ไม่บังคับ) --}}
  <div class="space-y-1.5">
    <label for="email" class="block text-sm font-medium text-slate-700">
      อีเมล (ถ้ามี)
    </label>
    <input
      id="email"
      name="email"
      type="email"
      class="{{ $CTL }}"
      value="{{ old('email', $user->email) }}"
    >
    @error('email')
      <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- หน่วยงาน (เก็บเป็น code ใน users.department) --}}
  <div class="space-y-1.5">
    <label for="department_id" class="block text-sm font-medium text-slate-700">
      หน่วยงาน (ถ้ามี)
    </label>

    <div class="relative mt-1">
      {{-- ไอคอนแว่นขยาย --}}
      <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 z-10 text-slate-400">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"></circle>
          <line x1="16" y1="16" x2="20" y2="20"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
        </svg>
      </span>

      <select
        id="department_id"   {{-- ✅ ให้ JS/TomSelect ใช้ตัวนี้ --}}
        name="department"    {{-- ✅ backend ใช้ field department (code) --}}
        placeholder="— เลือกหน่วยงาน —"
        class="ts-basic ts-with-icon w-full @error('department') ts-error @enderror"
      >
        <option value="">— ไม่ระบุหน่วยงาน —</option>
        @foreach($departments as $dept)
          <option value="{{ $dept->code }}"
            @selected(old('department', $user->department) == $dept->code)>
            {{ $dept->code }} — {{ $dept->display_name ?? $dept->name_th ?? $dept->name_en ?? $dept->name ?? '' }}
          </option>
        @endforeach
      </select>
    </div>

    @error('department')
      <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- บทบาท --}}
  <div class="space-y-1.5">
    <label for="role" class="block text-sm font-medium text-slate-700">
      บทบาท <span class="text-rose-500">*</span>
    </label>

    <div class="relative mt-1">
      {{-- ไอคอนแว่นขยาย --}}
      <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 z-10 text-slate-400">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"></circle>
          <line x1="16" y1="16" x2="20" y2="20"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
        </svg>
      </span>

      <select
        id="role"
        name="role"
        class="ts-basic ts-with-icon w-full @error('role') ts-error @enderror"
        required
      >
        @foreach($roles as $role)
          <option value="{{ $role }}" @selected($currentRole === $role)>
            {{ $roleLabels[$role] ?? $role }}
          </option>
        @endforeach
      </select>
    </div>

    @error('role')
      <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- รหัสผ่าน --}}
  <div class="space-y-1.5">
    <label for="password" class="block text-sm font-medium text-slate-700">
      รหัสผ่าน
      @if($isEdit)
        <span class="text-xs font-normal text-slate-500">
          (เว้นว่างหากไม่ต้องการเปลี่ยน)
        </span>
      @else
        <span class="text-xs font-normal text-slate-500">
          (อย่างน้อย 8 ตัวอักษร)
        </span>
      @endif
    </label>
    <input
      id="password"
      name="password"
      type="password"
      class="{{ $CTL }}"
      autocomplete="new-password"
      @if(!$isEdit) required @endif
    >
    @error('password')
      <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- ยืนยันรหัสผ่าน --}}
  <div class="space-y-1.5">
    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">
      ยืนยันรหัสผ่าน
      <span class="text-xs font-normal text-slate-500">
        (กรอกให้ตรงกับรหัสผ่าน)
      </span>
    </label>
    <input
      id="password_confirmation"
      name="password_confirmation"
      type="password"
      class="{{ $CTL }}"
      autocomplete="new-password"
      @if(!$isEdit) required @endif
    >
    @error('password_confirmation')
      <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

</div>
