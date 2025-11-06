

<?php $__env->startSection('title','Community Chat'); ?>

<?php $__env->startSection('content'); ?>
  
  <style>
    .btn-hard{
      display:inline-flex;align-items:center;justify-content:center;
      height:44px;min-width:110px;padding:0 16px;border-radius:10px;
      font-weight:600;background:#059669;color:#fff;
      box-shadow: 0 1px 0 rgba(0,0,0,.02);
    }
    .btn-hard:hover{background:#047857}
    .btn-hard:active{transform:translateY(.5px)}
    .btn-outline{
      display:inline-flex;align-items:center;justify-content:center;
      height:40px;padding:0 12px;border-radius:10px;
      border:1px solid #CBD5E1;color:#0F172A;background:#fff;
    }
    .btn-outline:hover{background:#F8FAFC}
    .chip{
      display:inline-flex;align-items:center;gap:6px;
      height:24px;padding:0 8px;border-radius:999px;
      background:#F1F5F9;color:#334155;font-size:12px;font-weight:500;
    }
  </style>

  <div class="max-w-5xl mx-auto py-6 space-y-6">

    
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="px-5 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
          <div class="size-10 grid place-items-center rounded-xl bg-emerald-50 text-emerald-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M6 8h12M6 12h8M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v11l-3.5 3.5H6a2 2 0 0 1-2-2V5z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-semibold text-slate-900">Community Chat</h1>
            <p class="text-sm text-slate-600">ถาม-ตอบ แชร์ทริก แลกเปลี่ยนประสบการณ์กันได้ที่นี่</p>
          </div>
        </div>

        <form method="GET" action="<?php echo e(route('chat.index')); ?>"
              class="w-full md:w-[440px] flex items-center rounded-xl border border-slate-300 bg-white shadow-sm focus-within:ring-2 focus-within:ring-emerald-500">
          <input
            type="text"
            name="q"
            value="<?php echo e(request('q','')); ?>"
            placeholder="ค้นหากระทู้…"
            class="flex-1 px-3 h-11 text-sm bg-transparent outline-none border-0 rounded-l-xl"
            aria-label="Search threads"
          >
          <?php if(request('q')): ?>
            <a href="<?php echo e(route('chat.index')); ?>" class="btn-outline mr-2">Clear</a>
          <?php endif; ?>
          <button type="submit"
                  class="h-11 px-4 text-sm font-medium text-white bg-emerald-700 rounded-r-xl hover:bg-emerald-800 active:translate-y-[0.5px]">
            Search
          </button>
        </form>
      </div>
      <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
    </div>

    
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-5 space-y-3">
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-600">สร้างกระทู้ใหม่</div>
          <?php if(auth()->guard()->check()): ?>
            <div class="chip" title="คุณกำลังโพสต์ในนาม: <?php echo e(auth()->user()->name); ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/></svg>
              <?php echo e(auth()->user()->name); ?>

            </div>
          <?php endif; ?>
        </div>

        <form method="POST" action="<?php echo e(route('chat.store')); ?>" class="space-y-3">
          <?php echo csrf_field(); ?>
          <div class="flex flex-col sm:flex-row gap-3">
            <input
              name="title"
              required
              maxlength="180"
              class="w-full sm:flex-1 rounded-lg border border-slate-300 px-3 h-11 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
              placeholder='หัวข้อกระทู้ เช่น "เลือกเครื่องพิมพ์เวรดึกยังไงให้ไม่งอแง?"'
              value="<?php echo e(old('title')); ?>"
              aria-label="Thread title"
            >
            <button type="submit" class="btn-hard" aria-label="Post thread">
              โพสต์
            </button>
          </div>

          <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-sm text-rose-600"><?php echo e($message); ?></p>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

          <?php if(session('status')): ?>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
              <?php echo e(session('status')); ?>

            </div>
          <?php endif; ?>
        </form>
      </div>
    </div>

    
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <div class="px-5 py-3 border-b border-slate-200 text-sm font-medium text-slate-800">
        กระทูล่าสุด
        <?php if(request('q')): ?>
          <span class="ml-2 text-slate-500 font-normal">ผลลัพธ์สำหรับ “<?php echo e(request('q')); ?>”</span>
        <?php endif; ?>
      </div>

      <?php
        /**
         * Controller ควรเตรียม:
         * ChatThread::query()
         *   ->with('author:id,name')
         *   ->withCount('messages')
         *   ->with(['latestMessage' => fn($q) => $q->select('id','chat_thread_id','user_id','body','created_at')
         *                                         ->latest('created_at')->limit(1)])
         *   ->when(request('q'), fn($q,$s) => $q->where('title','like',"%{$s}%"))
         *   ->orderByDesc('created_at')
         *   ->paginate(15);
         */
      ?>

      <?php if($threads->count()): ?>
        <ul class="divide-y divide-slate-200">
          <?php $__currentLoopData = $threads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $th): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li>
              <a href="<?php echo e(route('chat.show', $th)); ?>" class="block px-5 py-4 hover:bg-slate-50 focus:bg-slate-50">
                <div class="flex items-start gap-3">
                  
                  <?php if($th->is_locked): ?>
                    <span class="chip shrink-0" title="Thread locked">
                      <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M7 10V7a5 5 0 0 1 10 0v3M6 10h12v9a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-9z"/>
                      </svg>
                      Locked
                    </span>
                  <?php endif; ?>

                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                      <h2 class="font-medium text-slate-900 line-clamp-1"><?php echo e($th->title); ?></h2>
                      <span class="chip" title="จำนวนข้อความในกระทู้นี้">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M7 8h10M7 12h7M5 4h14a2 2 0 012 2v10l-4 4H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                        </svg>
                        <?php echo e($th->messages_count ?? 0); ?>

                      </span>
                    </div>

                    <div class="mt-1 text-xs text-slate-600">
                      โดย <?php echo e($th->author->name ?? 'Unknown'); ?>

                      • <?php echo e($th->created_at?->diffForHumans()); ?>

                    </div>

                    
                    <?php
                      $last = $th->latestMessage ?? null;
                    ?>
                    <?php if($last): ?>
                      <div class="mt-2 text-sm text-slate-700 line-clamp-2">
                        <span class="font-medium"><?php echo e(optional($last->user)->name ?? 'Someone'); ?>: </span>
                        <?php echo e(\Illuminate\Support\Str::limit(strip_tags($last->body), 180)); ?>

                        <span class="text-xs text-slate-500">— <?php echo e($last->created_at?->diffForHumans()); ?></span>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="shrink-0 self-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 5l7 7-7 7"/>
                    </svg>
                  </div>
                </div>
              </a>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <div class="px-5 py-4 border-t border-slate-200">
          <div class="flex justify-center">
            <?php echo e($threads->withQueryString()->links()); ?>

          </div>
        </div>
      <?php else: ?>
        
        <div class="p-10 text-center">
          <div class="mx-auto mb-3 size-12 grid place-items-center rounded-full bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M7 8h10M7 12h7M5 4h14a2 2 0 012 2v10l-4 4H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>
            </svg>
          </div>
          <div class="font-medium text-slate-800">ยังไม่มีกระทู้</div>
          <p class="text-sm text-slate-600">เริ่มพูดคุยกันได้เลยด้วยการสร้างกระทู้ใหม่ด้านบน</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/chat/index.blade.php ENDPATH**/ ?>