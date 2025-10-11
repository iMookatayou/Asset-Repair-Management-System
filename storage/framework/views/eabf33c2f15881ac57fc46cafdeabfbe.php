
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name']));

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

foreach (array_filter((['name']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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
  ];
  $paths = $icons[$name] ?? null;
?>

<?php if($paths): ?>
  <svg <?php echo e($attributes->merge(['class' => 'w-5 h-5'])); ?>

       xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
       fill="none" stroke="currentColor" stroke-width="2"
       stroke-linecap="round" stroke-linejoin="round">
    <?php echo $paths; ?>

  </svg>
<?php endif; ?>
<?php /**PATH /Users/fenyb_/Documents/Asset-Repair-Management-System/resources/views/components/app-icon.blade.php ENDPATH**/ ?>