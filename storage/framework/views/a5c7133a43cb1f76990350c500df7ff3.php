

<?php $__env->startSection('title','My Jobs'); ?>

<?php $__env->startSection('content'); ?>
<?php
  use Illuminate\Support\Str;

  $q        = request('q');
  $status   = request('status'); // null|pending|accepted|in_progress|on_hold|resolved|closed|cancelled
  $priority = request('priority'); // low|medium|high|urgent (optional)
  $sort     = request('sort','updated_desc'); // updated_desc|updated_asc|created_desc|created_asc
  $perPage  = (int) request('per_page', 20);

  // tokens
  $btnPrimary = 'inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2';
  $btnGhost   = 'inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2';
  $chip       = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1';

  $statusClasses = [
    'pending'     => 'text-amber-700 bg-amber-50 ring-amber-200',
    'accepted'    => 'text-sky-700 bg-sky-50 ring-sky-200',
    'in_progress' => 'text-indigo-700 bg-indigo-50 ring-indigo-200',
    'on_hold'     => 'text-slate-700 bg-slate-50 ring-slate-200',
    'resolved'    => 'text-emerald-700 bg-emerald-50 ring-emerald-200',
    'closed'      => 'text-slate-800 bg-slate-200 ring-slate-200',
    'cancelled'   => 'text-slate-600 bg-white ring-slate-300',
  ];
  $priorityClasses = [
    'low'     => 'text-emerald-700 bg-emerald-50 ring-emerald-200',
    'medium'  => 'text-sky-700 bg-sky-50 ring-sky-200',
    'high'    => 'text-amber-700 bg-amber-50 ring-amber-200',
    'urgent'  => 'text-rose-700 bg-rose-50 ring-rose-200',
  ];

  $human = fn($s) => Str::of($s)->replace('_',' ')->title();
?>

