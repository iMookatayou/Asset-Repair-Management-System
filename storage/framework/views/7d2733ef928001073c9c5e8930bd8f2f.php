<?php $__env->startSection('title', 'Edit Profile'); ?>

<?php $__env->startSection('page-header'); ?>
  
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 
              1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 
              1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
              stroke="currentColor" stroke-width="1.5"
              stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            แก้ไขโปรไฟล์
          </h1>
          <p class="mt-1 text-sm text-slate-600">
            ปรับข้อมูลส่วนตัวของคุณ เช่น ชื่อ อีเมล และแผนก
          </p>
        </div>

        
        <a href="<?php echo e(route('profile.show')); ?>"
           class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back
        </a>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="mx-auto max-w-3xl py-6 space-y-5">

    <?php if(session('status')): ?>
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
        <?php echo e(session('status')); ?>

      </div>
    <?php endif; ?>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <form method="POST" action="<?php echo e(route('profile.update')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>

        <div>
          <label for="name" class="block text-sm font-medium text-slate-700">ชื่อ-นามสกุล</label>
          <input id="name" name="name" type="text" value="<?php echo e(old('name', $user->name)); ?>" required
                 class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 
                        focus:border-emerald-600 focus:ring-emerald-600 
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-400 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
          <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-slate-700">อีเมล</label>
          <input id="email" name="email" type="email" value="<?php echo e(old('email', $user->email)); ?>" required
                 class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 
                        focus:border-emerald-600 focus:ring-emerald-600 
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-400 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
          <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          <p class="mt-1 text-xs text-slate-500">เปลี่ยนอีเมลจะทำให้สถานะยืนยันอีเมลถูกรีเซ็ต</p>
        </div>

        <div>
          <label for="department" class="block text-sm font-medium text-slate-700">แผนก</label>
          <select id="department" name="department"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 
                         focus:border-emerald-600 focus:ring-emerald-600 
                         <?php $__errorArgs = ['department'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-400 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <option value="">— เลือกแผนก —</option>
            <?php $__currentLoopData = \App\Models\Department::orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($dept->code); ?>" <?php if(old('department', $user->department) == $dept->code): echo 'selected'; endif; ?>>
                <?php echo e($dept->name); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php $__errorArgs = ['department'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="pt-2">
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
            บันทึกการเปลี่ยนแปลง
          </button>
        </div>
      </form>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/profile/edit.blade.php ENDPATH**/ ?>