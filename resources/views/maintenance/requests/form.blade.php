@php
  /** @var \App\Models\MaintenanceRequest|null $req */
  // priority ต้องใช้ medium (ไม่ใช่ normal)
  $priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];

  $defaultReporter = old('reporter_id', (string)($req->reporter_id ?? auth()->id()));
  $assetList = is_iterable($assets ?? null) ? $assets : [];
  $userList  = is_iterable($users ?? null)  ? $users  : [];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  {{-- Asset (search-select) --}}
  <div>
    <label for="asset_id" class="block text-sm font-medium text-slate-700">Asset</label>
    @php $assetColl = is_iterable($assetList ?? null) ? collect($assetList) : collect(); @endphp
    <x-search-select name="asset_id" id="asset_id"
      :items="$assetColl->map(fn($a)=> (object)['id'=>$a->id,'name'=>(($a->asset_code ?? '—')).' — '.($a->name ?? '')])"
      label-field="name" value-field="id"
      :value="old('asset_id', (string)($req->asset_id ?? ''))"
      placeholder="— เลือกทรัพย์สิน —" />
    @error('asset_id')<p id="asset_id_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
  </div>

  {{-- Reporter (optional; search-select) --}}
  <div>
    <label for="reporter_id" class="block text-sm font-medium text-slate-700">Reporter (optional)</label>
    @php $userColl = is_iterable($userList ?? null) ? collect($userList) : collect(); @endphp
    <x-search-select name="reporter_id" id="reporter_id"
      :items="$userColl->map(fn($u)=> (object)['id'=>$u->id,'name'=>$u->name])"
      label-field="name" value-field="id"
      :value="$defaultReporter"
      placeholder="— ใช้ผู้ใช้งานปัจจุบัน —" />
    @error('reporter_id')<p id="reporter_id_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
  </div>

  {{-- Reporter Email (optional; ใช้กรณีไม่ได้เลือกผู้ใช้ในระบบ) --}}
  <div>
    <label for="reporter_email" class="block text-sm font-medium text-slate-700">
      Reporter Email (optional)
    </label>
    <input
      id="reporter_email"
      name="reporter_email"
      type="email"
      maxlength="255"
      value="{{ old('reporter_email', $req->reporter_email ?? '') }}"
  class="mt-1 w-full rounded-xl border px-3 py-2 text-sm placeholder-slate-400 focus:border-emerald-600 focus:ring-emerald-600 border-slate-300 @error('reporter_email') ring-rose-200 @enderror"
      aria-invalid="@error('reporter_email') true @else false @enderror"
      aria-describedby="@error('reporter_email') reporter_email_error @enderror"
      placeholder="someone@example.com" />
    <div class="mt-1 text-xs text-slate-500">หากไม่ได้เลือก Reporter เป็นผู้ใช้ในระบบ สามารถกรอกอีเมลผู้ติดต่อที่นี่</div>
    @error('reporter_email')
      <p id="reporter_email_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
  </div>

  {{-- Title --}}
  <div class="md:col-span-2">
    <label for="title" class="block text-sm font-medium text-slate-700">
      Title <span class="text-rose-600" aria-hidden="true">*</span>
    </label>
    <input
      id="title"
      name="title"
      type="text"
      required
      maxlength="150"
      autocomplete="off"
      spellcheck="false"
      value="{{ old('title', $req->title ?? '') }}"
  class="mt-1 w-full rounded-xl border px-3 py-2 text-sm placeholder-slate-400 focus:border-emerald-600 focus:ring-emerald-600 border-slate-300 @error('title') ring-rose-200 @enderror"
      aria-invalid="@error('title') true @else false @enderror"
      aria-describedby="@error('title') title_error @enderror"
      placeholder="Short, clear summary (e.g., Aircon leaking in Room 302)"
    />
    <div class="mt-1 text-xs text-slate-500">Max 150 characters.</div>
    @error('title')
      <p id="title_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
  </div>

  {{-- Description --}}
  <div class="md:col-span-2">
    <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
    <textarea
      id="description"
      name="description"
      rows="6"
      spellcheck="false"
  class="mt-1 w-full resize-y rounded-xl border px-3 py-2 text-sm placeholder-slate-400 focus:border-emerald-600 focus:ring-emerald-600 border-slate-300 @error('description') ring-rose-200 @enderror"
      aria-invalid="@error('description') true @else false @enderror"
      aria-describedby="@error('description') description_error @enderror"
      placeholder="Add details to help triage (symptoms, when it occurs, photo links, etc.)"
    >{{ old('description', $req->description ?? '') }}</textarea>
    @error('description')
      <p id="description_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
  </div>

  {{-- Priority (use: low|medium|high|urgent) --}}
  <div>
    <label for="priority" class="block text-sm font-medium text-slate-700">
      Priority <span class="text-rose-600" aria-hidden="true">*</span>
    </label>
    <select
      id="priority"
      name="priority"
      required
  class="mt-1 w-full rounded-xl border px-3 py-2 text-sm focus:border-emerald-600 focus:ring-emerald-600 border-slate-300 @error('priority') ring-rose-200 @enderror"
      aria-invalid="@error('priority') true @else false @enderror"
      aria-describedby="@error('priority') priority_error @enderror"
    >
      @php $priorityValue = old('priority', $req->priority ?? 'medium'); @endphp
      @foreach ($priorities as $k => $label)
        <option value="{{ $k }}" @selected($priorityValue === $k)>{{ $label }}</option>
      @endforeach
    </select>
    @error('priority')
      <p id="priority_error" class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
  </div>
</div>
