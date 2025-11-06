
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'class' => 'w-5 h-5']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['name', 'class' => 'w-5 h-5']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $icons = [
    'bar-chart-3' => '<path d="M3 3v18h18"/><path d="M13 13h4v6h-4z"/><path d="M7 9h4v10H7z"/><path d="M17 3v4"/>',
    'wrench'      => '<path d="M15.5 5.5a5 5 0 0 0-7.07 7.07L3 18v3h3l5.43-5.43a5 5 0 0 0 7.07-7.07l-2 2-3-3 2-2z"/>',
    'briefcase'   => '<path d="M3 7h18v13H3z"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/>',
    'users'       => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 1 1 0 7.75"/>',
    'user'        => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'inbox'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 12h6l2 3h4l2-3h6"/>',
    'clipboard-list' => '<rect x="6" y="6" width="12" height="14" rx="2"/><path d="M9 6h6v4H9z"/><path d="M9 13h6"/><path d="M9 17h6"/>',
    'hard-hat'    => '<path d="M2 18h20"/><path d="M10 18v-2a2 2 0 0 1 2-2 2 2 0 0 1 2 2v2"/><path d="M4 18v-4a8 8 0 1 1 16 0v4"/><path d="M10 10V5a2 2 0 1 1 4 0v5"/>',
    'hammer'      => '<path d="M15 12l-8.6-8.6a2 2 0 0 1 2.8-2.8L17 9l-2 2 6 6-2 2-6-6-2 2z"/><path d="M2 22h6"/>',
    'mail'        => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    'shield'      => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  ];

  $paths = $icons[$name] ?? null;
?>

<?php if($paths): ?>
  <svg <?php echo e($attributes->merge(['class' => $class])); ?>

       xmlns="http://www.w3.org/2000/svg"
       viewBox="0 0 24 24"
       fill="none" stroke="currentColor"
       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <?php echo $paths; ?>

  </svg>
<?php endif; ?>
<?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/components/app-icon.blade.php ENDPATH**/ ?>