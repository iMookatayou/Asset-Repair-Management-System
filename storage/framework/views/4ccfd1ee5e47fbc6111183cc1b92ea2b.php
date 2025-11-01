
<?php
  $is = fn($p) => request()->routeIs($p);

  $base = 'flex items-center gap-3 pl-1 pr-2 h-10 rounded-md transition-all duration-150 font-medium relative';
  $off  = 'text-zinc-600 hover:text-emerald-600';
  $on   = 'text-emerald-600 font-semibold';
?>

<nav class="px-3 py-4 space-y-1 overflow-hidden">

  
  <?php $active = $is('repair.dashboard'); ?>
  <a href="<?php echo e(route('repair.dashboard')); ?>" class="<?php echo e($base); ?> <?php echo e($active ? $on : $off); ?>">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 transition-all
      <?php echo e($active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60'); ?>"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'bar-chart-3','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bar-chart-3','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $attributes = $__attributesOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__attributesOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $component = $__componentOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__componentOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
    </span>

    <span class="truncate"><?php echo e($active ? 'Dashboard' : 'Dashboard'); ?></span>
  </a>

  
  <?php $active = $is('maintenance.requests*'); ?>
  <a href="<?php echo e(route('maintenance.requests.index')); ?>" class="<?php echo e($base); ?> <?php echo e($active ? $on : $off); ?>">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 
      <?php echo e($active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60'); ?>"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'wrench','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'wrench','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $attributes = $__attributesOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__attributesOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $component = $__componentOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__componentOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
    </span>

    <span class="truncate">Repair Jobs</span>
  </a>

  
  <?php $active = $is('assets.*'); ?>
  <a href="<?php echo e(route('assets.index')); ?>" class="<?php echo e($base); ?> <?php echo e($active ? $on : $off); ?>">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 
      <?php echo e($active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60'); ?>"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'briefcase','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'briefcase','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $attributes = $__attributesOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__attributesOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $component = $__componentOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__componentOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
    </span>

    <span class="truncate">Assets</span>
  </a>

  
  <?php $active = $is('users.*'); ?>
  <a href="<?php echo e(route('users.index')); ?>" class="<?php echo e($base); ?> <?php echo e($active ? $on : $off); ?>">
    <span class="w-1.5 h-7 rounded-full bg-emerald-500 
      <?php echo e($active ? 'opacity-100' : 'opacity-0 group-hover:opacity-60'); ?>"></span>

    <span class="w-8 h-8 flex items-center justify-center">
      <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'users','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users','class' => 'w-4 h-4 
      '.e($active ? 'text-emerald-600' : 'text-zinc-500 group-hover:text-emerald-600').'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $attributes = $__attributesOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__attributesOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb2de5a19350412a96a4922f84030513)): ?>
<?php $component = $__componentOriginalbb2de5a19350412a96a4922f84030513; ?>
<?php unset($__componentOriginalbb2de5a19350412a96a4922f84030513); ?>
<?php endif; ?>
    </span>

    <span class="truncate">Users</span>
  </a>

</nav>
<?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/components/sidebar.blade.php ENDPATH**/ ?>