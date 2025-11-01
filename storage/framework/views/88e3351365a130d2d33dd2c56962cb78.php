<?php $__env->startSection('title', 'Create account'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('register')); ?>" class="space-y-4">
    <?php echo csrf_field(); ?>

    
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
        <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" required autofocus
               autocomplete="name"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-[#0E2B51] focus:ring-[#0E2B51]">
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    
    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required
               autocomplete="username"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-[#0E2B51] focus:ring-[#0E2B51]">
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    
    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <input id="password" type="password" name="password" required
               autocomplete="new-password"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-[#0E2B51] focus:ring-[#0E2B51]">
        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
               autocomplete="new-password"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                      focus:border-[#0E2B51] focus:ring-[#0E2B51]">
        <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    
    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm">
            <a href="<?php echo e(route('login')); ?>" class="text-[#0E2B51] hover:underline">Already have an account?</a>
        </div>

        <button class="h-11 px-5 rounded-lg bg-[#0E2B51] text-white font-medium
                       hover:opacity-95 focus:ring-2 focus:ring-offset-2 focus:ring-[#0E2B51]">
            Create account
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/auth/register.blade.php ENDPATH**/ ?>