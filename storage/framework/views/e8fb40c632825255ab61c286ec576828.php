<?php $__env->startSection('title', 'Maintenance Requests'); ?>

<?php $__env->startSection('page-header'); ?>
  <div class="flex items-center justify-between">
    <a href="<?php echo e(route('maintenance.requests.create')); ?>"
       class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
      + New Request
    </a>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  
  <form method="GET" action="<?php echo e(route('maintenance.requests.index')); ?>"
        class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4" role="search" aria-label="Filter maintenance requests">
    <div>
      <label for="q" class="block text-sm text-zinc-600">Search</label>
      <input id="q" type="text" name="q" value="<?php echo e(request('q')); ?>"
             placeholder="Search title/description…"
             class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500" />
    </div>

    <div>
      <label for="status" class="block text-sm text-zinc-600">Status</label>
      <select id="status" name="status"
              class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
        <option value="">All Status</option>
        <?php $__currentLoopData = ['pending'=>'Pending','in_progress'=>'In progress','completed'=>'Completed','cancelled'=>'Cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($k); ?>" <?php if(request('status') === $k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </div>

    <div>
      <label for="priority" class="block text-sm text-zinc-600">Priority</label>
      <select id="priority" name="priority"
              class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
        <option value="">All Priority</option>
        <?php $__currentLoopData = ['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($k); ?>" <?php if(request('priority') === $k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </div>

    <div class="flex items-end">
      <button type="submit"
              class="w-full rounded-lg border border-zinc-300 px-3 py-2 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        Apply
      </button>
    </div>
  </form>

  
  <div class="overflow-x-auto rounded-2xl border border-zinc-200">
    <table class="min-w-full text-sm">
      <thead class="bg-zinc-50">
        <tr class="text-left">
          <th scope="col" class="p-2 font-semibold text-zinc-700">#</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Title</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Priority</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Status</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Reporter</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Technician</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Requested</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr class="border-t">
            <td class="p-2 align-top"><?php echo e($row->id); ?></td>

            <td class="p-2 align-top">
              <div class="font-medium text-zinc-900"><?php echo e($row->title); ?></div>
              <?php if($row->description): ?>
                <div class="text-zinc-500 line-clamp-2"><?php echo e($row->description); ?></div>
              <?php endif; ?>
            </td>

            <td class="p-2 align-top">
              <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize
                           class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                             'border-emerald-200 bg-emerald-50 text-emerald-700' => $row->priority === 'low',
                             'border-sky-200 bg-sky-50 text-sky-700'             => $row->priority === 'medium',
                             'border-amber-200 bg-amber-50 text-amber-700'       => $row->priority === 'high',
                             'border-rose-200 bg-rose-50 text-rose-700'          => $row->priority === 'urgent',
                           ]); ?>"">
                <?php echo e(str_replace('_',' ', $row->priority)); ?>

              </span>
            </td>

            <td class="p-2 align-top">
              <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize
                           class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                             'border-zinc-300 bg-white text-zinc-700'            => $row->status === 'pending',
                             'border-blue-200 bg-blue-50 text-blue-700'          => $row->status === 'in_progress',
                             'border-emerald-200 bg-emerald-50 text-emerald-700' => $row->status === 'completed',
                             'border-zinc-300 bg-zinc-50 text-zinc-700'          => $row->status === 'cancelled',
                           ]); ?>"">
                <?php echo e(str_replace('_',' ', $row->status)); ?>

              </span>
            </td>

            <td class="p-2 align-top"><?php echo e($row->reporter->name ?? '-'); ?></td>
            <td class="p-2 align-top"><?php echo e($row->technician->name ?? '-'); ?></td>

            <td class="p-2 align-top">
              <?php $when = optional($row->request_date ?? $row->created_at); ?>
              <?php if($when): ?>
                <time datetime="<?php echo e($when->toIso8601String()); ?>"><?php echo e($when->format('Y-m-d H:i')); ?></time>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>

            <td class="p-2 align-top">
              <a href="<?php echo e(route('maintenance.requests.show', $row)); ?>"
                 class="text-emerald-700 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded">
                View
              </a>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="8" class="p-6 text-center text-zinc-500">No data</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <?php echo e($list->withQueryString()->links()); ?>

  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/maintenance/requests/index.blade.php ENDPATH**/ ?>