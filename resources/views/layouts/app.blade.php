{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ config('app.name', 'Asset Repair') }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    .layout { display:grid; grid-template-columns: 260px 1fr; min-height:100dvh; }
    .sidebar { background:#0b1422; border-right:1px solid #1f2937; }
    .topbar  { background:#0b1422; border-bottom:1px solid #1f2937; position:sticky; top:0; z-index:30; }
    .content { padding:1rem; }
    .footer  { border-top:1px solid #1f2937; padding:.75rem 1rem; color:#9ca3af; }
    @media (max-width: 1024px){
      .layout { grid-template-columns: 1fr; }
      .sidebar { position:fixed; inset:0 auto 0 0; width:270px; transform:translateX(-100%); transition:.2s; z-index:50;}
      .sidebar.open { transform:translateX(0); }
      .backdrop{ position:fixed; inset:0; background:#0007; display:none; z-index:40;}
      .backdrop.show{ display:block; }
    }
  </style>
</head>
<body class="bg-zinc-900 text-zinc-100">

  {{-- NAVBAR (ย่อ) --}}
  <div class="topbar px-4 py-3 flex items-center gap-3">
    <button id="btnSidebar" class="lg:hidden inline-flex items-center px-2 py-1 rounded border border-zinc-700" aria-controls="side" aria-expanded="false">☰</button>
    <div class="font-semibold">{{ config('app.name','Asset Repair') }}</div>
    <div class="ml-auto flex items-center gap-3 text-sm">
      {{ $header ?? '' }}
    </div>
  </div>

  <div class="layout">
    {{-- SIDEBAR: ใช้ slot ถ้ามี; ไม่งั้น x-sidebar --}}
    <aside id="side" class="sidebar">
      @if (trim($sidebar ?? '') !== '')
        {{ $sidebar }}
      @else
        <x-sidebar />
      @endif
    </aside>

    <div id="backdrop" class="backdrop lg:hidden" aria-hidden="true"></div>

    <main class="content">
      {{ $slot }}
    </main>
  </div>

  <div class="footer text-xs">
    {{ $footer ?? ('© ' . date('Y') . ' ' . config('app.name','Asset Repair') . ' • Build ' . app()->version()) }}
  </div>

  <script>
    const btn = document.getElementById('btnSidebar');
    const side = document.getElementById('side');
    const bd   = document.getElementById('backdrop');
    function closeSide(){ side.classList.remove('open'); bd.classList.remove('show'); btn?.setAttribute('aria-expanded','false'); }
    function openSide(){ side.classList.add('open'); bd.classList.add('show'); btn?.setAttribute('aria-expanded','true'); }
    btn && btn.addEventListener('click', ()=> side.classList.contains('open') ? closeSide() : openSide());
    bd && bd.addEventListener('click', closeSide);
  </script>
</body>
</html>
