{{-- resources/views/components/sidebar.blade.php --}}
@php
  $itemBase  = 'group relative flex items-center gap-3 px-3 py-2 rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500';
  $inactive  = 'text-zinc-300 hover:bg-zinc-800';
  $activeBox = 'bg-zinc-800 text-emerald-400';
@endphp

<nav class="p-3">
  <div class="text-xs uppercase text-zinc-400 mb-2">เมนูหลัก</div>
  <ul class="space-y-1">

    @php $active = request()->routeIs('repair.dashboard'); @endphp
    <li>
      <a href="{{ route('repair.dashboard') }}"
         class="{{ $itemBase }} {{ $active ? $activeBox : $inactive }}"
         aria-current="{{ $active ? 'page' : 'false' }}">
        <x-app-icon name="bar-chart-3" class="w-4 h-4 shrink-0"/>
        <span>Dashboard</span>
      </a>
    </li>

    @php $active = request()->routeIs('maintenance.requests.*'); @endphp
    <li>
      <a href="{{ route('maintenance.requests.index') }}"
         class="{{ $itemBase }} {{ $active ? $activeBox : $inactive }}"
         aria-current="{{ $active ? 'page' : 'false' }}">
        <x-app-icon name="wrench" class="w-4 h-4 shrink-0"/>
        <span>Repair jobs</span>
      </a>
    </li>

    @php $active = request()->routeIs('assets.*'); @endphp
    <li>
      <a href="{{ route('assets.index') }}"
         class="{{ $itemBase }} {{ $active ? $activeBox : $inactive }}"
         aria-current="{{ $active ? 'page' : 'false' }}">
        <x-app-icon name="briefcase" class="w-4 h-4 shrink-0"/>
        <span>Assets</span>
      </a>
    </li>

    @php $active = request()->routeIs('users.*'); @endphp
    <li>
      <a href="{{ route('users.index') }}"
         class="{{ $itemBase }} {{ $active ? $activeBox : $inactive }}"
         aria-current="{{ $active ? 'page' : 'false' }}">
        <x-app-icon name="users" class="w-4 h-4 shrink-0"/>
        <span>Users</span>
      </a>
    </li>

  </ul>
</nav>
