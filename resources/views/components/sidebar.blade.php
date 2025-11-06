{{-- resources/views/components/sidebar.blade.php --}}
@php
  use Illuminate\Support\Facades\Route;

  $is   = fn($p) => request()->routeIs($p);

  $base = 'group flex items-center gap-3 pl-1 pr-2 h-10 rounded-md transition-all duration-150 font-medium relative';
  $off  = 'text-zinc-600 hover:text-emerald-600';
  $on   = 'text-emerald-600 font-semibold';

  $dot  = fn($active) => 'w-1.5 h-7 rounded-full bg-emerald-500 transition-all '.($active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60');
  $ico  = fn($active) => 'w-4 h-4 '.($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600');

  // helper: safe link (render <a> เฉพาะเมื่อมี route ชื่อนั้น)
  $rl = function(string $name, string $fallbackUrl = '#') {
    return Route::has($name) ? route($name) : $fallbackUrl;
  };
@endphp

<nav class="px-3 py-4 space-y-1 overflow-hidden">

  {{-- Dashboard --}}
  @php $active = $is('repair.dashboard'); @endphp
  <a href="{{ $rl('repair.dashboard', url('/')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="{{ $dot($active) }}"></span>
    <span class="w-8 h-8 flex items-center justify-center">
      {{-- bar-chart-3 --}}
      <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="7" rx="1"/><rect x="12" y="6" width="3" height="11" rx="1"/><rect x="17" y="13" width="3" height="4" rx="1"/></svg>
    </span>
    <span class="truncate">Dashboard</span>
  </a>

  {{-- Repair Jobs (ทั้งหมด) --}}
  @php $active = $is('maintenance.requests*'); @endphp
  <a href="{{ $rl('maintenance.requests.index', url('/maintenance/requests')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="{{ $dot($active) }}"></span>
    <span class="w-8 h-8 flex items-center justify-center">
      {{-- wrench --}}
      <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a4.5 4.5 0 1 0-6.36 6.36l8.49 8.49a2 2 0 0 0 2.83-2.83l-8.49-8.49z"/><path d="m8 8 3 3"/></svg>
    </span>
    <span class="truncate">Repair Jobs</span>
  </a>

  {{-- Repair Queue --}}
  @php $active = $is('repairs.queue'); @endphp
  <a href="{{ $rl('repairs.queue', url('/repairs/queue')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="{{ $dot($active) }}"></span>
    <span class="w-8 h-8 flex items-center justify-center">
      {{-- inbox --}}
      <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5h13.1a2 2 0 0 1 1.93 1.52L22 12v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6l1.52-5.48A2 2 0 0 1 5.45 5z"/></svg>
    </span>
    <span class="truncate">Repair Queue</span>
  </a>

  {{-- My Jobs --}}
  @php $active = $is('repairs.my_jobs'); @endphp
  <a href="{{ $rl('repairs.my_jobs', url('/repairs/my-jobs')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="{{ $dot($active) }}"></span>
    <span class="w-8 h-8 flex items-center justify-center">
      {{-- clipboard-list --}}
      <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="4" width="8" height="4" rx="1"/><path d="M9 12h6M9 16h6"/><rect x="4" y="4" width="16" height="18" rx="2"/></svg>
    </span>
    <span class="truncate">My Jobs</span>
  </a>

  {{-- Assets --}}
  @php $active = $is('assets.*'); @endphp
  <a href="{{ $rl('assets.index', url('/assets')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="{{ $dot($active) }}"></span>
    <span class="w-8 h-8 flex items-center justify-center">
      {{-- briefcase --}}
      <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M2 13h20"/></svg>
    </span>
    <span class="truncate">Assets</span>
  </a>

  {{-- Livechat --}}
  @php $active = $is('chat.*'); @endphp
  <a href="{{ $rl('chat.index', url('/chat')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="{{ $dot($active) }}"></span>
    <span class="w-8 h-8 flex items-center justify-center">
      {{-- message-square --}}
      <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
    </span>
    <span class="truncate">Livechat</span>
  </a>

  {{-- Profile --}}
  @php $active = $is('profile.*'); @endphp
  <a href="{{ $rl('profile.show', url('/profile')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
    <span class="{{ $dot($active) }}"></span>
    <span class="w-8 h-8 flex items-center justify-center">
      <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
    </span>
    <span class="truncate">Profile</span>
  </a>

  {{-- Manage Users (admin only) --}}
  @can('manage-users')
    @php $active = $is('admin.users.*'); @endphp
    <a href="{{ $rl('admin.users.index', url('/admin/users')) }}" class="{{ $base }} {{ $active ? $on : $off }}">
      <span class="{{ $dot($active) }}"></span>
      <span class="w-8 h-8 flex items-center justify-center">
        <svg class="{{ $ico($active) }}" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <path d="M9 11l2 2 4-4"/>
        </svg>
      </span>
      <span class="truncate">Manage Users</span>
    </a>
  @endcan

</nav>
