{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="th" data-theme="govclean">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="theme-color" content="#0E2B51">
  <title>@yield('title', config('app.name', 'Asset Repair'))</title>

  {{-- Page-level extra heads (favicons, meta, etc.) --}}
  @yield('head')

  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')

  @stack('head')

  <style>
    /* ===== เพิ่มฟอนต์ Sarabun (Local Fonts) ===== */
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
      --topbar-h: 96px;
    }

    /* ===== Layout grid ===== */
    .layout{
      display:grid;
      grid-template-columns: 260px 1fr;
      min-height:100vh;
      transition:grid-template-columns .2s ease;
      background: hsl(var(--b2));
      color: hsl(var(--bc));
    }
    .content{ padding:1rem; }

    /* ===== Sidebar (3 โหมด: expanded / compact / collapsed) ===== */
    .sidebar{
      background:#ffffff;
      border-right:1px solid hsl(var(--b2));
      width:260px;             /* expanded */
      transition:width .2s ease;
    }
    /* Compact (180px) */
    @media (min-width:1024px){
      .sidebar.compact{ width:180px !important; }
      .layout.with-compact{ grid-template-columns:180px 1fr !important; }
    }
    /* Collapsed (76px) */
    @media (min-width:1024px){
      .sidebar.collapsed{ width:76px !important; }
      .layout.with-collapsed{ grid-template-columns:76px 1fr !important; }
      .layout.with-expanded{ grid-template-columns:260px 1fr !important; }
    }

    /* Mobile: off-canvas */
    @media (max-width:1024px){
      .layout{ grid-template-columns:1fr; }
      .sidebar{
        position:fixed; inset:0 auto 0 0; width:270px;
        transform:translateX(-100%); transition:.2s; z-index:50;
        box-shadow: 4px 0 24px rgba(0,0,0,.06);
      }
      .sidebar.open{ transform:translateX(0); }
      .backdrop{ position:fixed; inset:0; background:rgba(0,0,0,.45); display:none; z-index:40; }
      .backdrop.show{ display:block; }
    }

    /* Hover expand (desktop) */
    @media (min-width:1024px){
      .sidebar.collapsed.hover-expand{ width:260px !important; }
      .sidebar.collapsed.hover-expand .menu-text{ display:inline !important; }
      .sidebar.collapsed.hover-expand .menu-item{ justify-content:flex-start; gap:.75rem; }
      .sidebar.hover-expand{ box-shadow: 4px 0 12px rgba(0,0,0,.06); }
    }

    /* Sidebar menu items */
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

    /* ปรับคอลัมน์ตามโหมด */
    @media (min-width:1024px){
      .sidebar.collapsed .menu-item{ grid-template-columns:48px 0px; gap:0; padding-inline:.5rem; }
      .sidebar.collapsed .menu-item .menu-text{ opacity:0; pointer-events:none; }

      .sidebar.compact .menu-item{ grid-template-columns:48px 1fr; padding-inline:.5rem; }
      .sidebar.compact .menu-item .menu-text{ font-size: .92rem; }
    }

    /* ===== Topbar / Footer / Loader unchanged … ===== */
    .topbar--hero{ position:sticky; top:0; z-index:30; background:#0E2B51; color:#fff; border-bottom:1px solid rgba(255,255,255,.08); box-shadow:0 10px 30px rgba(0,0,0,.18); backdrop-filter:saturate(120%) blur(6px); }
    .topbar--inner{ min-height:var(--topbar-h); display:flex; align-items:center; }
    .brand-logo{ width:60px; height:60px; object-fit:contain; }
    .brand-title{ font-weight:900; letter-spacing:.2px; line-height:1; color:#fff; }
    .brand-sub{ font-size:.82rem; color:#d8e7f9; margin-top:2px; }
    .glass-btn{ background: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.28); color:#fff; }
    .glass-btn:hover{ background: rgba(255,255,255,.18); }
    .footer{ background:#0E2B51; display:flex; align-items:center; justify-content:center; min-height:44px; padding:.75rem 1rem; font-weight:600; font-size:.875rem; color:#fff!important; }
    .loader-overlay{ position:fixed; inset:0; background:rgba(255,255,255,.6); backdrop-filter:blur(2px); display:flex; align-items:center; justify-content:center; z-index:99999; visibility:hidden; opacity:0; transition:opacity .2s ease, visibility .2s; }
    .loader-overlay.show{ visibility:visible; opacity:1; }
    .loader-spinner{ width:38px; height:38px; border:4px solid #0E2B51; border-top-color:transparent; border-radius:50%; animation:spin .7s linear infinite; }
    @keyframes spin{ to{ transform:rotate(360deg) } }
    .sticky-under-topbar{ position: sticky; top: calc(var(--topbar-h) + .5rem); z-index: 10; }
  </style>
</head>

<body class="bg-base-200 text-base-content">
  {{-- Skip to content (a11y) --}}
  <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2
     focus:bg-white focus:text-black focus:px-3 focus:py-2 focus:rounded-md focus:shadow">
    ข้ามไปยังเนื้อหา
  </a>

  {{-- ======= TOPBAR ======= --}}
  <header class="topbar--hero" role="banner">
    <div class="topbar--inner px-4 gap-4 flex-wrap">
      {{-- Left: logo + title + badges --}}
      <div class="flex items-center gap-4 flex-wrap">
        <a href="{{ url('/') }}" class="flex items-center gap-3 no-underline">
          <img src="{{ asset('/images/logoppk.png') }}" alt="ตราหน่วยงาน" class="brand-logo" width="60" height="60" decoding="async">
          <div class="leading-tight">
            <div class="brand-title text-xl md:text-2xl">
              {{ config('app.name','Asset Repair Dashboard') }}
            </div>
            <div class="brand-sub">Asset Repair Management</div>
          </div>
        </a>

        <div class="hidden md:flex items-center gap-2 text-[13px]">
          @hasSection('topbadges')
            @yield('topbadges')
          @else
            <span class="status-badge status-total">Total: <strong>300</strong></span>
            <span class="status-badge status-pending">Pending: <strong>103</strong></span>
            <span class="status-badge status-progress">In&nbsp;progress: <strong>107</strong></span>
            <span class="status-badge status-done">Completed: <strong>90</strong></span>
          @endif
        </div>
      </div>

      {{-- Right: mobile menu + logout --}}
      <div class="flex items-center gap-2 ml-auto">
        <button id="btnSidebar"
                class="lg:hidden inline-flex items-center px-2 py-1 rounded-md glass-btn transition-colors"
                aria-controls="side" aria-expanded="false" title="เมนู">
          ☰
        </button>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md glass-btn transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
            </svg>
            <span class="hidden md:inline">Logout</span>
          </button>
        </form>
      </div>
    </div>
  </header>

  {{-- ======= LAYOUT GRID ======= --}}
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

  {{-- ======= FOOTER ======= --}}
  <footer class="footer" role="contentinfo">
    @hasSection('footer')
      @yield('footer')
    @else
      © {{ date('Y') }} {{ config('app.name','Asset Repair Dashboard') }} • Build {{ app()->version() }}
    @endif
  </footer>

  {{-- ======= Scripts ======= --}}
  <script>
    // Mobile sidebar
    const btn = document.getElementById('btnSidebar');
    const side = document.getElementById('side');
    const bd   = document.getElementById('backdrop');

    function closeSide(){ side.classList.remove('open'); bd.classList.remove('show'); btn?.setAttribute('aria-expanded','false'); }
    function openSide(){ side.classList.add('open'); bd.classList.add('show'); btn?.setAttribute('aria-expanded','true'); }

    btn && btn.addEventListener('click', ()=> side.classList.contains('open') ? closeSide() : openSide());
    bd && bd.addEventListener('click', closeSide);

    // Desktop collapsed state
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

    // Hover expand (desktop)
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

    // Global Loader API
    window.Loader = {
      show(){ document.getElementById('loaderOverlay')?.classList.add('show') },
      hide(){ document.getElementById('loaderOverlay')?.classList.remove('show') }
    };

    document.addEventListener('DOMContentLoaded', () => Loader.hide());
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a'); if (!a) return;
      const href = a.getAttribute('href') || '';
      const noLoader = a.hasAttribute('data-no-loader') || a.getAttribute('target');
      const isAnchorSamePage = href.startsWith('#');
      if (!noLoader && href && !isAnchorSamePage) Loader.show();
    });
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (form instanceof HTMLFormElement && !form.hasAttribute('data-no-loader')) Loader.show();
    });
    window.addEventListener('beforeunload', () => Loader.show());
  </script>

   @stack('scripts')

  {{-- Global Loader (เหลืออันเดียว) --}}
  <div id="loaderOverlay" class="loader-overlay" aria-hidden="true">
    <div class="loader-spinner" role="status" aria-label="กำลังโหลด"></div>
  </div>

  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" defer></script>
<x-toast />

  @if (session('toast'))
    <script>
      const t = @json(session('toast'));
      t.position = 'tc';
      if (window.showToast) { window.showToast(t); }
      else { window.dispatchEvent(new CustomEvent('app:toast', { detail: t })); }
    </script>
  @endif

  @includeWhen(Auth::check(), 'partials.chat-fab')
</body>
</html>