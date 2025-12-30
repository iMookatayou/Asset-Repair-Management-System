<!doctype html>
<html lang="th" data-theme="govclean">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="theme-color" content="#0E2B51">

  <script>
  (function () {
    try {
      if (sessionStorage.getItem('ui.sidebarIntro.next') === '1') {
        document.documentElement.classList.add('intro-pending');
      }
    } catch (e) {}
  })();
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <title>@yield('title', config('app.name', 'Asset Repair'))</title>

  @yield('head')

  <script>
    window.__playSidebarIntro = @json(session('play_sidebar_intro', false));
  </script>

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
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: 'Sarabun', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
                     Roboto, "Helvetica Neue", Arial, sans-serif;
        font-weight: 400;
        letter-spacing: 0.2px;
    }

    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding-top: 0 !important;
    }

    :root{
      color-scheme: light;
      --nav-h: 80px;
      --topbar-h: calc(var(--nav-h) + 8px);
    }

    @media (max-width: 992px){
      :root{ --nav-h: 72px; }
    }

    .content{
      padding: 1rem 1rem 0.25rem;
    }

    .sticky-under-topbar{
      position: sticky;
      top: var(--nav-h);
      z-index: 10;
    }
    .sticky-under-topbar > *:first-child { margin-top: 0 !important; }

    #main .sticky-under-topbar + * { margin-top: 6rem; }

    .layout{
        display: grid;
        grid-template-columns: 260px 1fr;
        flex: 1 0 auto;
        min-height: 0 !important;
        transition: grid-template-columns .2s ease;
        background: #ffffff;
        color: hsl(var(--bc));
    }

    .sidebar{
        background:#ffffff;
        border-right:1px solid hsl(var(--b2));
        width:260px;
        transition:width .2s ease;
    }

    .sidebar-logo {
        height: var(--nav-h);
        display: flex;
        align-items: center;
    }

    @media (min-width:1024px){
        .sidebar{
          position: static;
          top: auto;
          align-self: stretch;
          height: auto;
          overflow-y: visible;
        }

        .sidebar.compact{ width:180px !important; }
        .layout.with-compact{ grid-template-columns:180px 1fr !important; }

        .sidebar.collapsed{ width:76px !important; }
        .layout.with-collapsed{ grid-template-columns:76px 1fr !important; }
        .layout.with-expanded{ grid-template-columns:260px 1fr !important; }

        .sidebar.collapsed.hover-expand{ width:260px !important; }
        .sidebar.collapsed.hover-expand .menu-text{ display:inline !important; }
        .sidebar.collapsed.hover-expand .menu-item{ justify-content:flex-start; gap:.75rem; }
        .sidebar.hover-expand{ box-shadow: 4px 0 12px rgba(0,0,0,.06); }
    }

    @media (max-width:1024px){
        .layout{ grid-template-columns:1fr; }

        .sidebar{
          position:fixed;
          inset:var(--nav-h) auto 0 0;
          width:270px;
          transform:translateX(-100%);
          transition:.2s;
          z-index:50;
          box-shadow: 4px 0 24px rgba(0,0,0,.06);
          max-height: calc(100vh - var(--nav-h));
          overflow-y: auto;
        }
        .sidebar.open{ transform:translateX(0); }

        .backdrop{
          position:fixed;
          inset:var(--nav-h) 0 0 0;
          background:rgba(0,0,0,.45);
          display:none;
          z-index:40;
        }
        .backdrop.show{ display:block; }
    }

    .sidebar .menu{ padding:.5rem 0; }

    .sidebar .menu-item{
        display:grid;
        grid-template-columns:48px 1fr;
        align-items:center;
        gap:.75rem;
        height:44px;
        line-height:1;
        padding:0 .75rem;
        white-space:nowrap;
        overflow:hidden;
        transition:
          grid-template-columns .25s ease,
          padding .25s ease,
          background .15s ease;
        color:hsl(var(--bc));
    }
    .sidebar .menu-item:hover{ background:hsl(var(--b2)); }

    .sidebar .menu-item .icon-wrap{
        width:48px;
        height:44px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        color: color-mix(in srgb, hsl(var(--bc)) 60%, transparent);
        position:relative;
    }

    .sidebar .menu-item .menu-text{
        overflow:hidden;
        text-overflow:ellipsis;
        opacity:1;
        transition:opacity .18s ease;
    }

    @media (min-width:1024px){
        .sidebar.collapsed .menu-item{
          grid-template-columns:48px 0px;
          gap:0;
          padding-inline:.5rem;
        }
        .sidebar.collapsed .menu-item .menu-text{
          opacity:0;
          pointer-events:none;
        }

        .sidebar.compact .menu-item{
          grid-template-columns:48px 1fr;
          padding-inline:.5rem;
        }
        .sidebar.compact .menu-item .menu-text{
          font-size: .92rem;
        }
    }

    .brand-en {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
                    Roboto, "Helvetica Neue", Arial, sans-serif;
        letter-spacing: 0.08em;
    }

    .footer-hero {
      background-color: #0F2D5C;
      color: #EAF2FF;
      font-family: 'Sarabun', system-ui, sans-serif;
      box-shadow: 0 -4px 20px rgba(0,0,0,0.2);
      border-top: 1px solid rgba(255,255,255,.12);
      margin: 0;
    }

    .loader-overlay{
        position:fixed;
        inset:0;
        background:rgba(255,255,255,.6);
        backdrop-filter:blur(2px);
        display:flex;
        align-items:center;
        justify-content:center;
        z-index:99999;
        visibility:hidden;
        opacity:0;
        transition:opacity .2s ease, visibility .2s;
    }
    .loader-overlay.show{ visibility:visible; opacity:1; }
    .loader-spinner{
        width:38px;
        height:38px;
        border:4px solid #0E2B51;
        border-top-color:transparent;
        border-radius:50%;
        animation:spin .7s linear infinite;
    }
    @keyframes spin{ to{ transform:rotate(360deg) } }

    .app-navbar,
    .navbar-hero { z-index: 2000; }
    .dropdown-menu { z-index: 2100; }

    @keyframes tabNudge {
        0%,100% { transform: translateX(0); }
        50%     { transform: translateX(-2px); }
    }
    #teamTab { cursor: pointer; right: .8rem; }
    #teamTab .tri { transition: transform .18s ease, border-color .18s ease; }
    #teamTab:hover .tri { transform: translateX(-1px); }
    #teamTab .tab-nudge { animation: tabNudge 1.8s ease-in-out infinite; }

    .ts-wrapper{ position:relative !important; width:100% !important; }
    .ts-wrapper.single{ position:relative !important; }

    .ts-wrapper.single .ts-control{
    height:44px !important;
    min-height:44px !important;
    border-radius:0.375rem !important;
    border:1px solid #cbd5e1 !important;
    background:#fff !important;
    box-shadow:none !important;

    padding-left:2.5rem !important;
    padding-right:2.25rem !important;
    font-size:.875rem !important;
    line-height:1.25rem !important;

    display:flex !important;
    align-items:center !important;
    }

    .ts-wrapper.single .ts-control input,
    .ts-wrapper.single .ts-control .item{
    font-size:.875rem !important;
    line-height:1.25rem !important;
    }

    .ts-wrapper.single .ts-control:focus-within{
    border-color:#059669 !important;
    box-shadow:0 0 0 2px rgba(16,185,129,.20) !important;
    }

    .ts-wrapper.single::before{
    content:"" !important;
    position:absolute !important;
    left:.75rem !important;
    top:50% !important;
    transform:translateY(-50%) !important;
    width:16px !important;
    height:16px !important;
    pointer-events:none !important;
    z-index:50 !important;
    opacity:.85 !important;

    background-repeat:no-repeat !important;
    background-position:center !important;
    background-size:16px 16px !important;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='M21 21l-4.3-4.3'/%3E%3C/svg%3E") !important;
    }

    .ts-wrapper.single .ts-control::after{ display:none !important; }
    .ts-dropdown{
    border-radius:0.5rem !important;
    border:1px solid #e2e8f0 !important;
    box-shadow:0 10px 25px rgba(0,0,0,.08) !important;
    z-index:4000 !important;
    }
    .ts-dropdown .option{
    padding:.5rem .75rem !important;
    font-size:.875rem !important;
    }
    .ts-dropdown .option.active{
    background:#ecfdf5 !important;
    color:#047857 !important;
    }

    /* ✅ Intro Lock + Fade-In (เพิ่มใหม่) */
    html.intro-pending body { overflow: hidden; }

    html.intro-pending #layout,
    html.intro-pending .app-navbar,
    html.intro-pending .navbar-hero,
    html.intro-pending footer,
    html.intro-pending #teamTab,
    html.intro-pending #loaderOverlay {
      opacity: 0;
      pointer-events: none;
    }

    #layout,
    .app-navbar,
    .navbar-hero,
    footer {
      transition: opacity .55s ease;
    }
  </style>
