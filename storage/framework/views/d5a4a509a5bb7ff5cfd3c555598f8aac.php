
<?php $__env->startSection('title', $thread->title); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-4">
  <div class="section-card">
    <div class="section-head p-3"><?php echo e($thread->title); ?></div>

    <div class="section-body p-0">
      <div id="chatBox" class="h-[64vh] overflow-y-auto p-4 space-y-3 bg-white">
        <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="flex gap-2">
            <div class="shrink-0 w-8 h-8 rounded-full bg-slate-200 grid place-items-center text-xs">
              <?php echo e(strtoupper(mb_substr($m->user->name,0,1))); ?>

            </div>
            <div>
              <div class="text-sm">
                <span class="font-medium"><?php echo e($m->user->name); ?></span>
                <span class="text-xs text-gray-500">• <?php echo e($m->created_at->format('Y-m-d H:i')); ?></span>
              </div>
              <div class="text-[15px] leading-snug whitespace-pre-line"><?php echo e($m->body); ?></div>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      <?php if(!$thread->is_locked): ?>
        <form method="POST" action="<?php echo e(route('chat.messages.store',$thread)); ?>" class="border-t p-3 flex gap-2">
          <?php echo csrf_field(); ?>
          <input id="msgInput" name="body" required maxlength="3000" placeholder="พิมพ์ข้อความ..."
                 class="flex-1 rounded-lg border px-3 py-2">
          <button class="rounded-lg bg-[#0E2B51] text-white px-3 py-2">ส่ง</button>
        </form>
      <?php else: ?>
        <div class="border-t p-3 text-center text-gray-500">กระทู้นี้ถูกล็อก</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  // ===== Simple polling (อัปเดตทุก 2 วินาที) — อัปเกรดเป็น WebSocket ได้ภายหลัง
  const threadId = <?php echo e($thread->id); ?>;
  const box = document.getElementById('chatBox');
  let lastId = <?php echo e($messages->last()?->id ?? 0); ?>;
  let autoScroll = true;

  function appendMessage(m){
    const row = document.createElement('div');
    row.className = 'flex gap-2';
    row.innerHTML = `
      <div class="shrink-0 w-8 h-8 rounded-full bg-slate-200 grid place-items-center text-xs">
        ${ (m.user?.name || '?').slice(0,1).toUpperCase() }
      </div>
      <div>
        <div class="text-sm">
          <span class="font-medium">${ m.user?.name || 'Unknown' }</span>
          <span class="text-xs text-gray-500">• ${ new Date(m.created_at).toLocaleString() }</span>
        </div>
        <div class="text-[15px] leading-snug whitespace-pre-line"></div>
      </div>`;
    row.querySelector('.leading-snug').textContent = m.body;
    box.appendChild(row);
  }

  async function poll(){
    try{
      const res = await fetch(`<?php echo e(route('chat.messages',$thread)); ?>?after_id=${lastId}`);
      if(!res.ok) return;
      const data = await res.json();
      if(Array.isArray(data) && data.length){
        data.forEach(m => { appendMessage(m); lastId = Math.max(lastId, m.id); });
        if (autoScroll) box.scrollTop = box.scrollHeight;
      }
    }catch(e){ /* เงียบไว้ */ }
  }

  box.addEventListener('scroll', () => {
    const nearBottom = box.scrollTop + box.clientHeight >= box.scrollHeight - 30;
    autoScroll = nearBottom;
  });

  setInterval(poll, 2000);
  // เลื่อนลงล่างสุดครั้งแรก
  box.scrollTop = box.scrollHeight;
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/chat/show.blade.php ENDPATH**/ ?>