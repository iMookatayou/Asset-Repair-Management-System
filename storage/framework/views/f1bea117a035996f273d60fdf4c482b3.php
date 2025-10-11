
<?php
  $itemBase  = 'group relative flex items-center gap-3 px-3 py-2 rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500';
  $inactive  = 'text-zinc-300 hover:bg-zinc-800';
  $activeBox = 'bg-zinc-800 text-emerald-400';
?>

<nav class="p-3">
  <div class="text-xs uppercase text-zinc-400 mb-2">เมนูหลัก</div>
  <ul class="space-y-1">

    <?php $active = request()->routeIs('repair.dashboard'); ?>
    <li>
      <a href="<?php echo e(route('repair.dashboard')); ?>"
         class="<?php echo e($itemBase); ?> <?php echo e($active ? $activeBox : $inactive); ?>"
         aria-current="<?php echo e($active ? 'page' : 'false'); ?>">
        <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'bar-chart-3','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bar-chart-3','class' => 'w-4 h-4 shrink-0']); ?>
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
        <span>Dashboard</span>
      </a>
    </li>

    <?php $active = request()->routeIs('maintenance.requests.*'); ?>
    <li>
      <a href="<?php echo e(route('maintenance.requests.index')); ?>"
         class="<?php echo e($itemBase); ?> <?php echo e($active ? $activeBox : $inactive); ?>"
         aria-current="<?php echo e($active ? 'page' : 'false'); ?>">
        <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'wrench','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'wrench','class' => 'w-4 h-4 shrink-0']); ?>
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
        <span>Repair jobs</span>
      </a>
    </li>

    <?php $active = request()->routeIs('assets.*'); ?>
    <li>
      <a href="<?php echo e(route('assets.index')); ?>"
         class="<?php echo e($itemBase); ?> <?php echo e($active ? $activeBox : $inactive); ?>"
         aria-current="<?php echo e($active ? 'page' : 'false'); ?>">
        <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'briefcase','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'briefcase','class' => 'w-4 h-4 shrink-0']); ?>
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
        <span>Assets</span>
      </a>
    </li>

    <?php $active = request()->routeIs('users.*'); ?>
    <li>
      <a href="<?php echo e(route('users.index')); ?>"
         class="<?php echo e($itemBase); ?> <?php echo e($active ? $activeBox : $inactive); ?>"
         aria-current="<?php echo e($active ? 'page' : 'false'); ?>">
        <?php if (isset($component)) { $__componentOriginalbb2de5a19350412a96a4922f84030513 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb2de5a19350412a96a4922f84030513 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-icon','data' => ['name' => 'users','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users','class' => 'w-4 h-4 shrink-0']); ?>
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
        <span>Users</span>
      </a>
    </li>

  </ul>
</nav>
<?php /**PATH /Users/fenyb_/Documents/Asset-Repair-Management-System/resources/views/components/sidebar.blade.php ENDPATH**/ ?>