<div class="mx-auto max-w-7xl py-6 space-y-6">

  
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between px-5 py-4">
      <div class="flex items-center gap-3">
        <div class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-700">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h10M4 14h16M4 18h10"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-semibold text-slate-900">My Jobs</h2>
          <p class="text-sm text-slate-500">Browse, filter and manage repair jobs assigned to you</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo e(route('maintenance.requests.index')); ?>" class="<?php echo e($btnGhost); ?>">All Requests</a>
        <a href="<?php echo e(route('maintenance.requests.create')); ?>" class="hidden sm:inline-flex <?php echo e($btnPrimary); ?>">+ New Request</a>
      </div>
    </div>

    
    <div class="border-t border-slate-200 px-5 py-4">
      <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 xl:grid-cols-8">
        
        <?php $__currentLoopData = request()->except(['q','status','priority','sort','per_page','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <div class="lg:col-span-2 xl:col-span-3">
          <label for="q" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
          <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="m21 21-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <input id="q" name="q" value="<?php echo e($q); ?>" placeholder="Search subject, asset, reporter…"
                   class="w-full rounded-xl border border-slate-300 bg-white px-9 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500"/>
          </div>
        </div>

        
        <div class="lg:col-span-1">
          <label for="status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
          <select id="status" name="status"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            <option value="" <?php if(!$status): echo 'selected'; endif; ?>>All Status</option>
            <?php $__currentLoopData = ['in_progress','resolved','pending','accepted','on_hold','closed','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($s); ?>" <?php if($status===$s): echo 'selected'; endif; ?>><?php echo e($human($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="lg:col-span-1">
          <label for="priority" class="mb-1 block text-xs font-medium text-slate-600">Priority</label>
          <select id="priority" name="priority"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            <option value="" <?php if(!$priority): echo 'selected'; endif; ?>>All Priority</option>
            <?php $__currentLoopData = ['low','medium','high','urgent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($p); ?>" <?php if($priority===$p): echo 'selected'; endif; ?>><?php echo e(ucfirst($p)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="lg:col-span-1">
          <label for="sort" class="mb-1 block text-xs font-medium text-slate-600">Sort</label>
          <select id="sort" name="sort"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            <option value="updated_desc" <?php if($sort==='updated_desc'): echo 'selected'; endif; ?>>Updated</option>
            <option value="updated_asc"  <?php if($sort==='updated_asc'): echo 'selected'; endif; ?>>Updated</option>
            <option value="created_desc" <?php if($sort==='created_desc'): echo 'selected'; endif; ?>>Created</option>
            <option value="created_asc"  <?php if($sort==='created_asc'): echo 'selected'; endif; ?>>Created</option>
          </select>
        </div>

        
        <div class="lg:col-span-1">
          <label for="per_page" class="mb-1 block text-xs font-medium text-slate-600">Per page</label>
          <select id="per_page" name="per_page"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            <?php $__currentLoopData = [10,20,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($n); ?>" <?php if($perPage===$n): echo 'selected'; endif; ?>><?php echo e($n); ?>/page</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="flex items-end gap-2 lg:col-span-1">
          <button type="submit" class="w-full sm:w-auto <?php echo e($btnPrimary); ?>">Apply</button>
          <?php if($q || $status || $priority || $sort!=='updated_desc' || $perPage!==20): ?>
            <a href="<?php echo e(route('repairs.my_jobs')); ?>" class="w-full sm:w-auto <?php echo e($btnGhost); ?>">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    
    <div class="border-t border-slate-200">
      <div class="overflow-x-auto">
        <table class="w-full table-auto text-left">
          <thead class="bg-slate-50">
            <tr class="text-xs uppercase tracking-wide text-slate-500">
              <th class="px-5 py-3 w-[6%]">#</th>
              <th class="px-5 py-3 w-[34%]">Subject</th>
              <th class="px-5 py-3 w-[16%]">Asset</th>
              <th class="px-5 py-3 w-[12%]">Priority</th>
              <th class="px-5 py-3 w-[12%]">Status</th>
              <th class="px-5 py-3 w-[12%]">Updated</th>
              <th class="px-5 py-3 w-[6%]"><span class="sr-only">View</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-sm text-slate-800">
          <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
              $stCls = $statusClasses[$r->status] ?? 'text-slate-700 bg-slate-50 ring-slate-200';
              $prio  = strtolower($r->priority ?? 'medium');
              $prCls = $priorityClasses[$prio] ?? $priorityClasses['medium'];
            ?>
            <tr class="hover:bg-slate-50 align-top">
              <td class="px-5 py-3 text-slate-500">#<?php echo e($r->id); ?></td>

              <td class="px-5 py-3">
                <a href="<?php echo e(route('maintenance.requests.show', $r)); ?>"
                   class="font-medium text-slate-900 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded">
                  <span class="line-clamp-2"><?php echo e($r->title); ?></span>
                </a>
                <div class="mt-0.5 text-xs text-slate-500 line-clamp-2">
                  <?php echo e(Str::limit($r->description ?? '', 140)); ?>

                </div>
              </td>

              <td class="px-5 py-3 whitespace-nowrap">
                #<?php echo e($r->asset_id); ?> — <span class="truncate inline-block max-w-[14rem] align-bottom"><?php echo e($r->asset->name ?? '-'); ?></span>
                <?php if($r->location ?? null): ?>
                  <div class="text-xs text-slate-500"><?php echo e($r->location); ?></div>
                <?php endif; ?>
              </td>

              <td class="px-5 py-3">
                <span class="<?php echo e($chip); ?> <?php echo e($prCls); ?>"><?php echo e(ucfirst($prio)); ?></span>
              </td>

              <td class="px-5 py-3">
                <span class="<?php echo e($chip); ?> <?php echo e($stCls); ?>"><?php echo e($human($r->status)); ?></span>
              </td>

              <td class="px-5 py-3 whitespace-nowrap text-slate-600">
                <?php echo e(optional($r->updated_at)->format('Y-m-d H:i')); ?>

              </td>

              <td class="px-5 py-3 text-right">
                <a href="<?php echo e(route('maintenance.requests.show', $r)); ?>"
                   class="text-emerald-700 hover:text-emerald-800 hover:underline">View</a>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="px-5 py-10">
                <div class="text-center text-slate-500">
                  <div class="mx-auto mb-3 h-10 w-10 rounded-full border border-dashed border-slate-300"></div>
                  <div class="mb-2 text-sm">No jobs found.</div>
                  <a href="<?php echo e(route('repairs.my_jobs')); ?>" class="<?php echo e($btnGhost); ?>">Clear filters</a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      
      <div class="px-5 py-3">
        <div class="flex justify-center">
          <?php echo e($list->withQueryString()->links()); ?>

        </div>
      </div>
    </div>
  </div>

  
  <div class="grid grid-cols-1 gap-3 md:hidden">
    <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $stCls = $statusClasses[$r->status] ?? 'text-slate-700 bg-slate-50 ring-slate-200';
        $prio  = strtolower($r->priority ?? 'medium');
        $prCls = $priorityClasses[$prio] ?? $priorityClasses['medium'];
      ?>
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-xs text-slate-500">#<?php echo e($r->id); ?></div>
              <a href="<?php echo e(route('maintenance.requests.show', $r)); ?>"
                 class="block font-semibold text-slate-900 hover:underline">
                <span class="line-clamp-2"><?php echo e($r->title); ?></span>
              </a>
              <p class="mt-1 text-sm text-slate-600 line-clamp-3"><?php echo e(Str::limit($r->description ?? '', 140)); ?></p>
            </div>
            <span class="<?php echo e($chip); ?> <?php echo e($stCls); ?>"><?php echo e($human($r->status)); ?></span>
          </div>

          <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
            <div class="text-slate-500">Priority</div>
            <div><span class="<?php echo e($chip); ?> <?php echo e($prCls); ?>"><?php echo e(ucfirst($prio)); ?></span></div>
            <div class="text-slate-500">Asset</div>
            <div class="text-slate-800 truncate">#<?php echo e($r->asset_id); ?> — <?php echo e($r->asset->name ?? '-'); ?></div>
            <div class="text-slate-500">Updated</div>
            <div class="text-slate-800"><?php echo e(optional($r->updated_at)->format('Y-m-d H:i')); ?></div>
          </div>

          <div class="mt-4">
            <a href="<?php echo e(route('maintenance.requests.show', $r)); ?>" class="w-full <?php echo e($btnGhost); ?> justify-center">View</a>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
        <div class="mx-auto mb-3 h-10 w-10 rounded-full border border-dashed border-slate-300"></div>
        <p class="text-sm text-slate-600 mb-3">No jobs found</p>
        <a href="<?php echo e(route('repairs.my_jobs')); ?>" class="<?php echo e($btnGhost); ?>">Clear filters</a>
      </div>
    <?php endif; ?>

    <div class="flex justify-center">
      <?php echo e($list->withQueryString()->links()); ?>

    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/repair/my-jobs.blade.php ENDPATH**/ ?>