<!doctype html>
<html lang="th" data-theme="govclean">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  {{-- CSRF token สำหรับ JS ที่อาจต้องดึงค่าไปใช้กับ fetch / AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="theme-color" content="#0E2B51">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <title>@yield('title', config('app.name', 'Asset Repair'))</title>

  @yield('head')

  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')
  @stack('head')

  <style>
    @font-face {
      font-family: 'Sarabun';
      font-style: normal;
      font-weight: 400;
      src: url('{{ asset('fonts/Sarabun-Regular.woff2') }}') format('woff2'),
           url('{{ asset('fonts/Sarabun-Regular.woff') }}') format('woff');
    }
    @font-face {
      font-family: 'Sarabun';
      font-style: normal;
      font-weight: 500;
      src: url('{{ asset('fonts/Sarabun-Medium.woff2') }}') format('woff2'),
           url('{{ asset('fonts/Sarabun-Medium.woff') }}') format('woff');
    }
    @font-face {
      font-family: 'Sarabun';
      font-style: normal;
      font-weight: 600;
      src: url('{{ asset('fonts/Sarabun-SemiBold.woff2') }}') format('woff2'),
           url('{{ asset('fonts/Sarabun-SemiBold.woff') }}') format('woff');
    }
    @font-face {
      font-family: 'Sarabun';
      font-style: normal;
      font-weight: 700;
      src: url('{{ asset('fonts/Sarabun-Bold.woff2') }}') format('woff2'),
           url('{{ asset('fonts/Sarabun-Bold.woff') }}') format('woff');
    }

    html, body {
      font-family: 'Sarabun', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
                   Roboto, "Helvetica Neue", Arial, sans-serif;
      font-weight: 400;
      letter-spacing: 0.2px;
    }

    :root{
      color-scheme: light;
      --nav-h: 86px;
    }

    body{ padding-top: var(--nav-h); }

    .sticky-under-topbar{ position: sticky; top: calc(var(--nav-h) + .5rem); z-index: 10; }

    @media (max-width: 992px){
      :root{ --nav-h: 72px; }
    }

    .layout{
      display:grid;
      grid-template-columns: 260px 1fr;
      min-height:100vh;
      transition:grid-template-columns .2s ease;
      background: hsl(var(--b2));
      color: hsl(var(--bc));
    }
    .content{ padding:1rem; }
    .sidebar{
      background:#ffffff;
      border-right:1px solid hsl(var(--b2));
      width:260px;
      transition:width .2s ease;
    }

    @media (min-width:1024px){
      .sidebar.compact{ width:180px !important; }
      .layout.with-compact{ grid-template-columns:180px 1fr !important; }
    }
    @media (min-width:1024px){
      .sidebar.collapsed{ width:76px !important; }
      .layout.with-collapsed{ grid-template-columns:76px 1fr !important; }
      .layout.with-expanded{ grid-template-columns:260px 1fr !important; }
    }

    @media (max-width:1024px){
      .layout{ grid-template-columns:1fr; }
      .sidebar{
        position:fixed; inset:var(--nav-h) auto 0 0; width:270px;
        transform:translateX(-100%); transition:.2s; z-index:50;
        box-shadow: 4px 0 24px rgba(0,0,0,.06);
      }
      .sidebar.open{ transform:translateX(0); }
      .backdrop{ position:fixed; inset:var(--nav-h) 0 0 0; background:rgba(0,0,0,.45); display:none; z-index:40; }
      .backdrop.show{ display:block; }
    }

    @media (min-width:1024px){
      .sidebar.collapsed.hover-expand{ width:260px !important; }
      .sidebar.collapsed.hover-expand .menu-text{ display:inline !important; }
      .sidebar.collapsed.hover-expand .menu-item{ justify-content:flex-start; gap:.75rem; }
      .sidebar.hover-expand{ box-shadow: 4px 0 12px rgba(0,0,0,.06); }
    }

    .sidebar .menu{ padding:.5rem 0; }
    .sidebar .menu-item{
      display:grid; grid-template-columns:48px 1fr; align-items:center;
      gap:.75rem; height:44px; line-height:1; padding:0 .75rem;
      white-space:nowrap; overflow:hidden;
      transition: grid-template-columns .25s ease, padding .25s ease, background .15s ease;
      color:hsl(var(--bc));
    }
    .sidebar .menu-item:hover{ background:hsl(var(--b2)); }
    .sidebar .menu-item .icon-wrap{
      width:48px; height:44px; display:inline-flex; align-items:center; justify-content:center;
      color: color-mix(in srgb, hsl(var(--bc)) 60%, transparent);
      position:relative;
    }
    .sidebar .menu-item .menu-text{ overflow:hidden; text-overflow:ellipsis; opacity:1; transition:opacity .18s ease; }

    @media (min-width:1024px){
      .sidebar.collapsed .menu-item{ grid-template-columns:48px 0px; gap:0; padding-inline:.5rem; }
      .sidebar.collapsed .menu-item .menu-text{ opacity:0; pointer-events:none; }

      .sidebar.compact .menu-item{ grid-template-columns:48px 1fr; padding-inline:.5rem; }
      .sidebar.compact .menu-item .menu-text{ font-size: .92rem; }
    }

    .footer{ background:#0E2B51; display:flex; align-items:center; justify-content:center; min-height:44px; padding:.75rem 1rem; font-weight:600; font-size:.875rem; color:#fff!important; }

    .loader-overlay{ position:fixed; inset:0; background:rgba(255,255,255,.6); backdrop-filter:blur(2px); display:flex; align-items:center; justify-content:center; z-index:99999; visibility:hidden; opacity:0; transition:opacity .2s ease, visibility .2s; }
    .loader-overlay.show{ visibility:visible; opacity:1; }
    .loader-spinner{ width:38px; height:38px; border:4px solid #0E2B51; border-top-color:transparent; border-radius:50%; animation:spin .7s linear infinite; }
    @keyframes spin{ to{ transform:rotate(360deg) } }

    /* --- เสริมให้ navbar/dropdown ไม่ถูกคลิปซ้อน --- */
    .app-navbar, .navbar-hero { z-index: 2000; } /* x-navbar fixed-top */
    .dropdown-menu { z-index: 2100; }

    /* ===== Global team tab motion ===== */
    @keyframes tabNudge { 0%,100% { transform: translateX(0); } 50% { transform: translateX(-2px); } }
    #teamTab { cursor: pointer; }
    #teamTab .tri { transition: transform .18s ease, border-color .18s ease; }
    #teamTab:hover .tri { transform: translateX(-1px); }
    #teamTab .tab-nudge { animation: tabNudge 1.8s ease-in-out infinite; }
  </style>
</head>

<body class="bg-base-200 text-base-content">
  @if (View::hasSection('navbar'))
    @yield('navbar')
  @else
    <x-navbar
      :appName="config('app.name', 'Phrapokklao - Information Technology Group')"
      subtitle="Asset Repair Management"
      logo="{{ asset('/images/logoppk.png') }}"
      :showLogout="Auth::check()"
    />
  @endif

  <div id="layout" class="layout" role="presentation">
    <aside id="side" class="sidebar" aria-label="Sidebar navigation">
      @hasSection('sidebar')
        @yield('sidebar')
      @else
        <x-sidebar />
      @endif
    </aside>

    <div id="backdrop" class="backdrop lg:hidden" aria-hidden="true"></div>

    <main id="main" class="content" role="main" tabindex="-1">
      @hasSection('page-header')
        <div class="mb-4 sticky-under-topbar">@yield('page-header')</div>
      @endif

      @if (session('ok'))
        <div class="mb-4 p-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-800">
          {{ session('ok') }}
        </div>
      @endif

      @yield('content')
    </main>
  </div>

  <x-footer />

  {{-- ===== Core JS ===== --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- ===== Sidebar & Loader ===== --}}
  <script>
    const btn = document.getElementById('btnSidebar');
    const side = document.getElementById('side');
    const bd   = document.getElementById('backdrop');

    function closeSide(){ side.classList.remove('open'); bd.classList.remove('show'); btn?.setAttribute('aria-expanded','false'); }
    function openSide(){ side.classList.add('open'); bd.classList.add('show'); btn?.setAttribute('aria-expanded','true'); }

    btn && btn.addEventListener('click', ()=> side.classList.contains('open') ? closeSide() : openSide());
    bd && bd.addEventListener('click', closeSide);

    const KEY = 'app.sidebar.collapsed';
    const layout = document.getElementById('layout');
    function applyCollapsedState(collapsed){
      if (collapsed){
        side.classList.add('collapsed');
        layout.classList.add('with-collapsed');
        layout.classList.remove('with-expanded');
      } else {
        side.classList.remove('collapsed','hover-expand');
        layout.classList.remove('with-collapsed','with-expanded');
      }
    }
    const saved = localStorage.getItem(KEY);
    if (saved === null) {
      const isDesktop = window.matchMedia('(min-width: 1024px)').matches;
      applyCollapsedState(isDesktop);
      localStorage.setItem(KEY, isDesktop ? '1' : '0');
    } else {
      applyCollapsedState(saved === '1');
    }

    let hoverBound = false, hoverTimeout;
    function onEnter(){
      if (side.classList.contains('collapsed')) {
        clearTimeout(hoverTimeout);
        side.classList.add('hover-expand');
        layout.classList.add('with-expanded');
        layout.classList.remove('with-collapsed');
      }
    }
    function onLeave(){
      if (side.classList.contains('collapsed')) {
        hoverTimeout = setTimeout(()=>{
          side.classList.remove('hover-expand');
          layout.classList.remove('with-expanded');
          layout.classList.add('with-collapsed');
        },150);
      }
    }
    function bindHover(){
      if (hoverBound) return;
      side.addEventListener('mouseenter', onEnter);
      side.addEventListener('mouseleave', onLeave);
      hoverBound = true;
    }
    function unbindHover(){
      if (!hoverBound) return;
      side.removeEventListener('mouseenter', onEnter);
      side.removeEventListener('mouseleave', onLeave);
      hoverBound = false;
      side.classList.remove('hover-expand');
      layout.classList.remove('with-expanded');
    }

    const mql = window.matchMedia('(max-width: 1024px)');
    function handleResize(e){
      if (e.matches){
        unbindHover();
        side.classList.remove('hover-expand');
        layout.classList.remove('with-expanded');
      } else {
        bindHover();
        const s = localStorage.getItem(KEY);
        applyCollapsedState(s === '1');
      }
    }
    handleResize(mql);
    mql.addEventListener?.('change', handleResize);

    window.Loader = {
      show(){ document.getElementById('loaderOverlay')?.classList.add('show') },
      hide(){ document.getElementById('loaderOverlay')?.classList.remove('show') }
    };

    document.addEventListener('DOMContentLoaded', () => Loader.hide());
    document.addEventListener('click', (e) => {
      // อย่าโชว์ Loader หากคลิกอยู่ใน Chat Widget (ลด side-effects ระหว่าง polling)
      if (e.target.closest('#chatWidgetRoot')) return;
      if (e.defaultPrevented) return;
      const a = e.target.closest('a'); if (!a) return;
      const href = a.getAttribute('href') || '';
      const noLoader = a.hasAttribute('data-no-loader') || a.getAttribute('target');
      const isAnchorSamePage = href.startsWith('#');
      if (!noLoader && href && !isAnchorSamePage) Loader.show();
    });
    document.addEventListener('submit', (e) => {
      const form = e.target;
      // อย่าโชว์ Loader หากมีการ preventDefault ใน handler อื่น (เช่น client-side validate)
      if (e.defaultPrevented) return;
      if (form instanceof HTMLFormElement && !form.hasAttribute('data-no-loader')) Loader.show();
    });
    window.addEventListener('beforeunload', () => Loader.show());
  </script>

  {{-- ===== Init Bootstrap Dropdowns (หลังโหลด bundle แล้ว) ===== --}}
  <script>
    (function () {
      if (!window.bootstrap || !window.bootstrap.Dropdown) return;
      document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
        if (!bootstrap.Dropdown.getInstance(el)) {
          new bootstrap.Dropdown(el, { autoClose: 'outside' });
        }
      });
    })();
  </script>

  {{-- ===== Global Team Drawer (accessible on all authenticated pages) ===== --}}
  @if(Auth::check())
    @php
      // Query team (admins + technicians) with active workload counts
      $globalTeam = \App\Models\User::query()
        ->whereIn('role', \App\Models\User::teamRoles())
        ->withCount([
          'assignedRequests as active_count' => fn($q) => $q->whereNotIn('status',[ 'resolved','closed','cancelled' ]),
          'assignedRequests as total_count',
        ])
        ->orderBy('name')
        ->get(['id','name','role']);
    @endphp

    {{-- Triangle Tab Trigger --}}
    @if($globalTeam->count())
      <button id="teamTab" type="button" onclick="toggleTeamDrawer()" aria-label="เปิด/ปิดภาระงานทีม" aria-expanded="false"
        class="fixed top-1/2 right-0 -translate-y-1/2 z-[2202] group select-none">
        <!-- Closed state: point inward (left) -->
        <span id="teamTabTriClosed" class="tri tri-closed inline-block w-0 h-0 border-y-[12px] border-y-transparent border-r-[18px] border-r-indigo-600 group-hover:border-r-indigo-700 tab-nudge"></span>
        <!-- Open state: point outward (right) -->
        <span id="teamTabTriOpen" class="tri tri-open inline-block w-0 h-0 border-y-[12px] border-y-transparent border-l-[18px] border-l-indigo-600 group-hover:border-l-indigo-700 hidden"></span>
      </button>

      {{-- Overlay --}}
  <div id="teamOverlay" class="fixed inset-0 bg-black/40 z-[2200] hidden" onclick="closeTeamDrawer()" aria-hidden="true"></div>

      {{-- Drawer --}}
      <aside id="teamDrawer" class="fixed top-0 right-0 h-full w-[360px] max-w-[90vw] bg-white shadow-xl z-[2201] transform translate-x-full transition-transform duration-300" aria-label="ภาระงานทีม">
        <div class="h-full flex flex-col">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M3 7h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/>
                <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
              </svg>
              <h3 class="text-sm font-semibold text-gray-900">Technician Jobs</h3>
            </div>
            <button type="button" onclick="closeTeamDrawer()" class="p-1.5 rounded-md hover:bg-gray-100" aria-label="ปิด">
              <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-3 overflow-y-auto flex-1" id="teamDrawerScroll">
            <div class="space-y-2">
              @foreach($globalTeam as $member)
                @php
                  $initial = \Illuminate\Support\Str::of($member->name)->substr(0,1)->upper();
                  $roleClasses = method_exists($member,'isSupervisor') && $member->isSupervisor()
                    ? 'bg-indigo-100 text-indigo-700 ring-indigo-200'
                    : 'bg-emerald-100 text-emerald-700 ring-emerald-200';
                @endphp
                <a href="{{ route('repairs.my_jobs', array_merge(request()->except('page'), ['filter'=>'all','tech'=>$member->id])) }}" class="group flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                  <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold ring-1 {{ $roleClasses }}">{{ $initial }}</span>
                    <div>
                      <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-700">{{ $member->name }}</div>
                      <div class="text-xs text-gray-500">บทบาท: {{ $member->role_label ?? ucfirst($member->role) }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded">{{ $member->active_count ?? 0 }}</span>
                    <svg class="h-4 w-4 text-gray-500 group-hover:text-indigo-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                  </div>
                </a>
              @endforeach
            </div>
          </div>
          <!-- footer removed per request -->
        </div>
      </aside>
    @endif
  @endif

  <script>
    // Global Team Drawer logic (shared across pages)
    let teamDrawerOpen = false;
    function toggleTeamDrawer(){
      if(teamDrawerOpen) closeTeamDrawer(); else openTeamDrawer();
    }
    function openTeamDrawer(){
      const d = document.getElementById('teamDrawer');
      const o = document.getElementById('teamOverlay');
      const tab = document.getElementById('teamTab');
      const triC = document.getElementById('teamTabTriClosed');
      const triO = document.getElementById('teamTabTriOpen');
      if(!d||!o) return; d.classList.remove('translate-x-full'); o.classList.remove('hidden'); teamDrawerOpen = true;
      if(tab){ tab.setAttribute('aria-expanded','true'); }
      if(triC && triO){ triC.classList.add('hidden'); triO.classList.remove('hidden'); }
    }
    function closeTeamDrawer(){
      const d = document.getElementById('teamDrawer');
      const o = document.getElementById('teamOverlay');
      const tab = document.getElementById('teamTab');
      const triC = document.getElementById('teamTabTriClosed');
      const triO = document.getElementById('teamTabTriOpen');
      if(!d||!o) return; d.classList.add('translate-x-full'); o.classList.add('hidden'); teamDrawerOpen = false;
      if(tab){ tab.setAttribute('aria-expanded','false'); }
      if(triC && triO){ triC.classList.remove('hidden'); triO.classList.add('hidden'); }
    }
    // Swipe gestures (open from right edge, close by swiping right)
    let tdStartX=null, tdStartY=null;
    document.addEventListener('touchstart', e=>{ const t=e.touches[0]; tdStartX=t.clientX; tdStartY=t.clientY; }, {passive:true});
    document.addEventListener('touchmove', e=>{
      if(tdStartX===null) return; const t=e.touches[0]; const dx=t.clientX-tdStartX; const dy=t.clientY-tdStartY; if(Math.abs(dx)<Math.abs(dy)) return;
      if(!teamDrawerOpen && tdStartX > (window.innerWidth - 28) && dx < -40){ openTeamDrawer(); tdStartX=null; }
      if(teamDrawerOpen && dx > 70){ closeTeamDrawer(); tdStartX=null; }
    }, {passive:true});
    document.addEventListener('keydown', e=>{ if(e.key==='Escape' && teamDrawerOpen) closeTeamDrawer(); });
    // Draggable tab (vertical only)
    (function(){
      const tab = document.getElementById('teamTab'); if(!tab) return;
      const KEY='ui.teamTab.top';
      const saved = localStorage.getItem(KEY);
      if (saved) {
        tab.style.top = saved+'px';
        tab.classList.remove('top-1/2','-translate-y-1/2');
      }
      let dragging=false, startY=0, startTop=0;
      function onDown(ev){
        dragging=true; startY = (ev.touches?ev.touches[0].clientY:ev.clientY);
        startTop = tab.getBoundingClientRect().top; // relative to viewport
        // remove centering classes when user starts dragging
        tab.classList.remove('top-1/2','-translate-y-1/2');
        ev.preventDefault?.();
      }
      function onMove(ev){ if(!dragging) return; const y=(ev.touches?ev.touches[0].clientY:ev.clientY); let top = startTop + (y - startY);
        const min = (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h'))||72) + 10;
        const max = window.innerHeight - 48; if(top<min) top=min; if(top>max) top=max; tab.style.top = top+'px'; }
      function onUp(){ if(!dragging) return; dragging=false; const top=parseFloat(tab.style.top||''); if(top) localStorage.setItem(KEY, String(top)); }
      tab.addEventListener('mousedown', onDown); document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp);
      tab.addEventListener('touchstart', onDown, {passive:false}); document.addEventListener('touchmove', onMove, {passive:false}); document.addEventListener('touchend', onUp);
    })();
    // Expose for other scripts
    window.openTeamDrawer = openTeamDrawer; window.closeTeamDrawer = closeTeamDrawer;
  </script>

  @stack('scripts')

  <script>
    // ===== Global diagnostic for searchable select components =====
    window.ssDiag = function(print = true){
      const nodes = Array.from(document.querySelectorAll('[data-ss]'));
      const rows = nodes.map(n => ({
        id: n.getAttribute('data-ss-id'),
        variant: n.getAttribute('data-ss-variant'),
        inline: n.getAttribute('data-ss-inline'),
        bound: n.getAttribute('data-ss-bound') === '1',
        fail: n.getAttribute('data-ss-fail') || '',
        options: n.querySelectorAll('[data-ss-option]').length,
        value: n.querySelector('[data-ss-input]')?.value || '',
      }));
      if (print) {
        console.group('[ssDiag] Searchable Select Components');
        console.table(rows);
        console.log('Total:', rows.length, 'Bound:', rows.filter(r=>r.bound).length, 'Failed:', rows.filter(r=>r.fail).length);
        console.groupEnd();
      }
      return rows;
    };
    document.addEventListener('DOMContentLoaded', () => {
      // Auto-run short summary
      const rows = window.ssDiag(false);
      console.info(`[ssDiag] components=${rows.length} bound=${rows.filter(r=>r.bound).length} failed=${rows.filter(r=>r.fail).length}`);
      if (rows.some(r=>!r.bound)) {
        console.warn('[ssDiag] Some components were not bound. Run ssDiag() for details.');
      }
    });
  </script>

  <div id="loaderOverlay" class="loader-overlay" aria-hidden="true">
    <div class="loader-spinner" role="status" aria-label="กำลังโหลด"></div>
  </div>

  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" defer></script>
  <x-toast />

  @includeWhen(Auth::check(), 'partials.chat-fab')

  {{-- NOTE: เดิมมีการ @push styles/scripts สำหรับ CropperJS หลังจาก @stack ถูก render แล้วใน layout เดียวกัน
    ทำให้ไฟล์ไม่ถูกโหลดจริง หากต้องใช้ Cropper ให้ include ภายในหน้าเฉพาะที่ต้องใช้แทน
    (ตัวอย่างเช่น resources/views/profile/edit.blade.php มีการ include ไว้อย่างถูกที่แล้ว) --}}
</body>
</html>
