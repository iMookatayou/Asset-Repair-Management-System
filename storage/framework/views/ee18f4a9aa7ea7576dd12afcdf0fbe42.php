

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

  // สไตล์ป้ายสถานะ (ราชการ: ring-1 + bg-white)
  $statusBadge = fn(?string $s) => match(strtolower((string)$s)) {
    'active'     => 'ring-emerald-300 text-emerald-800 bg-white',
    'in_repair'  => 'ring-amber-300 text-amber-800 bg-white',
    'disposed'   => 'ring-rose-300 text-rose-800 bg-white',
    default      => 'ring-zinc-300 text-zinc-700 bg-white',
  };
?>


<div class="pt-3 md:pt-4"></div>

<div class="w-full px-4 md:px-6 lg:px-8 flex flex-col gap-5">

  
  <div class="rounded-lg border border-zinc-300 bg-white">
    <div class="px-5 py-4">
      <div class="flex flex-wrap items-start justify-between gap-4">

        <div class="flex items-start gap-3">
          <div class="grid h-9 w-9 place-items-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M6 11h8m-8 4h12M4 5v14a2 2 0 0 0 2 2h12l2-2V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2Z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-[17px] font-semibold text-zinc-900">Assets</h1>
            <p class="text-[13px] text-zinc-600">ทรัพย์สินครุภัณฑ์ • ค้นหา กรอง และจัดการข้อมูล</p>
          </div>
        </div>

        <a href="<?php echo e(route('assets.create')); ?>"
           class="inline-flex items-center gap-2 rounded-md border border-emerald-700 bg-emerald-700 px-4 py-2 text-[13px] font-medium text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
          </svg>
          เพิ่มทรัพย์สิน
        </a>
      </div>

      
      <div class="mt-4 h-px bg-zinc-200"></div>

      
  <form method="GET" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12" role="search" aria-label="Filter assets">
        
        <div class="md:col-span-5 min-w-0">
          <label for="q" class="mb-1 block text-[12px] text-zinc-600">คำค้นหา</label>
          <div class="relative">
            <input id="q" type="text" name="q" value="<?php echo e(request('q')); ?>"
                   placeholder="เช่น รหัส/ชื่อ/Serial number"
                   class="w-full rounded-md border border-zinc-300 pl-12 pr-3 py-2 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-zinc-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
          </div>
        </div>

        
        <div class="md:col-span-2">
          <label for="status" class="mb-1 block text-[12px] text-zinc-600">สถานะ</label>
          <?php $statuses = ['' => 'ทั้งหมด','active'=>'พร้อมใช้งาน','in_repair'=>'อยู่ระหว่างซ่อม','disposed'=>'จำหน่าย']; ?>
          <select id="status" name="status"
                  class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($k); ?>" <?php if(request('status')===$k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="md:col-span-2">
          <label for="category_id" class="mb-1 block text-[12px] text-zinc-600">หมวดหมู่</label>
          <select id="category_id" name="category_id" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            <option value="">ทั้งหมด</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($c->id); ?>" <?php if((string)request('category_id') === (string)$c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="md:col-span-2">
          <label for="department_id" class="mb-1 block text-[12px] text-zinc-600">หน่วยงาน</label>
          <select id="department_id" name="department_id" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
            <option value="">ทั้งหมด</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($d['id']); ?>" <?php if((string)request('department_id') === (string)$d['id']): echo 'selected'; endif; ?>><?php echo e($d['display_name']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div class="md:col-span-2">
          <label for="type" class="mb-1 block text-[12px] text-zinc-600">ประเภท</label>
          <input id="type" name="type" value="<?php echo e(request('type')); ?>" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" placeholder="เช่น Computer, Printer" />
        </div>
        <div class="md:col-span-2">
          <label for="location" class="mb-1 block text-[12px] text-zinc-600">ที่ตั้ง</label>
          <input id="location" name="location" value="<?php echo e(request('location')); ?>" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" placeholder="เช่น ER, IT Room" />
        </div>

        
        <div class="md:col-span-1 flex items-end justify-end gap-2">
          <?php if(request()->hasAny(['q','status','category_id','sort_by','sort_dir'])): ?>
            <a href="<?php echo e(route('assets.index')); ?>"
               class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50">
              ล้างค่า
            </a>
          <?php endif; ?>
          <button class="rounded-md border border-emerald-700 bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800">
            กรองข้อมูล
          </button>
        </div>
      </form>
    </div>
  </div>

  
  <div class="hidden md:block rounded-lg border border-zinc-300 bg-white overflow-hidden">
    <div class="relative overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-zinc-50">
          <tr class="text-zinc-700 border-b border-zinc-200">
            <th class="p-3 text-left font-medium"><?php echo $th('id','#'); ?></th>
            <th class="p-3 text-left font-medium"><?php echo $th('asset_code','Asset Code'); ?></th>
            <th class="p-3 text-left font-medium"><?php echo $th('name','Name'); ?></th>
            <th class="p-3 text-left font-medium hidden xl:table-cell"><?php echo $th('category','Category'); ?></th>
            <th class="p-3 text-left font-medium hidden lg:table-cell">Location</th>
            <th class="p-3 text-left font-medium"><?php echo $th('status','Status'); ?></th>
            <th class="p-3 text-right font-medium">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="align-top hover:bg-zinc-50 border-b last:border-0">
              <td class="p-3 text-zinc-700"><?php echo e($a->id); ?></td>
              <td class="p-3 font-medium text-zinc-900"><?php echo e($a->asset_code); ?></td>
              <td class="p-3">
                <a class="text-emerald-700 hover:underline" href="<?php echo e(route('assets.show',$a)); ?>"><?php echo e($a->name); ?></a>
                <div class="text-xs text-zinc-500">S/N: <?php echo e($a->serial_number ?? '—'); ?></div>
              </td>
              <td class="p-3 hidden xl:table-cell text-zinc-700"><?php echo e(optional($a->categoryRef)->name ?? '—'); ?></td>
              <td class="p-3 hidden lg:table-cell text-zinc-700"><?php echo e($a->location ?? '—'); ?></td>
              <td class="p-3">
                <span class="rounded-full px-2 py-1 text-[11px] ring-1 <?php echo e($statusBadge($a->status)); ?>">
                  <?php echo e(ucfirst(str_replace('_',' ', $a->status))); ?>

                </span>
              </td>
              <td class="p-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  <a href="<?php echo e(route('assets.show',$a)); ?>"
              class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 px-2.5 md:px-3 py-1.5 text-[11px] md:text-xs font-medium text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-600 whitespace-nowrap min-w-[84px] justify-center" aria-label="ดูรายละเอียดทรัพย์สิน">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                    <span class="hidden sm:inline">ดูรายละเอียด</span><span class="sm:hidden">ดู</span>
                  </a>
                  <a href="<?php echo e(route('assets.edit',$a)); ?>"
              class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 px-2.5 md:px-3 py-1.5 text-[11px] md:text-xs font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 whitespace-nowrap min-w-[74px] justify-center" aria-label="แก้ไขทรัพย์สิน">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                    <span class="hidden sm:inline">แก้ไข</span><span class="sm:hidden">แก้</span>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="p-10 text-center text-zinc-600">ไม่พบทรัพย์สิน</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  
  <div class="md:hidden grid grid-cols-1 gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="rounded-lg border border-zinc-300 bg-white p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-xs text-zinc-500">#<?php echo e($a->id); ?> — <?php echo e($a->asset_code); ?></div>
            <a class="font-medium text-zinc-900 hover:underline" href="<?php echo e(route('assets.show',$a)); ?>"><?php echo e($a->name); ?></a>
            <div class="text-xs text-zinc-500">S/N: <?php echo e($a->serial_number ?? '—'); ?></div>
          </div>
          <span class="rounded-full px-2 py-1 text-[11px] ring-1 <?php echo e($statusBadge($a->status)); ?>">
            <?php echo e(ucfirst(str_replace('_',' ', $a->status))); ?>

          </span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-zinc-500">Category</div>
          <div><?php echo e(optional($a->categoryRef)->name ?? '—'); ?></div>
          <div class="text-zinc-500">Location</div>
          <div><?php echo e($a->location ?? '—'); ?></div>
        </div>

        <div class="mt-3 flex justify-end gap-2">
          <a href="<?php echo e(route('assets.show',$a)); ?>"
         class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 px-3 py-2 text-xs font-medium text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-600" aria-label="ดูรายละเอียดทรัพย์สิน">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
            ดู
          </a>
          <a href="<?php echo e(route('assets.edit',$a)); ?>"
         class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 px-3 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600" aria-label="แก้ไขทรัพย์สิน">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
            แก้ไข
          </a>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-lg border border-zinc-300 bg-white p-8 text-center text-zinc-600">
        ไม่พบทรัพย์สิน
      </div>
    <?php endif; ?>
  </div>

  
  <div class="mt-4">
    <?php echo e($assets->links()); ?>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/assets/index.blade.php ENDPATH**/ ?>