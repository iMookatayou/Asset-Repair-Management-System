<!doctype html>
<html lang="th" data-theme="govclean">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="theme-color" content="#0E2B51">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />

  {{-- ✅ TomSelect CSS โหลดครั้งเดียวที่ layout --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <title>@yield('title', config('app.name', 'Asset Repair'))</title>

  @yield('head')
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')
  @stack('head')

  <script>
    window.__playSidebarIntro = @json(session('play_sidebar_intro', false));
  </script>

  <style>
    @font-face { font-family:'Sarabun'; font-style:normal; font-weight:400;
      src:url('{{ asset('fonts/Sarabun-Regular.woff2') }}') format('woff2'),
          url('{{ asset('fonts/Sarabun-Regular.woff') }}') format('woff'); }
    @font-face { font-family:'Sarabun'; font-style:normal; font-weight:500;
      src:url('{{ asset('fonts/Sarabun-Medium.woff2') }}') format('woff2'),
          url('{{ asset('fonts/Sarabun-Medium.woff') }}') format('woff'); }
    @font-face { font-family:'Sarabun'; font-style:normal; font-weight:600;
      src:url('{{ asset('fonts/Sarabun-SemiBold.woff2') }}') format('woff2'),
          url('{{ asset('fonts/Sarabun-SemiBold.woff') }}') format('woff'); }
    @font-face { font-family:'Sarabun'; font-style:normal; font-weight:700;
      src:url('{{ asset('fonts/Sarabun-Bold.woff2') }}') format('woff2'),
          url('{{ asset('fonts/Sarabun-Bold.woff') }}') format('woff'); }

    html, body{
      height:100%; margin:0; padding:0;
      font-family:'Sarabun',system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
      font-weight:400; letter-spacing:.2px;
    }
    body{ min-height:100vh; display:flex; flex-direction:column; padding-top:0 !important; }

    :root{ color-scheme:light; --nav-h:80px; --topbar-h:calc(var(--nav-h) + 8px); }
    @media (max-width:992px){ :root{ --nav-h:72px; } }

    .content{ padding:1rem 1rem .25rem; }

    .sticky-under-topbar{ position:sticky; top:var(--nav-h); z-index:10; }
    .sticky-under-topbar > *:first-child{ margin-top:0 !important; }
    #main .sticky-under-topbar + *{ margin-top:6rem; }

    .layout{
      display:grid; grid-template-columns:260px 1fr;
      flex:1 0 auto; min-height:0 !important;
      transition:grid-template-columns .2s ease;
      background:#fff;
      color:hsl(var(--bc));
    }
    .sidebar{ background:#fff; border-right:1px solid hsl(var(--b2)); width:260px; transition:width .2s ease; }
    .sidebar-logo{ height:var(--nav-h); display:flex; align-items:center; }

    @media (min-width:1024px){
      .sidebar{ position:static; top:auto; align-self:stretch; height:auto; overflow-y:visible; }
      .sidebar.compact{ width:180px !important; }
      .layout.with-compact{ grid-template-columns:180px 1fr !important; }
      .sidebar.collapsed{ width:76px !important; }
      .layout.with-collapsed{ grid-template-columns:76px 1fr !important; }
      .layout.with-expanded{ grid-template-columns:260px 1fr !important; }
      .sidebar.collapsed.hover-expand{ width:260px !important; }
      .sidebar.collapsed.hover-expand .menu-text{ display:inline !important; }
      .sidebar.collapsed.hover-expand .menu-item{ justify-content:flex-start; gap:.75rem; }
      .sidebar.hover-expand{ box-shadow:4px 0 12px rgba(0,0,0,.06); }
    }

    @media (max-width:1024px){
      .layout{ grid-template-columns:1fr; }
      .sidebar{
        position:fixed; inset:var(--nav-h) auto 0 0; width:270px;
        transform:translateX(-100%); transition:.2s; z-index:50;
        box-shadow:4px 0 24px rgba(0,0,0,.06);
        max-height:calc(100vh - var(--nav-h)); overflow-y:auto;
      }
      .sidebar.open{ transform:translateX(0); }
      .backdrop{
        position:fixed; inset:var(--nav-h) 0 0 0; background:rgba(0,0,0,.45);
        display:none; z-index:40;
      }
      .backdrop.show{ display:block; }
    }

    .sidebar .menu{ padding:.5rem 0; }
    .sidebar .menu-item{
      display:grid; grid-template-columns:48px 1fr; align-items:center; gap:.75rem;
      height:44px; line-height:1; padding:0 .75rem; white-space:nowrap; overflow:hidden;
      transition:grid-template-columns .25s ease, padding .25s ease, background .15s ease;
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
      .sidebar.compact .menu-item .menu-text{ font-size:.92rem; }
    }

    .brand-en{ font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif; letter-spacing:.08em; }

    .footer-hero{
      background-color:#0F2D5C; color:#EAF2FF; font-family:'Sarabun',system-ui,sans-serif;
      box-shadow:0 -4px 20px rgba(0,0,0,.2);
      border-top:1px solid rgba(255,255,255,.12);
      margin:0;
    }

    .loader-overlay{
      position:fixed; inset:0;
      background:rgba(255,255,255,.6);
      backdrop-filter:blur(2px);
      display:flex; align-items:center; justify-content:center;
      z-index:99999;
      visibility:hidden; opacity:0;
      transition:opacity .2s ease, visibility .2s;
    }
    .loader-overlay.show{ visibility:visible; opacity:1; }
    .loader-spinner{
      width:38px; height:38px; border:4px solid #0E2B51;
      border-top-color:transparent; border-radius:50%;
      animation:spin .7s linear infinite;
    }
    @keyframes spin{ to{ transform:rotate(360deg) } }

    .app-navbar,.navbar-hero{ z-index:2000; }
    .dropdown-menu{ z-index:2100; }

    /* ✅ TomSelect ให้หน้าตาเข้ากับระบบ (ถ้าจะใช้ .ts-basic ใน _form) */
    .ts-wrapper.ts-control,
    .ts-wrapper{ font-size:14px; }
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

  {{-- Loader overlay (global) --}}
  <div id="loaderOverlay" class="loader-overlay" aria-hidden="true">
    <div class="loader-spinner" role="status" aria-label="กำลังโหลด"></div>
  </div>

  {{-- Core JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- ✅ TomSelect JS โหลดครั้งเดียวที่ layout --}}
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

  <script>
    // ✅ Loader แบบปลอดภัย
    window.Loader = {
      show(){
        document.getElementById('loaderOverlay')?.classList.add('show');
      },
      hide(){
        document.getElementById('loaderOverlay')?.classList.remove('show');
      }
    };

    // ✅ Failsafe: กันค้าง
    window.addEventListener('pageshow', () => window.Loader.hide());
    window.addEventListener('load', () => window.Loader.hide());
    setTimeout(() => window.Loader.hide(), 2500);

    // แสดง loader ตอนคลิกลิงก์/submit
    document.addEventListener('click', (e) => {
      if (e.target.closest('#chatWidgetRoot')) return;
      if (e.defaultPrevented) return;
      const a = e.target.closest('a'); if (!a) return;
      const href = a.getAttribute('href') || '';
      const noLoader = a.hasAttribute('data-no-loader') || a.getAttribute('target');
      const isAnchor = href.startsWith('#');
      if (!noLoader && href && !isAnchor) window.Loader.show();
    });

    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (e.defaultPrevented) return;
      if (form instanceof HTMLFormElement && !form.hasAttribute('data-no-loader')) window.Loader.show();
    });

    // ✅ Sidebar mobile
    (function(){
      const btn = document.getElementById('btnSidebar');
      const side = document.getElementById('side');
      const bd = document.getElementById('backdrop');

      function closeSide(){ side?.classList.remove('open'); bd?.classList.remove('show'); btn?.setAttribute('aria-expanded','false'); }
      function openSide(){ side?.classList.add('open'); bd?.classList.add('show'); btn?.setAttribute('aria-expanded','true'); }

      btn && btn.addEventListener('click', ()=> side.classList.contains('open') ? closeSide() : openSide());
      bd && bd.addEventListener('click', closeSide);
    })();

    // ✅ TomSelect init กลาง (รองรับทั้ง .ts-basic และ .ts-department)
    window.initTomSelect = function(root){
      root = root || document;
      root.querySelectorAll('select.ts-basic, select.ts-department').forEach(function(el){
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
    };

    document.addEventListener('DOMContentLoaded', function(){
      window.initTomSelect(document);
      window.Loader.hide();
    });

    document.addEventListener('turbo:load', function(){
      window.initTomSelect(document);
      window.Loader.hide();
    });
    document.addEventListener('livewire:navigated', function(){
      window.initTomSelect(document);
      window.Loader.hide();
    });
  </script>

  @yield('scripts')
  @stack('scripts')

  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" defer></script>
  <x-toast />
  @includeWhen(Auth::check(), 'partials.chat-fab')
</body>
</html>
