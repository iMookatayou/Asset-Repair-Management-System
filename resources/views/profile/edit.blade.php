@extends('layouts.app')
@section('title', 'Edit Profile')

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Edit Profile
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            ปรับข้อมูลส่วนตัวของคุณ เช่น เลขบัตรประชาชน ชื่อ อีเมล แผนก และรูปโปรไฟล์
          </p>
        </div>

        <a href="{{ route('profile.show') }}"
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
  @php
    // URL รูป (อาจเป็น null ทั้งคู่ได้)
    $avatarMain  = data_get($user, 'avatar_url');
    $avatarThumb = data_get($user, 'avatar_thumb_url');

    // ตัดอักษรย่อจากชื่อผู้ใช้ (2 ตัว)
    $name = trim((string) ($user->name ?? ''));
    $parts = preg_split('/\s+/u', $name) ?: [];
    $initials = strtoupper(mb_substr($parts[0] ?? 'U', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));

    // แผนกปัจจุบัน
    $currentDeptId = old('department_id', $user->department_id);
  @endphp

  <div class="mx-auto max-w-3xl py-6 space-y-5">
    @if (session('status'))
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
        {{ session('status') }}
      </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PATCH')

        {{-- รูปโปรไฟล์ --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">รูปโปรไฟล์</label>

          <div class="flex items-center gap-4">
            <img id="avatar-preview"
                 @if(!$avatarThumb && !$avatarMain) class="hidden h-16 w-16 rounded-full object-cover ring-1 ring-slate-200"
                 @else class="h-16 w-16 rounded-full object-cover ring-1 ring-slate-200" @endif
                 src="{{ $avatarThumb ?: $avatarMain }}"
                 srcset="{{ $avatarThumb ?: $avatarMain }} 128w, {{ $avatarMain ?: $avatarThumb }} 512w"
                 sizes="64px"
                 alt="Profile photo" width="64" height="64"
                 loading="lazy" decoding="async" />

            <div id="avatar-fallback"
                 @if($avatarThumb || $avatarMain) class="hidden" @endif
                 class="flex h-16 w-16 items-center justify-center rounded-full bg-violet-500 text-white ring-1 ring-slate-200">
              <span class="text-lg font-semibold">{{ $initials }}</span>
            </div>

            <div class="flex-1 space-y-2">
              <div>
                <input id="avatar-input" type="file" name="avatar"
                       accept=".jpg,.jpeg,.png,.webp,image/*"
                       class="block w-full text-sm file:mr-3 file:rounded-md file:border file:border-slate-300 file:bg-white file:px-3 file:py-1.5 file:text-sm file:text-slate-700 hover:file:bg-slate-50">
                <p class="mt-1 text-xs text-slate-500">รองรับไฟล์: JPG, JPEG, PNG, WEBP (ไม่เกิน 2MB)</p>
                @error('avatar')
                  <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
              </div>

              <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input id="remove-avatar" type="checkbox" name="remove_avatar" value="1"
                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                ลบรูปโปรไฟล์ปัจจุบัน
              </label>
            </div>
          </div>
        </div>

        {{-- citizen_id (อ่านอย่างเดียว) --}}
        <div>
          <label for="citizen_id" class="block text-sm font-medium text-slate-700">
            เลขประจำตัวประชาชน (ใช้เข้าสู่ระบบ)
          </label>
          <input id="citizen_id"
                 type="text"
                 value="{{ $user->citizen_id }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 cursor-not-allowed"
                 readonly>
          <p class="mt-1 text-xs text-slate-500">
            ไม่สามารถแก้ไขเลขบัตรจากหน้านี้ได้ หากต้องการเปลี่ยนให้ติดต่อผู้ดูแลระบบ
          </p>
        </div>

        {{-- ชื่อ-นามสกุล --}}
        <div>
          <label for="name" class="block text-sm font-medium text-slate-700">ชื่อ-นามสกุล</label>
          <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                 class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                        focus:border-emerald-600 focus:ring-emerald-600
                        @error('name') border-rose-400 ring-rose-200 @enderror">
          @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        {{-- อีเมล (ถ้ามี) --}}
        <div>
          <label for="email" class="block text-sm font-medium text-slate-700">อีเมล (ถ้ามี)</label>
          <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                 class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                        focus:border-emerald-600 focus:ring-emerald-600
                        @error('email') border-rose-400 ring-rose-200 @enderror">
          @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          <p class="mt-1 text-xs text-slate-500">
            ถ้ามีอีเมล ระบบจะใช้สำหรับรีเซ็ตรหัสผ่านและการแจ้งเตือนต่าง ๆ
            การเปลี่ยนอีเมลจะทำให้สถานะยืนยันอีเมลถูกรีเซ็ต
          </p>
        </div>

        {{-- แผนก (department_id) --}}
        <div>
          <label for="department_id" class="block text-sm font-medium text-slate-700">
            หน่วยงาน (ถ้ามี)
          </label>
          <select id="department_id" name="department_id"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                         focus:border-emerald-600 focus:ring-emerald-600
                         @error('department_id') border-rose-400 ring-rose-200 @enderror">
            <option value="">— ไม่ระบุหน่วยงาน —</option>
            @foreach(\App\Models\Department::query()
                ->orderBy('code')
                ->get() as $dept)
              @php
                $deptLabel = $dept->display_name
                    ?? $dept->name_th
                    ?? $dept->name_en
                    ?? $dept->name
                    ?? $dept->code;
              @endphp
              <option value="{{ $dept->id }}" @selected($currentDeptId == $dept->id)>
                {{ $dept->code }} — {{ $deptLabel }}
              </option>
            @endforeach
          </select>
          @error('department_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="pt-2">
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
            บันทึกการเปลี่ยนแปลง
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal ครอปรูป --}}
  <div id="cropper-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative mx-auto mt-10 w-[92vw] max-w-2xl rounded-xl bg-white shadow-xl">
      <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
        <h3 class="font-semibold text-slate-900">ครอปรูปโปรไฟล์</h3>
        <button type="button" id="cropper-close" class="rounded-md px-2 py-1 text-slate-600 hover:bg-slate-100">ปิด</button>
      </div>
      <div class="p-4">
        <div class="aspect-square w-full max-h-[60vh] overflow-hidden rounded-lg border">
          <img id="cropper-image" alt="To crop" class="max-w-full block">
        </div>
        <p class="mt-2 text-xs text-slate-500">ลาก/ซูม เพื่อครอปภาพสี่เหลี่ยมจัตุรัส (จะแปลงเป็น 512×512)</p>
      </div>
      <div class="flex items-center justify-end gap-2 px-4 py-3 border-t border-slate-200">
        <button type="button" id="cropper-cancel" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">ยกเลิก</button>
        <button type="button" id="cropper-apply" class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">ใช้รูปนี้</button>
      </div>
    </div>
  </div>
@endsection

@push('styles')
<link href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
(() => {
  const fileInput   = document.getElementById('avatar-input');
  const previewEl   = document.getElementById('avatar-preview');
  const fallbackEl  = document.getElementById('avatar-fallback');
  const removeBox   = document.getElementById('remove-avatar');
  const modal       = document.getElementById('cropper-modal');
  const imgEl       = document.getElementById('cropper-image');
  const btnClose    = document.getElementById('cropper-close');
  const btnCancel   = document.getElementById('cropper-cancel');
  const btnApply    = document.getElementById('cropper-apply');

  let cropper = null;
  let pendingFileName = null;

  function openModal() { modal.classList.remove('hidden'); }
  function closeModal() {
    modal.classList.add('hidden');
    if (cropper) { cropper.destroy(); cropper = null; }
    imgEl.src = '';
  }

  fileInput.addEventListener('change', (e) => {
    const [file] = e.target.files || [];
    if (!file) return;

    if (removeBox) removeBox.checked = false;
    pendingFileName = file.name;

    const reader = new FileReader();
    reader.onload = () => {
      imgEl.src = reader.result;
      openModal();
      imgEl.onload = () => {
        cropper = new Cropper(imgEl, {
          aspectRatio: 1,
          viewMode: 1,
          dragMode: 'move',
          autoCropArea: 1,
          background: false,
          zoomOnWheel: true,
          checkCrossOrigin: false,
        });
      };
    };
    reader.readAsDataURL(file);
  });

  btnClose.addEventListener('click', closeModal);
  btnCancel.addEventListener('click', () => {
    fileInput.value = '';
    pendingFileName = null;
    closeModal();
  });

  btnApply.addEventListener('click', () => {
    if (!cropper) return;

    const canvas = cropper.getCroppedCanvas({ width: 512, height: 512 });
    canvas.toBlob((blob) => {
      if (!blob) return;

      const baseName = (pendingFileName || 'avatar').replace(/\.(png|jpe?g|webp|gif|heic)$/i, '');
      const fileName = baseName + '.webp';
      const croppedFile = new File([blob], fileName, { type: 'image/webp', lastModified: Date.now() });

      const dt = new DataTransfer();
      dt.items.add(croppedFile);
      fileInput.files = dt.files;

      const previewReader = new FileReader();
      previewReader.onload = () => {
        previewEl.src = previewReader.result;
        previewEl.classList.remove('hidden');
        if (fallbackEl) fallbackEl.classList.add('hidden');
      };
      previewReader.readAsDataURL(croppedFile);

      closeModal();
    }, 'image/webp', 0.82);
  });

  if (removeBox) {
    removeBox.addEventListener('change', (e) => {
      if (e.target.checked) {
        fileInput.value = '';
        pendingFileName = null;
        previewEl.classList.add('hidden');
        previewEl.removeAttribute('src');
        if (fallbackEl) fallbackEl.classList.remove('hidden');
      }
    });
  }
})();
</script>
@endpush