</head>

<body class="bg-white text-base-content">
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
        <div class="sticky-under-topbar">@yield('page-header')</div>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const btn = document.getElementById('btnSidebar');
    const side = document.getElementById('side');
    const bd   = document.getElementById('backdrop');

    function closeSide(){
      side.classList.remove('open');
      bd.classList.remove('show');
      btn?.setAttribute('aria-expanded','false');
    }
    function openSide(){
      side.classList.add('open');
      bd.classList.add('show');
      btn?.setAttribute('aria-expanded','true');
    }

    btn && btn.addEventListener('click', ()=> side.classList.contains('open') ? closeSide() : openSide());
    bd && bd.addEventListener('click', closeSide);

    const KEY = 'app.sidebar.collapsed';
    const layout = document.getElementById('layout');

    localStorage.setItem(KEY, '0');
    side.classList.remove('collapsed', 'hover-expand');
    layout.classList.remove('with-collapsed', 'with-expanded');

    function applyCollapsedState(collapsed)

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
      if (e.defaultPrevented) return;
      if (form instanceof HTMLFormElement && !form.hasAttribute('data-no-loader')) Loader.show();
    });
    window.addEventListener('beforeunload', () => Loader.show());

    // ✅ กัน Loader โผล่ระหว่าง intro
    (function(){
      if (!window.Loader) return;
      const _show = window.Loader.show.bind(window.Loader);
      window.Loader.show = function(){
        if (document.documentElement.classList.contains('intro-pending')) return;
        _show();
      };
    })();
  </script>

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

  @if(Auth::check())
    @php
      $globalTeam = \App\Models\User::query()
        ->whereIn('role', \App\Models\User::teamRoles())
        ->withCount([
          'assignedRequests as active_count' => function ($q) {
            $q->whereNotIn('maintenance_requests.status', [
                \App\Models\MaintenanceRequest::STATUS_RESOLVED,
                \App\Models\MaintenanceRequest::STATUS_CLOSED,
                \App\Models\MaintenanceRequest::STATUS_CANCELLED,
            ]);
          },
          'assignedRequests as total_count',
        ])
        ->orderBy('name')
        ->get(['id','name','role']);
    @endphp

    @if($globalTeam->count())
      <button id="teamTab"
        class="fixed top-1/2 right-2 -translate-y-1/2 z-[2202] group select-none
               w-10 h-10 bg-indigo-600 text-white rounded-full shadow-lg
               flex items-center justify-center hover:bg-indigo-700 transition"
        onclick="toggleTeamDrawer()"
        aria-expanded="false">
        <svg id="teamTabIconClosed" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
        <svg id="teamTabIconOpen" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 6l6 6-6 6"/>
        </svg>
      </button>

      <div id="teamOverlay" class="fixed inset-0 bg-black/40 z-[2200] hidden" onclick="closeTeamDrawer()" aria-hidden="true"></div>

      <aside id="teamDrawer" class="fixed top-0 right-0 h-full w-[360px] max-w-[90vw] bg-white shadow-xl z-[2201] transform translate-x-full transition-transform duration-300" aria-label="ภาระงานทีม">
        <div class="h-full flex flex-col">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-semibold text-gray-900">Technician</h3>
            </div>
            <button type="button" onclick="closeTeamDrawer()" class="p-1.5 rounded-md hover:bg-gray-100" aria-label="ปิด">
              <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 18L18 6M6 6l12 12"/>
              </svg>
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

                <a href="{{ route('repairs.my_jobs', array_merge(request()->except('page'), ['filter'=>'all','tech'=>$member->id])) }}"
                   class="group flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                  <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold ring-1 {{ $roleClasses }}">{{ $initial }}</span>
                    <div>
                      <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-700">{{ $member->name }}</div>
                      <div class="text-xs text-gray-500">บทบาท: {{ $member->role_label ?? ucfirst($member->role) }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded">{{ $member->active_count ?? 0 }}</span>
                    <svg class="h-4 w-4 text-gray-500 group-hover:text-indigo-700" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </a>
              @endforeach
            </div>
          </div>
        </div>
      </aside>
    @endif
  @endif

  <script>
    let teamDrawerOpen = false;

    function toggleTeamDrawer(){ teamDrawerOpen ? closeTeamDrawer() : openTeamDrawer(); }

    function openTeamDrawer(){
      const d = document.getElementById('teamDrawer');
      const o = document.getElementById('teamOverlay');
      const tab = document.getElementById('teamTab');
      const iconC = document.getElementById('teamTabIconClosed');
      const iconO = document.getElementById('teamTabIconOpen');
      if (!d || !o) return;

      d.classList.remove('translate-x-full');
      o.classList.remove('hidden');
      teamDrawerOpen = true;

      tab?.setAttribute('aria-expanded', 'true');
      iconC?.classList.add('hidden');
      iconO?.classList.remove('hidden');
    }

    function closeTeamDrawer(){
      const d = document.getElementById('teamDrawer');
      const o = document.getElementById('teamOverlay');
      const tab = document.getElementById('teamTab');
      const iconC = document.getElementById('teamTabIconClosed');
      const iconO = document.getElementById('teamTabIconOpen');
      if (!d || !o) return;

      d.classList.add('translate-x-full');
      o.classList.add('hidden');
      teamDrawerOpen = false;

      tab?.setAttribute('aria-expanded', 'false');
      iconO?.classList.add('hidden');
      iconC?.classList.remove('hidden');
    }

    let tdStartX=null, tdStartY=null;
    document.addEventListener('touchstart', e=>{
      const t=e.touches[0]; tdStartX=t.clientX; tdStartY=t.clientY;
    }, {passive:true});
    document.addEventListener('touchmove', e=>{
      if(tdStartX===null) return;
      const t=e.touches[0];
      const dx=t.clientX-tdStartX;
      const dy=t.clientY-tdStartY;
      if(Math.abs(dx)<Math.abs(dy)) return;
      if(!teamDrawerOpen && tdStartX > (window.innerWidth - 28) && dx < -40){
        openTeamDrawer(); tdStartX=null;
      }
      if(teamDrawerOpen && dx > 70){
        closeTeamDrawer(); tdStartX=null;
      }
    }, {passive:true});
    document.addEventListener('keydown', e=>{
      if(e.key==='Escape' && teamDrawerOpen) closeTeamDrawer();
    });

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
        dragging=true;
        startY = (ev.touches?ev.touches[0].clientY:ev.clientY);
        startTop = tab.getBoundingClientRect().top;
        tab.classList.remove('top-1/2','-translate-y-1/2');
        ev.preventDefault?.();
      }
      function onMove(ev){
        if(!dragging) return;
        const y=(ev.touches?ev.touches[0].clientY:ev.clientY);
        let top = startTop + (y - startY);
        const min = (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h'))||72) + 10;
        const max = window.innerHeight - 48;
        if(top<min) top=min;
        if(top>max) top=max;
        tab.style.top = top+'px';
      }
      function onUp(){
        if(!dragging) return;
        dragging=false;
        const top=parseFloat(tab.style.top||'');
        if(top) localStorage.setItem(KEY, String(top));
      }
      tab.addEventListener('mousedown', onDown);
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', onUp);
      tab.addEventListener('touchstart', onDown, {passive:false});
      document.addEventListener('touchmove', onMove, {passive:false});
      document.addEventListener('touchend', onUp);
    })();

    window.openTeamDrawer = openTeamDrawer;
    window.closeTeamDrawer = closeTeamDrawer;
  </script>

  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
  <script>
    (function () {
      function initTomSelect(root) {
        root = root || document;

        root.querySelectorAll('select.ts-basic, select.ts-department').forEach(function (el) {
          if (el.tomselect) return;

          const placeholder =
            el.getAttribute('data-placeholder') ||
            el.getAttribute('placeholder') ||
            '— ไม่ระบุ —';

          new TomSelect(el, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 2000,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: placeholder,
            searchField: ['text'],
          });
        });
      }

      document.addEventListener('DOMContentLoaded', function () {
        initTomSelect(document);
      });

      // เผื่อ turbo/livewire
      document.addEventListener('turbo:load', function () {
        initTomSelect(document);
      });
      document.addEventListener('livewire:navigated', function () {
        initTomSelect(document);
      });

      window.initTomSelect = initTomSelect;
    })();
  </script>

  @yield('scripts')
  @stack('scripts')

  <div id="loaderOverlay" class="loader-overlay" aria-hidden="true">
    <div class="loader-spinner" role="status" aria-label="กำลังโหลด"></div>
  </div>

  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" defer></script>

  <!-- ✅ Toast จะถูก “หน่วง” ด้วย logic ใน component ที่ให้ด้านล่าง -->
  <x-toast />

  @includeWhen(Auth::check(), 'partials.chat-fab')
</body>

<script>
(function () {
  function forceHideLoader() {
    const el = document.getElementById('loaderOverlay');
    if (el) el.classList.remove('show');
  }

  document.addEventListener('DOMContentLoaded', forceHideLoader);
  window.addEventListener('pageshow', forceHideLoader); // back/forward cache
  document.addEventListener('turbo:load', forceHideLoader);
  document.addEventListener('livewire:navigated', forceHideLoader);

  // safety net กันค้าง 100%
  setTimeout(forceHideLoader, 600);
})();
</script>

</html>
