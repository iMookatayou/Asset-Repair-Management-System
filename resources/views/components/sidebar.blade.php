{{-- resources/views/components/sidebar.blade.php --}}
@php
  $is = fn($p) => request()->routeIs($p);

  $base = 'flex items-center gap-3 pl-1 pr-2 h-10 rounded-md transition-all duration-150 font-medium relative';
  $off  = 'text-zinc-600 hover:text-emerald-600';
  $on   = 'text-emerald-600 font-semibold';
@endphp

<nav class="px-3 py-4 space-y-1 overflow-hidden">

  {{-- Dashboard --}}
  @php $active = $is('repair.dashboard'); @endphp
  <a href="{{ route('repair.dashboard') }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 transition-all
      {{ $active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60' }}"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <x-app-icon name="bar-chart-3" class="w-4 h-4 
      {{ $active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600' }}" />
    </span>

    <span class="truncate">{{ $active ? 'Dashboard' : 'Dashboard' }}</span>
  </a>

  {{-- Repair Jobs --}}
  @php $active = $is('maintenance.requests*'); @endphp
  <a href="{{ route('maintenance.requests.index') }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 
      {{ $active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60' }}"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <x-app-icon name="wrench" class="w-4 h-4 
      {{ $active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600' }}" />
    </span>

    <span class="truncate">Repair Jobs</span>
  </a>

  {{-- Assets --}}
  @php $active = $is('assets.*'); @endphp
  <a href="{{ route('assets.index') }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 
      {{ $active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60' }}"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <x-app-icon name="briefcase" class="w-4 h-4 
      {{ $active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600' }}" />
    </span>

    <span class="truncate">Assets</span>
  </a>

  {{-- Users --}}
  @php $active = $is('users.*'); @endphp
  <a href="{{ route('users.index') }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 
      {{ $active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60' }}"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <x-app-icon name="users" class="w-4 h-4 
      {{ $active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600' }}" />
    </span>

    <span class="truncate">Users</span>
  </a>

</nav>
