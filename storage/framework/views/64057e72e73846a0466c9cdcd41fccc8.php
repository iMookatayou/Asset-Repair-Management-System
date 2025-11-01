
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $__env->yieldContent('title', config('app.name', 'Asset Repair')); ?></title>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>

  <style>
    /* =========================
       Base Layout (Light theme)
       ========================= */
    .layout { display:grid; grid-template-columns: 260px 1fr; min-height:100dvh; transition:grid-template-columns .2s ease; }
    .sidebar { background:#ffffff; border-right:1px solid #e5e7eb; width:260px; transition:width .2s ease; }
    .topbar  { position:sticky; top:0; z-index:30; }
    .content { padding:1rem; }
    .footer  { border-top:1px solid #e5e7eb; padding:.75rem 1rem; color:#6b7280; }

    @media (min-width: 1024px){
      .sidebar.collapsed { width:76px !important; }
      .layout.with-collapsed { grid-template-columns: 76px 1fr !important; }
      .layout.with-expanded  { grid-template-columns: 260px 1fr !important; }
    }

    @media (max-width: 1024px){
      .layout { grid-template-columns: 1fr; }
      .sidebar { position:fixed; inset:0 auto 0 0; width:270px; transform:translateX(-100%); transition:.2s; z-index:50;}
      .sidebar.open { transform:translateX(0); }
      .backdrop{ position:fixed; inset:0; background:#0007; display:none; z-index:40;}
      .backdrop.show{ display:block; }
    }

    @media (min-width: 1024px) {
      .sidebar.collapsed.hover-expand { width: 260px !important; }
      .sidebar.collapsed.hover-expand .menu-text { display: inline !important; }
      .sidebar.collapsed.hover-expand .menu-item { justify-content: flex-start; gap: .75rem; }
      .sidebar.hover-expand { box-shadow: 4px 0 12px rgba(0,0,0,.06); }
    }

    .sidebar .menu { padding: .5rem 0; }
    .sidebar .menu-item {
      display: grid; grid-template-columns: 48px 1fr; align-items: center;
      gap: .75rem; height: 44px; line-height: 1; padding: 0 .75rem;
      white-space: nowrap; overflow: hidden;
      transition: grid-template-columns .25s ease, padding .25s ease, background .15s ease;
      color:#374151;
    }
    .sidebar .menu-item:hover{ background:#f9fafb; }
    .sidebar .menu-item .icon-wrap { width: 48px; height: 44px; display:inline-flex; align-items:center; justify-content:center; color:#6b7280; }
    .sidebar .menu-item .menu-text { overflow: hidden; text-overflow: ellipsis; opacity: 1; transition: opacity .18s ease; }

    @media (min-width: 1024px){
      .sidebar.collapsed .menu-item { grid-template-columns: 48px 0px; gap:0; padding-inline:.5rem; }
      .sidebar.collapsed .menu-item .menu-text { opacity:0; pointer-events:none; }
      .sidebar.collapsed.hover-expand .menu-item { grid-template-columns:48px 1fr; gap:.75rem; padding-inline:.75rem; }
      .sidebar.collapsed.hover-expand .menu-item .menu-text { opacity:1; }
    }

    /* =========================
       NAVBAR THEME: BLUE + WHITE
       ========================= */
    .topbar--hero{
      background: #0D294A; /* โทนเดียว */
      color:#f6fbff;
      border-bottom:none;
      box-shadow: 0 10px 30px rgba(0,0,0,.18);
      backdrop-filter: saturate(120%) blur(6px);
      position: relative;
    }

    .topbar--inner{ min-height: 96px; }
    .brand-logo{
      width: 60px; /* จาก 44px → เพิ่มขนาด */
      height: 60px;
      border-radius: 0; /* ไม่มีโค้ง */
      background: transparent !important; /* ตัดพื้นหลังออก */
      padding: 0; /* เอา padding ออก */
      box-shadow: none !important; /* ตัดเงา */
      object-fit: contain;
    }
    .brand-title{ font-weight: 900; letter-spacing:.2px; line-height:1; color:#ffffff; }
    .brand-sub{ font-size:.82rem; color:#d8e7f9; margin-top:2px; }

    /* ปุ่มโปร่งแก้วโทนขาวฟ้า */
    .glass-btn{
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.28);
      color:#f6fbff;
    }
    .glass-btn:hover{ background: rgba(255,255,255,.18); }

    /* Badges โทนขาวฟ้า */
    .status-badge{
      font-size:.75rem; font-weight:700; border-radius:999px; padding:.22rem .6rem;
      background:#ffffff; color:#0b1220; border:1px solid #e5edf8;
      display:inline-flex; gap:.25rem; align-items:center;
    }
    .status-total   { background:#f3f7ff; color:#0b1220; }
    .status-pending { background:#eef5ff; color:#1e3a8a; }
    .status-progress{ background:#e6f0ff; color:#1d4ed8; }
    .status-done    { background:#eaf4ff; color:#0f3dbd; }
  </style>
</head>

<body class="bg-gray-50 text-gray-900">

  
  <div class="topbar topbar--hero">
    <div class="topbar--inner px-4 flex items-center justify-between gap-4 flex-wrap">

      
      <div class="flex items-center gap-4 flex-wrap">
        <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-3 no-underline">
          
          <img src="<?php echo e(asset('/images/logoppk.png')); ?>" alt="PPK" class="brand-logo">
          <div class="leading-tight">
            <div class="brand-title text-xl md:text-2xl">
              <?php echo e(config('app.name','Asset Repair Dashboard')); ?>

            </div>
            <div class="brand-sub">Asset Repair Management</div>
          </div>
        </a>

        
        <div class="hidden md:flex items-center gap-2 text-[13px]">
          <?php if (! empty(trim($__env->yieldContent('topbadges')))): ?>
            <?php echo $__env->yieldContent('topbadges'); ?>
          <?php else: ?>
            <span class="status-badge status-total">Total: <strong>300</strong></span>
            <span class="status-badge status-pending">Pending: <strong>103</strong></span>
            <span class="status-badge status-progress">In&nbsp;progress: <strong>107</strong></span>
            <span class="status-badge status-done">Completed: <strong>90</strong></span>
          <?php endif; ?>
        </div>
      </div>

      
      <div class="flex items-center gap-2 ml-auto">
        <button id="btnSidebar"
                class="lg:hidden inline-flex items-center px-2 py-1 rounded-md glass-btn transition-colors"
                aria-controls="side" aria-expanded="false" title="เมนู">
          ☰
        </button>

        <form method="POST" action="<?php echo e(route('logout')); ?>">
          <?php echo csrf_field(); ?>
          <button type="submit"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md glass-btn transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
            </svg>
            <span class="hidden md:inline">Logout</span>
          </button>
        </form>
      </div>
    </div>
  </div>

  
  <div id="layout" class="layout">
    <aside id="side" class="sidebar" aria-label="Sidebar">
      <?php if (! empty(trim($__env->yieldContent('sidebar')))): ?>
        <?php echo $__env->yieldContent('sidebar'); ?>
      <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal2880b66d47486b4bfeaf519598a469d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2880b66d47486b4bfeaf519598a469d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $attributes = $__attributesOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $component = $__componentOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__componentOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>
      <?php endif; ?>
    </aside>

    <div id="backdrop" class="backdrop lg:hidden" aria-hidden="true"></div>

    <main class="content">
      <?php if (! empty(trim($__env->yieldContent('page-header')))): ?>
        <div class="mb-4"><?php echo $__env->yieldContent('page-header'); ?></div>
      <?php endif; ?>

      <?php if(session('ok')): ?>
        <div class="mb-4 p-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-800">
          <?php echo e(session('ok')); ?>

        </div>
      <?php endif; ?>

      <?php echo $__env->yieldContent('content'); ?>
    </main>
  </div>

  <div class="footer text-xs">
    <?php if (! empty(trim($__env->yieldContent('footer')))): ?>
      <?php echo $__env->yieldContent('footer'); ?>
    <?php else: ?>
      © <?php echo e(date('Y')); ?> <?php echo e(config('app.name','Asset Repair Dashboard')); ?> • Build <?php echo e(app()->version()); ?>

    <?php endif; ?>
  </div>

  
  <script>
    // ===== Mobile sidebar open/close =====
    const btn = document.getElementById('btnSidebar');
    const side = document.getElementById('side');
    const bd   = document.getElementById('backdrop');
    function closeSide(){ side.classList.remove('open'); bd.classList.remove('show'); btn?.setAttribute('aria-expanded','false'); }
    function openSide(){ side.classList.add('open'); bd.classList.add('show'); btn?.setAttribute('aria-expanded','true'); }
    btn && btn.addEventListener('click', ()=> side.classList.contains('open') ? closeSide() : openSide());
    bd && bd.addEventListener('click', closeSide);

    // ===== Desktop collapse/expand persist =====
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

    // ===== Hover expand =====
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
  </script>
</body>
</html>
<?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/layouts/app.blade.php ENDPATH**/ ?>