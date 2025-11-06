

<?php $__env->startSection('title','Assets'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $sortBy  = request('sort_by','id');
  $sortDir = request('sort_dir','desc');
  $th = function(string $key, string $label) use ($sortBy,$sortDir) {
    $nextDir = $sortBy === $key && $sortDir === 'asc' ? 'desc' : 'asc';
    $q = request()->fullUrlWithQuery(['sort_by' => $key, 'sort_dir' => $nextDir]);
    $arrow = $sortBy === $key ? ($sortDir === 'asc' ? '↑' : '↓') : '';
    return "<a href=\"{$q}\" class=\"inline-flex items-center gap-1 hover:text-zinc-900\">{$label} <span class=\"text-xs text-zinc-400\">{$arrow}</span></a>";
  };
?>


<div class="w-full px-4 md:px-6 lg:px-8 py-5 min-h-[calc(100vh-6rem)] flex flex-col gap-5">

  
  <div class="rounded-xl border bg-base-100/80 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-base-100/60">
    <div class="px-4 md:px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="size-10 grid place-items-center rounded-lg bg-primary/10 text-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M6 7h12M6 11h8m-8 4h12M4 5v14a2 2 0 0 0 2 2h12l2-2V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2Z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-lg md:text-xl font-semibold">Assets</h1>
          <p class="text-sm opacity-70">Browse, filter and maintain inventory</p>
        </div>
      </div>

      <a href="<?php echo e(route('assets.create')); ?>"
         class="hidden md:inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white
                hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        + New Asset
      </a>
    </div>
    <div class="h-px bg-gradient-to-r from-transparent via-base-200 to-transparent"></div>
  </div>

  
  <form method="GET" class="rounded-xl border bg-white shadow-sm p-4" role="search" aria-label="Filter assets">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
      <div class="md:col-span-2">
        <label for="q" class="block text-sm text-zinc-700">Search</label>
        <input id="q" type="text" name="q" value="<?php echo e(request('q')); ?>"
               placeholder="Search code / name / serial..."
               class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-[#0E2B51]">
      </div>

      <div>
        <label for="status" class="block text-sm text-zinc-700">Status</label>
        <?php $statuses = ['' => 'All','active'=>'Active','in_repair'=>'In Repair','disposed'=>'Disposed']; ?>
        <select id="status" name="status"
                class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2
                       focus:outline-none focus:ring-2 focus:ring-[#0E2B51]">
          <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($k); ?>" <?php if(request('status')===$k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div>
        <label for="category" class="block text-sm text-zinc-700">Category</label>
        <input id="category" type="text" name="category" value="<?php echo e(request('category')); ?>"
               placeholder="e.g. Computer"
               class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-[#0E2B51]">
      </div>

      <div class="md:col-span-3"></div>
      <div class="flex items-end justify-end gap-2">
        <?php if(request()->hasAny(['q','status','category','sort_by','sort_dir'])): ?>
          <a href="<?php echo e(route('assets.index')); ?>"
             class="inline-flex items-center justify-center rounded-lg px-3 py-2
                    border border-zinc-300 hover:bg-zinc-50">
            Reset
          </a>
        <?php endif; ?>
        <button class="inline-flex items-center justify-center rounded-lg px-3 py-2
                       bg-zinc-900 text-white hover:bg-zinc-800">
          Filter
        </button>
      </div>
    </div>
  </form>

  
  <div class="hidden md:flex flex-col rounded-xl border bg-white shadow-sm flex-1 overflow-hidden">
    <div class="overflow-x-auto overflow-y-auto flex-1">
      <table class="min-w-full divide-y divide-zinc-200 text-sm">
        <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/70">
          <tr class="text-left text-zinc-600">
            <th class="px-4 py-3"><?php echo $th('id','#'); ?></th>
            <th class="px-4 py-3"><?php echo $th('asset_code','Asset Code'); ?></th>
            <th class="px-4 py-3"><?php echo $th('name','Name'); ?></th>
            <th class="px-4 py-3 hidden xl:table-cell"><?php echo $th('category','Category'); ?></th>
            <th class="px-4 py-3 hidden lg:table-cell">Location</th>
            <th class="px-4 py-3"><?php echo $th('status','Status'); ?></th>
            <th class="px-4 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 bg-white">
          <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-zinc-50">
              <td class="px-4 py-3 text-zinc-500"><?php echo e($a->id); ?></td>
              <td class="px-4 py-3 font-medium text-zinc-800"><?php echo e($a->asset_code); ?></td>
              <td class="px-4 py-3">
                <a class="text-emerald-700 hover:underline" href="<?php echo e(route('assets.show',$a)); ?>"><?php echo e($a->name); ?></a>
                <div class="text-xs text-zinc-500">S/N: <?php echo e($a->serial_number ?? '—'); ?></div>
              </td>
              <td class="px-4 py-3 hidden xl:table-cell"><?php echo e($a->category ?? '—'); ?></td>
              <td class="px-4 py-3 hidden lg:table-cell"><?php echo e($a->location ?? '—'); ?></td>
              <td class="px-4 py-3">
                <?php
                  $badge = [
                    'active'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                    'in_repair' => 'bg-amber-50 text-amber-700 ring-amber-200',
                    'disposed'  => 'bg-rose-50 text-rose-700 ring-rose-200',
                  ][$a->status] ?? 'bg-zinc-50 text-zinc-700 ring-zinc-200';
                ?>
                <span class="rounded-full px-2 py-1 text-xs ring-1 <?php echo e($badge); ?>">
                  <?php echo e(ucfirst(str_replace('_',' ',$a->status))); ?>

                </span>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <a href="<?php echo e(route('assets.edit',$a)); ?>" class="text-emerald-700 hover:underline">Edit</a>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="px-4 py-10 text-center text-zinc-500">No assets found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    
    <div class="px-4 py-3 border-t bg-white">
      <div class="flex justify-center">
        <?php echo e($assets->withQueryString()->links()); ?>

      </div>
    </div>
  </div>

  
  <div class="grid grid-cols-1 gap-3 md:hidden flex-1 overflow-y-auto">
    <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="rounded-xl border bg-white shadow-sm p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-xs text-zinc-500">#<?php echo e($a->id); ?> — <?php echo e($a->asset_code); ?></div>
            <a class="font-medium text-zinc-900 hover:underline" href="<?php echo e(route('assets.show',$a)); ?>"><?php echo e($a->name); ?></a>
            <div class="text-xs text-zinc-500">S/N: <?php echo e($a->serial_number ?? '—'); ?></div>
          </div>
          <?php
            $badge = [
              'active'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
              'in_repair' => 'bg-amber-50 text-amber-700 ring-amber-200',
              'disposed'  => 'bg-rose-50 text-rose-700 ring-rose-200',
            ][$a->status] ?? 'bg-zinc-50 text-zinc-700 ring-zinc-200';
          ?>
          <span class="rounded-full px-2 py-1 text-xs ring-1 <?php echo e($badge); ?>">
            <?php echo e(ucfirst(str_replace('_',' ',$a->status))); ?>

          </span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-zinc-500">Category</div>
          <div><?php echo e($a->category ?? '—'); ?></div>
          <div class="text-zinc-500">Location</div>
          <div><?php echo e($a->location ?? '—'); ?></div>
        </div>

        <div class="mt-3 text-right">
          <a href="<?php echo e(route('assets.edit',$a)); ?>"
             class="inline-flex items-center rounded-lg px-3 py-2 border border-zinc-300 hover:bg-zinc-50">
            Edit
          </a>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-xl border bg-white shadow-sm p-8 text-center text-zinc-500">
        No assets found.
      </div>
    <?php endif; ?>

    <div class="flex justify-center pb-2">
      <?php echo e($assets->withQueryString()->links()); ?>

    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/assets/index.blade.php ENDPATH**/ ?>