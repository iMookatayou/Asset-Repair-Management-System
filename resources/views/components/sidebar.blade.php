{{-- resources/views/components/sidebar.blade.php --}}
@php
  use Illuminate\Support\Facades\Route;

  $is = fn(...$p) => request()->routeIs($p);

  $rl = fn(string $name, string $fallback = '#') =>
      Route::has($name) ? route($name) : $fallback;

  $itemBase = 'menu-item group relative';
  $linkBase = 'flex items-center h-11 px-6 gap-3 text-sm font-medium transition-all duration-200 ease-in-out';
  $off      = 'text-zinc-600 hover:bg-slate-50 hover:text-[#0F2D5C]';
  $on       = 'bg-slate-100/80 text-[#0F2D5C] font-semibold';

  $strip = fn(bool $active) =>
      'absolute left-0 top-1/2 -translate-y-1/2 w-[4px] h-8 rounded-r bg-[#0F2D5C] transition-all duration-300 ease-out origin-left ' .
      ($active ? 'opacity-100 scale-y-100' : 'opacity-0 scale-y-50');
@endphp

{{-- ✅ สำคัญ: เอา w-64 / border ออก เพื่อให้ “ขนาดเดิมของคุณ” (layout คุม width) --}}
<div class="h-full flex flex-col overflow-hidden">

  {{-- ✅ HEADER (ติดบนสุด) — ลดความสูงให้เล็กลง --}}
  <div class="sticky top-0 z-30">
    <div class="flex items-center gap-3 border-b border-white/10 bg-[#0F2D5C] px-6 py-3">
      <img
        id="sidebarLogo"
        src="{{ asset('images/logoppk.png') }}"
        alt="Phrapokklao Logo"
        class="h-12 w-auto flex-shrink-0"
      />
      <div class="flex flex-col min-w-0 leading-tight">
        <div class="brand-en text-[16px] font-semibold tracking-[0.12em] text-white truncate">
          PHRAPOKKLAO
        </div>
        <div class="text-[12px] text-slate-200 truncate">
          โรงพยาบาลพระปกเกล้า
        </div>
      </div>
    </div>
  </div>

  {{-- ✅ NAV (เลื่อนเฉพาะเมนู) --}}
  <nav class="flex-1 py-3 overflow-y-auto overscroll-contain">

    <div class="px-6 mt-1 mb-1 text-[11px] font-bold uppercase tracking-[0.1em] text-zinc-400/80">
      Overview
    </div>

    {{-- Dashboard --}}
    @php $active = $is('repair.dashboard'); @endphp
    <a href="{{ $rl('repair.dashboard') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
      <span class="{{ $strip($active) }}"></span>
      <span class="icon-wrap">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 3v18h18"/>
          <rect x="7" y="10" width="3" height="7" rx="1"/>
          <rect x="12" y="6" width="3" height="11" rx="1"/>
          <rect x="17" y="13" width="3" height="4" rx="1"/>
        </svg>
      </span>
      <span class="menu-text truncate">Dashboard</span>
    </a>

    <div class="px-6 mt-4 mb-1 text-[11px] font-bold uppercase tracking-[0.1em] text-zinc-400/80">
      Operations
    </div>

    {{-- Requests --}}
    @php
      $active = $is('maintenance.requests.index','maintenance.requests.show','maintenance.requests.create','maintenance.requests.edit');
    @endphp
    <a href="{{ $rl('maintenance.requests.index') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
      <span class="{{ $strip($active) }}"></span>
      <span class="icon-wrap">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14.7 6.3a4.5 4.5 0 1 0-6.36 6.36l8.49 8.49a2 2 0 0 0 2.83-2.83l-8.49-8.49z"/>
          <path d="m8 8 3 3"/>
        </svg>
      </span>
      <span class="menu-text truncate">Requests</span>
    </a>

    {{-- Jobs --}}
    @can('view-my-jobs')
      @php $active = $is('repairs.my_jobs'); @endphp
      <a href="{{ $rl('repairs.my_jobs') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
        <span class="{{ $strip($active) }}"></span>
        <span class="icon-wrap">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <rect x="8" y="4" width="8" height="4" rx="1"/>
            <path d="M9 12h6M9 16h6"/>
            <rect x="4" y="4" width="16" height="18" rx="2"/>
          </svg>
        </span>
        <span class="menu-text truncate">Jobs</span>
      </a>
    @endcan

    {{-- Assets --}}
    @php $active = $is('assets.*'); @endphp
    <a href="{{ $rl('assets.index') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
      <span class="{{ $strip($active) }}"></span>
      <span class="icon-wrap">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="7" width="20" height="14" rx="2"/>
          <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
          <path d="M2 13h20"/>
        </svg>
      </span>
      <span class="menu-text truncate">Assets</span>
    </a>

    {{-- Livechat --}}
    @php $active = $is('chat.*'); @endphp
    <a href="{{ $rl('chat.index') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
      <span class="{{ $strip($active) }}"></span>
      <span class="icon-wrap">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
        </svg>
      </span>
      <span class="menu-text truncate">Livechat</span>
    </a>

    <div class="px-6 mt-4 mb-1 text-[11px] font-bold uppercase tracking-[0.1em] text-zinc-400/80">
      Feedback
    </div>

    {{-- Evaluate --}}
    @php $active = $is('maintenance.requests.rating.evaluate'); @endphp
    <a href="{{ $rl('maintenance.requests.rating.evaluate') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
      <span class="{{ $strip($active) }}"></span>
      <span class="icon-wrap">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <rect x="6" y="4" width="12" height="16" rx="2"/>
          <path d="M9 4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6H9V4.5z"/>
          <path d="M9 11h6"/>
          <path d="M9 14h3"/>
          <path d="m11 18 1-2 1 2"/>
        </svg>
      </span>
      <span class="menu-text truncate">Evaluate</span>
    </a>

    {{-- Technician Ratings --}}
    @php $active = $is('maintenance.requests.rating.technicians'); @endphp
    <a href="{{ $rl('maintenance.requests.rating.technicians') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
      <span class="{{ $strip($active) }}"></span>
      <span class="icon-wrap">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 21h18"/>
          <rect x="5" y="10" width="3" height="7" rx="1"/>
          <rect x="10.5" y="7" width="3" height="10" rx="1"/>
          <rect x="16" y="4" width="3" height="13" rx="1"/>
        </svg>
      </span>
      <span class="menu-text truncate">Technician Ratings</span>
    </a>

    <div class="px-6 mt-4 mb-1 text-[11px] font-bold uppercase tracking-[0.1em] text-zinc-400/80">
      Account
    </div>

    @php $active = $is('profile.*'); @endphp
    <a href="{{ $rl('profile.show') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
      <span class="{{ $strip($active) }}"></span>
      <span class="icon-wrap">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <circle cx="9" cy="7" r="4"/>
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </span>
      <span class="menu-text truncate">Profile</span>
    </a>

    @can('manage-users')
      <div class="px-6 mt-4 mb-1 text-[11px] font-bold uppercase tracking-[0.1em] text-zinc-400/80">
        Administration
      </div>

      @php $active = $is('admin.users.*'); @endphp
      <a href="{{ $rl('admin.users.index') }}" class="{{ $itemBase }} {{ $linkBase }} {{ $active ? $on : $off }}">
        <span class="{{ $strip($active) }}"></span>
        <span class="icon-wrap">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            <path d="M9 11l2 2 4-4"/>
          </svg>
        </span>
        <span class="menu-text truncate">Manage Users</span>
      </a>
    @endcan

    <div class="h-6"></div>
  </nav>
</div>
