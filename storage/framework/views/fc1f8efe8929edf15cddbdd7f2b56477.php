

<?php $__env->startSection('title', 'Repair Queue — Pending'); ?>

<?php $__env->startSection('content'); ?>
<?php
  use Illuminate\Support\Str;
  /** @var \Illuminate\Pagination\LengthAwarePaginator $list */
  $q = request('q');
  $status = request('status'); // null|pending|in_progress|completed

  // Tabs (โทนเดียว-ชัด)
  $tab = function(?string $key) use ($status) {
    $active = ($status === $key) || (is_null($key) && empty($status));
    return $active
      ? 'bg-zinc-900 text-white border-zinc-900'
      : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50';
  };

  // Priority badge
  $priBadge = function(?string $p) {
    $p = strtolower((string)$p);
    return match (true) {
      in_array($p, ['urgent','high']) => 'bg-rose-50 text-rose-700 ring-rose-200',
      $p === 'medium'                 => 'bg-amber-50 text-amber-700 ring-amber-200',
      default                         => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    };
  };
?>


<div class="w-full px-4 md:px-6 lg:px-8 py-5 min-h-[calc(100vh-6rem)] flex flex-col gap-5">

  
  <div class="sticky top-0 z-20 -mt-2 rounded-xl border border-zinc-200 bg-white/95 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="px-5 py-4 flex flex-wrap items-center gap-3">
      <div class="mr-auto flex items-center gap-3">
        <div class="size-10 grid place-items-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8l-2 2 9 9 2-2-9-9zM16 3l5 5-3 3-5-5 3-3z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-lg md:text-xl font-semibold text-zinc-900">Pending Repair Requests</h1>
          <p class="text-xs text-zinc-500">รอรับเข้าคิว / มอบหมาย / เริ่มทำงาน</p>
        </div>
      </div>

      
      <div class="flex items-center gap-2 text-sm">
        <span class="rounded-full px-3 py-1 bg-zinc-900 text-white">Total: <b><?php echo e($stats['total'] ?? $list->total()); ?></b></span>
        <span class="rounded-full px-3 py-1 bg-amber-600/10 text-amber-800 ring-1 ring-amber-200">Pending: <b><?php echo e($stats['pending'] ?? '-'); ?></b></span>
        <span class="rounded-full px-3 py-1 bg-sky-600/10 text-sky-800 ring-1 ring-sky-200">In progress: <b><?php echo e($stats['in_progress'] ?? '-'); ?></b></span>
        <span class="rounded-full px-3 py-1 bg-emerald-600/10 text-emerald-800 ring-1 ring-emerald-200">Completed: <b><?php echo e($stats['completed'] ?? '-'); ?></b></span>
      </div>

      <a href="<?php echo e(route('repairs.my_jobs')); ?>"
         class="ml-2 inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        ← My Jobs
      </a>
    </div>

    
    <div class="border-t border-zinc-200 px-5 py-3">
      <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-12">
        
        <div class="md:col-span-7">
          <div class="flex flex-wrap gap-2">
            <?php $base = request()->except(['status','page']); ?>
            <a href="<?php echo e(url()->current() . '?' . http_build_query($base)); ?>"
               class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm <?php echo e($tab(null)); ?>">All</a>
            <a href="<?php echo e(request()->fullUrlWithQuery(array_merge($base,['status'=>'pending']))); ?>"
               class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm <?php echo e($tab('pending')); ?>">Pending</a>
            <a href="<?php echo e(request()->fullUrlWithQuery(array_merge($base,['status'=>'in_progress']))); ?>"
               class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm <?php echo e($tab('in_progress')); ?>">In progress</a>
            <a href="<?php echo e(request()->fullUrlWithQuery(array_merge($base,['status'=>'completed']))); ?>"
               class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm <?php echo e($tab('completed')); ?>">Completed</a>
          </div>
        </div>

        
        <div class="md:col-span-5">
          <div class="flex gap-2">
            <div class="relative grow">
              <input id="q" name="q" value="<?php echo e($q); ?>"
                     placeholder="ค้นหา: ชื่องาน, รายละเอียด, ผู้แจ้ง, หมายเลขทรัพย์สิน…"
                     class="w-full rounded-lg border border-zinc-300 pl-9 pr-3 py-2 text-sm placeholder:text-zinc-400
                            focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </span>
            </div>
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
              ค้นหา
            </button>
            <a href="<?php echo e(request()->url()); ?>"
               class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-300">
              ล้าง
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  
  <div class="flex-1 rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
    <div class="relative overflow-x-auto overflow-y-auto h-full">
      <table class="min-w-full text-sm">
        <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/70">
          <tr class="text-zinc-700 border-b">
            <th class="p-3 text-left font-semibold w-[40%]">Subject</th>
            <th class="p-3 text-left font-semibold w-[20%]">Asset</th>
            <th class="p-3 text-left font-semibold w-[18%]">Reporter</th>
            <th class="p-3 text-left font-semibold w-[14%]">Reported</th>
            <th class="p-3 text-right font-semibold w-[8%]">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr class="align-top hover:bg-zinc-50/70 border-b last:border-0">
            
            <td class="p-3">
              <a href="<?php echo e(route('maintenance.requests.show', $r)); ?>"
                 class="block max-w-full truncate font-medium text-zinc-900 hover:underline">
                <?php echo e(Str::limit($r->title, 90)); ?>

              </a>
              <?php if(!empty($r->description)): ?>
                <p class="mt-1 text-xs leading-relaxed text-zinc-500">
                  <?php echo e(Str::limit($r->description, 140)); ?>

                </p>
              <?php endif; ?>

              <div class="mt-2 flex flex-wrap gap-2">
                <?php if(!empty($r->priority)): ?>
                  <span class="rounded-full px-2 py-1 text-[11px] ring-1 <?php echo e($priBadge($r->priority)); ?>">
                    <?php echo e(ucfirst(strtolower($r->priority))); ?>

                  </span>
                <?php endif; ?>
                <?php if(!empty($r->category)): ?>
                  <span class="rounded-full px-2 py-1 text-[11px] ring-1 bg-zinc-50 text-zinc-700 ring-zinc-200">
                    <?php echo e($r->category); ?>

                  </span>
                <?php endif; ?>
              </div>
            </td>

            
            <td class="p-3">
              <div class="font-medium text-zinc-800">#<?php echo e($r->asset_id); ?></div>
              <div class="max-w-full truncate text-xs text-zinc-500"><?php echo e($r->asset->name ?? '—'); ?></div>
              <?php if(!empty($r->asset?->location)): ?>
                <div class="mt-0.5 max-w-full truncate text-[11px] text-zinc-400"><?php echo e($r->asset->location); ?></div>
              <?php endif; ?>
            </td>

            
            <td class="p-3">
              <div class="max-w-full truncate text-zinc-800"><?php echo e($r->reporter->name ?? '—'); ?></div>
              <?php if(!empty($r->reporter?->department)): ?>
                <div class="max-w-full truncate text-[11px] text-zinc-400"><?php echo e($r->reporter->department); ?></div>
              <?php endif; ?>
            </td>

            
            <td class="p-3">
              <div class="font-medium text-zinc-700">
                <?php echo e(optional($r->request_date)->format('Y-m-d H:i')); ?>

              </div>
              <?php if($r->request_date): ?>
                <div class="text-[11px] text-zinc-400"><?php echo e($r->request_date->diffForHumans()); ?></div>
              <?php endif; ?>
            </td>

            
            <td class="p-3">
              <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tech-only')): ?>
                <div class="hidden justify-end gap-2 sm:flex">
                  <form method="POST" action="<?php echo e(route('maintenance.requests.transition', $r)); ?>">
                    <?php echo csrf_field(); ?> <input type="hidden" name="action" value="accept">
                    <button class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium
                                   bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                      Accept
                    </button>
                  </form>

                  <form method="POST" action="<?php echo e(route('maintenance.requests.transition', $r)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="assign">
                    <input type="hidden" name="technician_id" value="<?php echo e(auth()->id()); ?>">
                    <button class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium
                                   bg-sky-600 text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-400">
                      Assign
                    </button>
                  </form>

                  <form method="POST" action="<?php echo e(route('maintenance.requests.transition', $r)); ?>">
                    <?php echo csrf_field(); ?> <input type="hidden" name="action" value="start">
                    <button class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium
                                   bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                      Start
                    </button>
                  </form>
                </div>

                
                <div class="relative sm:hidden text-right">
                  <details class="group inline-block">
                    <summary class="flex cursor-pointer list-none justify-end">
                      <span class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-xs border border-zinc-300">Actions ▾</span>
                    </summary>
                    <div class="absolute right-0 mt-1 w-44 rounded-lg border border-zinc-200 bg-white p-2 shadow">
                      <form method="POST" action="<?php echo e(route('maintenance.requests.transition', $r)); ?>" class="block">
                        <?php echo csrf_field(); ?> <input type="hidden" name="action" value="accept">
                        <button class="w-full rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Accept</button>
                      </form>
                      <form method="POST" action="<?php echo e(route('maintenance.requests.transition', $r)); ?>" class="mt-1 block">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="assign">
                        <input type="hidden" name="technician_id" value="<?php echo e(auth()->id()); ?>">
                        <button class="w-full rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-sky-700">Assign</button>
                      </form>
                      <form method="POST" action="<?php echo e(route('maintenance.requests.transition', $r)); ?>" class="mt-1 block">
                        <?php echo csrf_field(); ?> <input type="hidden" name="action" value="start">
                        <button class="w-full rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">Start</button>
                      </form>
                    </div>
                  </details>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="5" class="p-12 text-center text-zinc-500">No pending requests</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    
    <div class="border-t border-zinc-200 px-4 py-3 bg-white">
      <div class="flex items-center justify-between">
        <p class="text-xs text-zinc-500">
          Showing <span class="font-medium text-zinc-800"><?php echo e($list->firstItem() ?? 0); ?></span>
          to <span class="font-medium text-zinc-800"><?php echo e($list->lastItem() ?? 0); ?></span>
          of <span class="font-medium text-zinc-800"><?php echo e($list->total()); ?></span> results
        </p>
        <div class="shrink-0"><?php echo e($list->appends(request()->query())->links()); ?></div>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/repair/queue.blade.php ENDPATH**/ ?>