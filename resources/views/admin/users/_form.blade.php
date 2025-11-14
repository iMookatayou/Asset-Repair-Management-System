@php
  $roles = $roles ?? \App\Models\User::availableRoles();
  $labels = \App\Models\User::roleLabels();
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
  <div>
    <label class="block text-sm font-medium text-zinc-700">Name <span class="text-rose-600">*</span></label>
    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
           class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-zinc-700">Email <span class="text-rose-600">*</span></label>
    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
           class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
    @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-zinc-700">
      Password {{ isset($user) ? '(leave blank to keep)' : '' }} {{ isset($user) ? '' : ' *' }}
    </label>
    <input type="password" name="password"
           class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
    @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-zinc-700">Confirm Password</label>
    <input type="password" name="password_confirmation"
           class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
  </div>

  <div>
    <label class="block text-sm font-medium text-zinc-700">Department</label>
    <input type="text" name="department" value="{{ old('department', $user->department ?? '') }}"
           class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
    @error('department')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-zinc-700">บทบาท Role <span class="text-rose-600">*</span></label>
    <select name="role" required
            class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
      @foreach($roles as $r)
        <option value="{{ $r }}" @selected(old('role', $user->role ?? '') === $r)>{{ $labels[$r] ?? ucfirst($r) }}</option>
      @endforeach
    </select>
    @error('role')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
  </div>
</div>
