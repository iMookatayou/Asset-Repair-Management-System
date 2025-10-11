
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo e(config('app.name', 'Asset Repair')); ?></title>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>
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

  
  <div class="topbar px-4 py-3 flex items-center gap-3">
    <button id="btnSidebar" class="lg:hidden inline-flex items-center px-2 py-1 rounded border border-zinc-700" aria-controls="side" aria-expanded="false">☰</button>
    <div class="font-semibold"><?php echo e(config('app.name','Asset Repair')); ?></div>
    <div class="ml-auto flex items-center gap-3 text-sm">
      <?php echo e($header ?? ''); ?>

    </div>
  </div>

  <div class="layout">
    
    <aside id="side" class="sidebar">
      <?php if(trim($sidebar ?? '') !== ''): ?>
        <?php echo e($sidebar); ?>

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
      <?php echo e($slot); ?>

    </main>
  </div>

  <div class="footer text-xs">
    <?php echo e($footer ?? ('© ' . date('Y') . ' ' . config('app.name','Asset Repair') . ' • Build ' . app()->version())); ?>

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
<?php /**PATH /Users/fenyb_/Documents/Asset-Repair-Management-System/resources/views/layouts/app.blade.php ENDPATH**/ ?>