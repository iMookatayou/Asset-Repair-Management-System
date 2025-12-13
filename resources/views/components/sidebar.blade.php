{{-- LOGO + TOP BAR (ความสูงตามเดิม) --}}
<div class="h-full w-64 flex flex-col bg-white border-r border-gray-300">

    {{-- LOGO BLOCK --}}
    <div class="sidebar-logo px-6 py-5 flex items-center gap-3 border-b border-gray-200 bg-white">
        <img src="{{ asset('images/logoppk.png') }}"
             alt="Phrapokklao Logo"
             class="h-14 w-auto flex-shrink-0"> {{-- โลโก้ใหญ่ขึ้น h-14 --}}

        <div class="flex flex-col min-w-0 leading-tight">
            <div class="brand-en text-[17px] font-semibold tracking-[0.14em] text-zinc-900">
                PHRAPOKKLAO
            </div>
            <div class="text-[12px] text-zinc-500">
                โรงพยาบาลพระปกเกล้า
            </div>
        </div>
    </div>

    @php
        use Illuminate\Support\Facades\Route;

        // ให้รองรับ pattern ได้หลายอัน
        $is = fn(...$p) => request()->routeIs($p);

        // base style ของเมนู + spacing ซ้าย + ความสูงแต่ละแถว
        $base = 'group relative flex items-center h-11 px-6 gap-3 text-sm font-medium rounded-md transition';

        $off  = 'text-zinc-600 hover:bg-emerald-50/60 hover:text-emerald-700';
        $on   = 'bg-emerald-50 text-emerald-700';

        $ico = fn($active) =>
            'w-5 h-5 flex-shrink-0 transition '.
            ($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600');

        // จุด active → ด้านขวา
        $dot = fn($active) =>
            'absolute right-3 top-1/2 -translate-y-1/2 w-1.5 h-7 rounded-full bg-emerald-500 transition '.
            ($active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60');

        $rl = fn(string $name, string $fallback = '#') =>
            Route::has($name) ? route($name) : $fallback;
    @endphp

    {{-- NAVIGATION --}}
    <nav class="flex-1 py-3 overflow-y-auto">

        {{-- SECTION: Overview --}}
        <div class="px-6 mt-1 mb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
            Overview
        </div>

        {{-- Dashboard --}}
        @php $active = $is('repair.dashboard'); @endphp
        <a href="{{ $rl('repair.dashboard') }}" class="{{ $base }} {{ $active ? $on : $off }}">
            <svg class="{{ $ico($active) }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v18h18"/>
                <rect x="7" y="10" width="3" height="7" rx="1"/>
                <rect x="12" y="6" width="3" height="11" rx="1"/>
                <rect x="17" y="13" width="3" height="4" rx="1"/>
            </svg>
            <span class="truncate">Dashboard</span>
            <span class="{{ $dot($active) }}"></span>
        </a>

        {{-- SECTION: Operations --}}
        <div class="px-6 mt-3 mb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
            Operations
        </div>

        {{-- Requests (ไม่ใช้ * แล้ว เลือกเฉพาะ route ของ request จริง ๆ) --}}
        @php
            $active = $is(
                'maintenance.requests.index',
                'maintenance.requests.show',
                'maintenance.requests.create',
                'maintenance.requests.edit'
            );
        @endphp
        <a href="{{ $rl('maintenance.requests.index') }}" class="{{ $base }} {{ $active ? $on : $off }}">
            <svg class="{{ $ico($active) }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a4.5 4.5 0 1 0-6.36 6.36l8.49 8.49a2 2 0 0 0 2.83-2.83l-8.49-8.49z"/>
                <path d="m8 8 3 3"/>
            </svg>
            <span class="truncate">Requests</span>
            <span class="{{ $dot($active) }}"></span>
        </a>

        {{-- Jobs (เฉพาะสิทธิ์) --}}
        @can('view-my-jobs')
            @php $active = $is('repairs.my_jobs'); @endphp
            <a href="{{ $rl('repairs.my_jobs') }}" class="{{ $base }} {{ $active ? $on : $off }}">
                <svg class="{{ $ico($active) }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <rect x="8" y="4" width="8" height="4" rx="1"/>
                    <path d="M9 12h6M9 16h6"/>
                    <rect x="4" y="4" width="16" height="18" rx="2"/>
                </svg>
                <span class="truncate">Jobs</span>
                <span class="{{ $dot($active) }}"></span>
            </a>
        @endcan

        {{-- Assets --}}
        @php $active = $is('assets.*'); @endphp
        <a href="{{ $rl('assets.index') }}" class="{{ $base }} {{ $active ? $on : $off }}">
            <svg class="{{ $ico($active) }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                <path d="M2 13h20"/>
            </svg>
            <span class="truncate">Assets</span>
            <span class="{{ $dot($active) }}"></span>
        </a>

        {{-- Livechat --}}
        @php $active = $is('chat.*'); @endphp
        <a href="{{ $rl('chat.index') }}" class="{{ $base }} {{ $active ? $on : $off }}">
            <svg class="{{ $ico($active) }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
            </svg>
            <span class="truncate">Livechat</span>
            <span class="{{ $dot($active) }}"></span>
        </a>

        {{-- SECTION: Feedback --}}
        <div class="px-6 mt-3 mb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
            Feedback
        </div>

        {{-- Evaluate --}}
        @php $active = $is('maintenance.requests.rating.evaluate'); @endphp
        <a href="{{ $rl('maintenance.requests.rating.evaluate') }}" class="{{ $base }} {{ $active ? $on : $off }}">
            <svg class="{{ $ico($active) }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <rect x="6" y="4" width="12" height="16" rx="2"/>
                <path d="M9 4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6H9V4.5z"/>
                <path d="M9 11h6"/>
                <path d="M9 14h3"/>
                <path d="m11 18 1-2 1 2"/>
            </svg>
            <span class="truncate">Evaluate</span>
            <span class="{{ $dot($active) }}"></span>
        </a>

        {{-- Technician Ratings --}}
        @php $active = $is('maintenance.requests.rating.technicians'); @endphp
        <a href="{{ $rl('maintenance.requests.rating.technicians') }}" class="{{ $base }} {{ $active ? $on : $off }}">
            <svg class="{{ $ico($active) }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 21h18"/>
                <rect x="5" y="10" width="3" height="7" rx="1"/>
                <rect x="10.5" y="7" width="3" height="10" rx="1"/>
                <rect x="16" y="4" width="3" height="13" rx="1"/>
            </svg>
            <span class="truncate">Technician Ratings</span>
            <span class="{{ $dot($active) }}"></span>
        </a>

        {{-- SECTION: Account --}}
        <div class="px-6 mt-3 mb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
            Account
        </div>

        @php $active = $is('profile.*'); @endphp
        <a href="{{ $rl('profile.show') }}" class="{{ $base }} {{ $active ? $on : $off }}">
            <svg class="{{ $ico($active) }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <circle cx="9" cy="7" r="4"/>
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="truncate">Profile</span>
            <span class="{{ $dot($active) }}"></span>
        </a>

        {{-- SECTION: Administration --}}
        @can('manage-users')
            <div class="px-6 mt-3 mb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
                Administration
            </div>

            @php $active = $is('admin.users.*'); @endphp
            <a href="{{ $rl('admin.users.index') }}" class="{{ $base }} {{ $active ? $on : $off }}">
                <svg class="{{ $ico($active) }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 11l2 2 4-4"/>
                </svg>
                <span class="truncate">Manage Users</span>
                <span class="{{ $dot($active) }}"></span>
            </a>
        @endcan

    </nav>
</div>
