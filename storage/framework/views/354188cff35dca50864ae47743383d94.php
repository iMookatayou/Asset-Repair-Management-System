
<?php $__env->startSection('title','Create Asset'); ?>

<?php $__env->startSection('page-header'); ?>
  
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create Asset
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            เพิ่มครุภัณฑ์ใหม่เข้าสู่ระบบ — โปรดระบุข้อมูลให้ครบถ้วนเพื่อความถูกต้องในการจัดเก็บ
          </p>
        </div>

        <a href="<?php echo e(route('assets.index')); ?>"
           class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back
        </a>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
    
    <?php if($errors->any()): ?>
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <p class="font-medium">มีข้อผิดพลาดในการบันทึกข้อมูล:</p>
        <ul class="mt-2 list-disc pl-5 text-sm">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('assets.store')); ?>"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <?php echo csrf_field(); ?>

      
      <div class="space-y-6">
        <div>
          <h2 class="text-base font-semibold text-slate-900">ข้อมูลหลัก</h2>
          <p class="text-sm text-slate-500">ระบุรหัสและชื่อครุภัณฑ์ให้ชัดเจน</p>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="asset_code" class="block text-sm font-medium text-slate-700">
                รหัสครุภัณฑ์ <span class="text-rose-600">*</span>
              </label>
              <input id="asset_code" name="asset_code" type="text" required autofocus autocomplete="off"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('asset_code')); ?>">
              <?php $__errorArgs = ['asset_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
              <label for="name" class="block text-sm font-medium text-slate-700">
                ชื่อครุภัณฑ์ <span class="text-rose-600">*</span>
              </label>
              <input id="name" name="name" type="text" required
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('name')); ?>">
              <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
          </div>
        </div>

        
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">การจัดประเภท / หน่วยงาน</h2>
          <p class="text-sm text-slate-500">เลือกหมวดหมู่และหน่วยงาน (ถ้ามี)</p>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="type" class="block text-sm font-medium text-slate-700">ประเภท (type)</label>
              <input id="type" name="type" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('type')); ?>">
            </div>
            <div>
              <label for="category" class="block text-sm font-medium text-slate-700">หมวด (legacy)</label>
              <input id="category" name="category" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('category')); ?>">
            </div>

            <?php if(isset($categories)): ?>
              <div>
                <label for="category_id" class="block text-sm font-medium text-slate-700">หมวด (FK)</label>
                <select id="category_id" name="category_id"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600">
                  <option value="">— ไม่ระบุ —</option>
                  <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>" <?php if(old('category_id') == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            <?php endif; ?>

            <?php if(isset($departments)): ?>
              <div>
                <label for="department_id" class="block text-sm font-medium text-slate-700">หน่วยงาน</label>
                <select id="department_id" name="department_id"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600">
                  <option value="">— ไม่ระบุ —</option>
                  <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($d->id); ?>" <?php if(old('department_id') == $d->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            <?php endif; ?>
          </div>
        </div>

        
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">สเปก / ตำแหน่ง / สถานะ</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="brand" class="block text-sm font-medium text-slate-700">ยี่ห้อ</label>
              <input id="brand" name="brand" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('brand')); ?>">
            </div>
            <div>
              <label for="model" class="block text-sm font-medium text-slate-700">รุ่น</label>
              <input id="model" name="model" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('model')); ?>">
            </div>
            <div>
              <label for="serial_number" class="block text-sm font-medium text-slate-700">Serial</label>
              <input id="serial_number" name="serial_number" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('serial_number')); ?>">
            </div>
            <div>
              <label for="location" class="block text-sm font-medium text-slate-700">ที่ตั้ง</label>
              <input id="location" name="location" type="text"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('location')); ?>">
            </div>
            <div>
              <label for="status" class="block text-sm font-medium text-slate-700">สถานะ</label>
              <select id="status" name="status"
                      class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600">
                <?php $statuses = ['active'=>'ใช้งาน','in_repair'=>'ซ่อม','disposed'=>'จำหน่าย']; ?>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($k); ?>" <?php if(old('status','active') === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>
        </div>

        
        <div class="pt-4 border-t border-slate-200">
          <h2 class="text-base font-semibold text-slate-900">อายุการใช้งาน</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="purchase_date" class="block text-sm font-medium text-slate-700">วันที่ซื้อ</label>
              <input id="purchase_date" name="purchase_date" type="date"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('purchase_date')); ?>">
            </div>
            <div>
              <label for="warranty_expire" class="block text-sm font-medium text-slate-700">หมดประกัน</label>
              <input id="warranty_expire" name="warranty_expire" type="date"
                     class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-600 focus:ring-emerald-600"
                     value="<?php echo e(old('warranty_expire')); ?>">
            </div>
          </div>
        </div>
      </div>

      
      <div class="mt-6 flex justify-end gap-2">
        <a href="<?php echo e(route('assets.index')); ?>"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50">
          Cancel
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          Save
        </button>
      </div>
    </form>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/assets/create.blade.php ENDPATH**/ ?>