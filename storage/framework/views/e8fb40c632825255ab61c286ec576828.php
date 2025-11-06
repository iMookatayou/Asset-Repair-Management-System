<?php $__env->startSection('title', 'Maintenance Requests'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $priorityClass = function(string $p) {
    return match($p) {
      'low'    => 'border-emerald-200 bg-emerald-50 text-emerald-700',
      'medium' => 'border-sky-200 bg-sky-50 text-sky-700',
      'high'   => 'border-amber-200 bg-amber-50 text-amber-700',
      'urgent' => 'border-rose-200 bg-rose-50 text-rose-700',
      default  => 'border-zinc-200 bg-white text-zinc-700',
    };
  };
  $statusClass = function(string $s) {
    return match($s) {
      'pending'     => 'border-zinc-300 bg-white text-zinc-700',
      'in_progress' => 'border-blue-200 bg-blue-50 text-blue-700',
      'completed'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
      'cancelled'   => 'border-zinc-300 bg-zinc-50 text-zinc-700',
      default       => 'border-zinc-200 bg-white text-zinc-700',
    };
  };
?>


<div class="w-full px-4 md:px-6 lg:px-8 py-5 min-h-[calc(100vh-6rem)] flex flex-col gap-5">

  
  <div class="rounded-xl border bg-white shadow-sm">
    <div class="px-4 md:px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="size-10 grid place-items-center rounded-lg bg-emerald-50 text-emerald-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M6 11h12M8 15h8m-6 4h4"/>
          </svg>
        </div>
        <div>
          <h1 class="text-lg md:text-xl font-semibold">Maintenance Requests</h1>
          <p class="text-sm text-zinc-500">Browse, filter and review incoming requests</p>
        </div>
      </div>

      <a href="<?php echo e(route('maintenance.requests.create')); ?>"
         class="hidden md:inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
         onclick="showLoader()">
        + New Request
      </a>
    </div>
    <div class="h-px bg-gradient-to-r from-transparent via-zinc-200 to-transparent"></div>

    
    <form method="GET" action="<?php echo e(route('maintenance.requests.index')); ?>"
          class="p-4 md:p-5" role="search" aria-label="Filter maintenance requests"
          onsubmit="showLoader()">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <div class="lg:col-span-5">
          <label for="q" class="block text-sm text-zinc-600">Search</label>
          <input id="q" type="text" name="q" value="<?php echo e(request('q')); ?>"
                 placeholder="Search name/title/description…"
                 class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0E2B51]" />
        </div>

        <div class="lg:col-span-3">
          <label for="status" class="block text-sm text-zinc-600">Status</label>
          <select id="status" name="status"
                  class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0E2B51]">
            <option value="">All Status</option>
            <?php $__currentLoopData = ['pending'=>'Pending','in_progress'=>'In progress','completed'=>'Completed','cancelled'=>'Cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($k); ?>" <?php if(request('status') === $k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        <div class="lg:col-span-2">
          <label for="priority" class="block text-sm text-zinc-600">Priority</label>
          <select id="priority" name="priority"
                  class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0E2B51]">
            <option value="">All Priority</option>
            <?php $__currentLoopData = ['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($k); ?>" <?php if(request('priority') === $k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        <div class="lg:col-span-2 flex items-end">
          <div class="flex w-full gap-2">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg px-4 py-2 border border-zinc-300 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 w-full">
              Filter
            </button>
            <?php if(request()->hasAny(['q','status','priority'])): ?>
              <a href="<?php echo e(route('maintenance.requests.index')); ?>"
                 class="inline-flex items-center justify-center rounded-lg px-4 py-2 border border-zinc-300 hover:bg-zinc-50 w-28">
                Reset
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </form>
  </div>

  
  <div class="hidden md:flex flex-col rounded-xl border bg-white shadow-sm flex-1 overflow-hidden">
    <div class="overflow-x-auto overflow-y-auto flex-1">
      <table class="min-w-full text-sm">
        <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/70">
          <tr class="text-left text-zinc-700 border-b">
            <th class="p-3 font-semibold">#</th>
            <th class="p-3 font-semibold">Title</th>
            <th class="p-3 font-semibold">Email</th>
            <th class="p-3 font-semibold">Department</th>
            <th class="p-3 font-semibold">Priority</th>
            <th class="p-3 font-semibold">Status</th>
            <th class="p-3 font-semibold">Reporter</th>
            <th class="p-3 font-semibold">Technician</th>
            <th class="p-3 font-semibold">Requested</th>
            <th class="p-3 font-semibold"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t hover:bg-zinc-50">
              <td class="p-3 align-top text-zinc-700"><?php echo e($row->id); ?></td>

              <td class="p-3 align-top">
                <div class="font-medium text-zinc-900"><?php echo e($row->title); ?></div>
                <?php if($row->description): ?>
                  <div class="text-zinc-500 line-clamp-2"><?php echo e($row->description); ?></div>
                <?php endif; ?>
              </td>

              <td class="p-3 align-top text-zinc-700"><?php echo e($row->reporter->email ?? '-'); ?></td>
              <td class="p-3 align-top text-zinc-700"><?php echo e($row->reporter->department->name ?? $row->department->name ?? '-'); ?></td>

              <td class="p-3 align-top">
                <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize <?php echo e($priorityClass($row->priority ?? '')); ?>">
                  <?php echo e(str_replace('_',' ', $row->priority)); ?>

                </span>
              </td>

              <td class="p-3 align-top">
                <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize <?php echo e($statusClass($row->status ?? '')); ?>">
                  <?php echo e(str_replace('_',' ', $row->status)); ?>

                </span>
              </td>

              <td class="p-3 align-top text-zinc-700"><?php echo e($row->reporter->name ?? '-'); ?></td>
              <td class="p-3 align-top text-zinc-700"><?php echo e($row->technician->name ?? '-'); ?></td>

              <td class="p-3 align-top text-zinc-700 whitespace-nowrap">
                <?php $when = optional($row->request_date ?? $row->created_at); ?>
                <?php if($when): ?>
                  <time datetime="<?php echo e($when->toIso8601String()); ?>"><?php echo e($when->format('Y-m-d H:i')); ?></time>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>

              <td class="p-3 align-top text-right">
                <a href="<?php echo e(route('maintenance.requests.show', $row)); ?>"
                   class="text-emerald-700 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded px-1 py-0.5"
                   onclick="showLoader()">
                  View
                </a>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="10" class="p-10 text-center text-zinc-500">No data</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="px-4 py-3 border-t bg-white">
      <div class="flex justify-center">
        <?php echo e($list->withQueryString()->links()); ?>

      </div>
    </div>
  </div>

  
  <div class="grid grid-cols-1 gap-3 md:hidden flex-1 overflow-y-auto">
    <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="rounded-xl border bg-white shadow-sm p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="font-medium text-zinc-900"><?php echo e($row->title); ?></div>
            <?php if($row->description): ?>
              <div class="text-sm text-zinc-500 line-clamp-2"><?php echo e($row->description); ?></div>
            <?php endif; ?>
          </div>
          <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize text-xs <?php echo e($statusClass($row->status ?? '')); ?>">
            <?php echo e(str_replace('_',' ', $row->status)); ?>

          </span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-zinc-500">Priority</div>
          <div>
            <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize text-xs <?php echo e($priorityClass($row->priority ?? '')); ?>">
              <?php echo e(str_replace('_',' ', $row->priority)); ?>

            </span>
          </div>

          <div class="text-zinc-500">Reporter</div>
          <div><?php echo e($row->reporter->name ?? '-'); ?></div>

          <div class="text-zinc-500">Technician</div>
          <div><?php echo e($row->technician->name ?? '-'); ?></div>

          <div class="text-zinc-500">Requested</div>
          <div>
            <?php $when = optional($row->request_date ?? $row->created_at); ?>
            <?php if($when): ?>
              <time datetime="<?php echo e($when->toIso8601String()); ?>"><?php echo e($when->format('Y-m-d H:i')); ?></time>
            <?php else: ?>
              -
            <?php endif; ?>
          </div>
        </div>

        <div class="mt-3 text-right">
          <a href="<?php echo e(route('maintenance.requests.show', $row)); ?>"
             class="inline-flex items-center rounded-lg px-3 py-2 border border-zinc-300 hover:bg-zinc-50"
             onclick="showLoader()">
            View
          </a>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-xl border bg-white shadow-sm p-8 text-center text-zinc-500">
        No data
      </div>
    <?php endif; ?>

    <div class="flex justify-center pb-2">
      <?php echo e($list->withQueryString()->links()); ?>

    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('after-content'); ?>
<div id="loaderOverlay" class="loader-overlay">
  <div class="loader-spinner"></div>
</div>

<style>
  .loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:99999;visibility:hidden;opacity:0;transition:opacity .2s,visibility .2s}
  .loader-overlay.show{visibility:visible;opacity:1}
  .loader-spinner{width:38px;height:38px;border:4px solid #0E2B51;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>
<script>
  function showLoader(){document.getElementById("loaderOverlay").classList.add("show")}
  function hideLoader(){document.getElementById("loaderOverlay").classList.remove("show")}
  document.addEventListener("DOMContentLoaded", hideLoader);
  document.addEventListener("click", e => {
    const link = e.target.closest("a");
    if (!link) return;
    const url = link.getAttribute("href");
    if (url && !link.target && !url.startsWith("#") && link.host === location.host) showLoader();
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/maintenance/requests/index.blade.php ENDPATH**/ ?>