<?php $__env->startSection('title','Repair #'.$req->id); ?>

<?php $__env->startPush('head'); ?>
<style>
  :root{
    --bg:#0b0b0b;--card:#111213;--line:#25262a;--muted:#a1a1aa;--text:#e5e7eb;
    --field:#0f1012;--primary:#1d2430;--primary-line:#343740;
    --good:#16a34a;--warn:#eab308;--bad:#ef4444;--info:#3b82f6;
    --chip:#0f1115;--chip-line:#2b2e36;
    --accent:#4f46e5;--accent-2:#06b6d4;
  }
  /* layout base */
  body{background:var(--bg);color:var(--text)}
  .container{max-width:1100px;margin:0 auto;padding:18px}
  .page-head{
    position:sticky;top:0;z-index:30;
    background:linear-gradient(180deg,rgba(10,10,10,.9),rgba(10,10,10,.7) 60%,transparent);
    backdrop-filter: blur(6px);
    padding:12px 0 8px;border-bottom:1px solid #131313;
  }
  .titlebar{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .titlebar h1{font-size:20px;font-weight:700;display:flex;align-items:center;gap:10px;margin:0}
  .subrow{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
  .card{
    background:var(--card);border:1px solid var(--line);border-radius:16px;
    padding:16px;margin:14px 0;
    box-shadow:0 10px 30px rgba(0,0,0,.25), inset 0 1px 0 rgba(255,255,255,.02);
  }
  .card h3{margin:0 0 10px;font-size:16px;display:flex;align-items:center;gap:8px}
  .grid-2{display:grid;grid-template-columns:2fr 1fr;gap:14px}
  .grid-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:980px){.grid-2{grid-template-columns:1fr}.grid-row{grid-template-columns:1fr}}
  /* chips/badges */
  .chip{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;
    border:1px solid var(--chip-line);background:var(--chip);font-size:12px;color:#d1d5db}
  .chip.soft{background:linear-gradient(180deg,#0f1218,#0c0e12);border-color:#1f2430}
  .chip.good{border-color:var(--good);color:#86efac}
  .chip.warn{border-color:var(--warn);color:#fde68a}
  .chip.bad{border-color:var(--bad);color:#fecaca}
  .chip.info{border-color:var(--info);color:#bfdbfe}
  .chip.hl{border-color:#4f46e5;color:#c7d2fe}
  /* fields */
  label{display:flex;align-items:center;gap:8px;margin:6px 0 6px;color:#cbd5e1;font-size:13px}
  input,select,textarea{
    width:100%;padding:11px 12px;background:var(--field);color:#e5e7eb;
    border:1px solid #2b2f36;border-radius:10px;outline:none;
    transition:border .2s, box-shadow .2s, transform .02s;
  }
  input:focus,select:focus,textarea:focus{
    border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.25);
  }
  .btn{
    display:inline-flex;align-items:center;gap:8px;padding:11px 14px;border-radius:10px;
    border:1px solid var(--primary-line);background:
      linear-gradient(180deg,rgba(37,47,66,.9),rgba(24,30,44,.95));
    color:#fff;cursor:pointer;transition:transform .02s,filter .2s,opacity .2s;
    text-decoration:none;
  }
  .btn:hover{filter:brightness(1.08)}
  .btn:active{transform:translateY(1px)}
  .btn.ghost{background:#12141a;border-color:#2a2f38}
  .btn.pri{border-color:#475569;background:linear-gradient(180deg,#334155,#1f2937)}
  .btn.warn{border-color:#856c12;background:linear-gradient(180deg,#a07f12,#7c5f0f)}
  .btn.good{border-color:#1a6d33;background:linear-gradient(180deg,#15803d,#14532d)}
  .muted{color:var(--muted)}
  /* meta */
  .meta{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
  @media (max-width:980px){.meta{grid-template-columns:repeat(2,1fr)}}
  .kv .k{color:#a1a1aa;font-size:12px;margin-bottom:2px;display:flex;align-items:center;gap:6px}
  .kv .v{font-weight:600}
  /* timeline */
  .timeline{position:relative;padding-left:22px}
  .timeline:before{content:"";position:absolute;left:9px;top:6px;bottom:6px;width:2px;background:#212328;border-radius:2px}
  .t-item{position:relative;margin:12px 0;padding-left:12px}
  .dot{position:absolute;left:0;top:4px;width:18px;height:18px;border-radius:50%;
    background:#0e1116;border:2px solid #374151;display:flex;align-items:center;justify-content:center}
  .dot.good{border-color:var(--good)} .dot.warn{border-color:var(--warn)} .dot.bad{border-color:var(--bad)}
  /* attachments */
  .thumbs{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px}
  .thumb{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#0f1217}
  .thumb img{display:block;width:100%;height:130px;object-fit:cover}
  .thumb .cap{padding:6px 8px;font-size:12px;color:#cbd5e1;display:flex;align-items:center;justify-content:space-between;gap:6px}
  .cap a.chip{text-decoration:none}
  /* helpers */
  .hr{height:1px;background:linear-gradient(90deg,transparent,#1c1f25,transparent);margin:10px 0}
  .ribbon{
    background:linear-gradient(90deg,rgba(79,70,229,.3),rgba(6,182,212,.18) 60%,transparent);
    border:1px solid rgba(79,70,229,.25);padding:8px 12px;border-radius:12px
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-head">
  <div class="container">
    <div class="titlebar">
      <h1><i data-lucide="wrench"></i> Repair Detail #<?php echo e($req->id); ?></h1>
      <span class="chip soft">
        <i data-lucide="package"></i><?php echo e($req->asset->name ?? $req->asset_id); ?>

      </span>
      <?php
        $status = str_replace('_',' ',$req->status);
        $statusClass = match($req->status){
          'completed' => 'good', 'canceled' => 'bad',
          'assigned','in_progress' => 'warn', default => 'info'
        };
        $prioClass = match(strtolower($req->priority)){
          'high' => 'bad', 'medium' => 'warn', 'low' => 'good', default => 'info'
        };
      ?>
      <span class="chip <?php echo e($statusClass); ?>"><i data-lucide="workflow"></i><?php echo e($status); ?></span>
      <span class="chip <?php echo e($prioClass); ?>"><i data-lucide="flag-triangle-right"></i><?php echo e($req->priority); ?></span>

      <div style="margin-left:auto;display:flex;gap:8px">
        <a class="btn ghost" href="<?php echo e(route('maintenance.requests.index')); ?>"><i data-lucide="arrow-left"></i> กลับรายการ</a>
      </div>
    </div>
    <?php if(session('ok')): ?>
      <div class="subrow"><span class="chip good"><i data-lucide="check-circle-2"></i><?php echo e(session('ok')); ?></span></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
      <div class="subrow"><span class="chip bad"><i data-lucide="alert-triangle"></i> ดำเนินการไม่สำเร็จ</span></div>
    <?php endif; ?>
  </div>
</div>

<div class="container">
  <div class="card">
    <div class="meta">
      <div class="kv">
        <div class="k"><i data-lucide="hash"></i>หมายเลข</div>
        <div class="v">#<?php echo e($req->id); ?></div>
      </div>
      <div class="kv">
        <div class="k"><i data-lucide="user-2"></i>ผู้แจ้ง</div>
        <div class="v"><?php echo e($req->reporter_name ?? '-'); ?></div>
      </div>
      <div class="kv">
        <div class="k"><i data-lucide="package"></i>ทรัพย์สิน</div>
        <div class="v"><?php echo e($req->asset->name ?? $req->asset_id); ?></div>
      </div>
      <div class="kv">
        <div class="k"><i data-lucide="map-pin"></i>สถานที่</div>
        <div class="v"><?php echo e($req->location ?? '-'); ?></div>
      </div>
    </div>

    <div class="grid-2" style="margin-top:12px">
      <div>
        <div class="k"><i data-lucide="type"></i>หัวข้อ</div>
        <div class="v" style="font-size:15px"><?php echo e($req->title); ?></div>
        <div class="hr"></div>
        <div class="k"><i data-lucide="file-text"></i>รายละเอียด</div>
        <div class="prose prose-invert" style="margin-top:4px"><?php echo e($req->description ?: '-'); ?></div>
      </div>
      <div>
        <div class="k"><i data-lucide="calendar-clock"></i>เวลา</div>
        <?php $requestedAt = optional($req->request_date ?? $req->created_at); ?>
        <div class="ribbon" style="margin-top:6px;display:grid;gap:6px">
          <span class="chip info">
            <i data-lucide="calendar-search"></i>
            Requested:
            <?php if($requestedAt): ?>
              <time datetime="<?php echo e($requestedAt->toIso8601String()); ?>"><?php echo e($requestedAt->format('Y-m-d H:i')); ?></time>
            <?php else: ?> - <?php endif; ?>
          </span>
          <?php if($req->assigned_date): ?>
            <span class="chip warn">
              <i data-lucide="calendar-range"></i>
              Assigned:
              <time datetime="<?php echo e($req->assigned_date->toIso8601String()); ?>"><?php echo e($req->assigned_date->format('Y-m-d H:i')); ?></time>
            </span>
          <?php endif; ?>
          <?php if($req->completed_date): ?>
            <span class="chip good">
              <i data-lucide="calendar-check-2"></i>
              Done:
              <time datetime="<?php echo e($req->completed_date->toIso8601String()); ?>"><?php echo e($req->completed_date->format('Y-m-d H:i')); ?></time>
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  
  <div class="card">
    <h3><i data-lucide="settings-2"></i> ดำเนินการ</h3>
    <form method="post"
          action="<?php echo e(route('maintenance.requests.transition', $req)); ?>"
          class="grid-row" style="margin-top:6px" aria-label="Change request status form">
      <?php echo csrf_field(); ?>

      <div>
        <label for="action"><i data-lucide="workflow"></i> Action</label>
        <select id="action" name="action" required aria-required="true">
          <option value="" disabled selected>เลือกการดำเนินการ…</option>
          <option value="assign" <?php if(old('action')==='assign'): echo 'selected'; endif; ?>>assign</option>
          <option value="start" <?php if(old('action')==='start'): echo 'selected'; endif; ?>>start</option>
          <option value="complete" <?php if(old('action')==='complete'): echo 'selected'; endif; ?>>complete</option>
          <option value="cancel" <?php if(old('action')==='cancel'): echo 'selected'; endif; ?>>cancel</option>
        </select>
      </div>

      <div id="techWrap" style="display:none">
        <label for="technician_id"><i data-lucide="user-round"></i> Technician ID</label>
        <input id="technician_id" type="number" name="technician_id" placeholder="เช่น 5" inputmode="numeric" />
      </div>

      <div style="grid-column:1/-1">
        <label for="remark"><i data-lucide="message-square"></i> Remark</label>
        <input id="remark" type="text" name="remark" placeholder="บันทึกเพิ่มเติม (optional)" />
      </div>

      <div style="grid-column:1/-1;display:flex;gap:8px;flex-wrap:wrap">
        <button type="submit" class="btn pri"><i data-lucide="save"></i> บันทึก</button>
        <a class="btn ghost" href="<?php echo e(route('maintenance.requests.index')); ?>"><i data-lucide="arrow-left"></i> กลับรายการ</a>
      </div>
    </form>
  </div>

  
  <div class="card">
    <h3><i data-lucide="image-up"></i> ไฟล์แนบ</h3>
    <form method="post" enctype="multipart/form-data"
          action="<?php echo e(route('maintenance.requests.attachments', $req)); ?>"
          class="grid-row" style="margin-top:6px" aria-label="Upload attachment form">
      <?php echo csrf_field(); ?>
      <div>
        <label for="att_type"><i data-lucide="badge-info"></i> ประเภท</label>
        <select id="att_type" name="type">
          <option value="before">before</option>
          <option value="after">after</option>
          <option value="other" selected>other</option>
        </select>
      </div>
      <div>
        <label for="file"><i data-lucide="paperclip"></i> ไฟล์</label>
        <input id="file" type="file" name="file" required aria-required="true" />
      </div>
      <div style="grid-column:1/-1">
        <button type="submit" class="btn"><i data-lucide="upload-cloud"></i> อัปโหลด</button>
      </div>
    </form>

    <?php if($req->attachments->count()): ?>
      <div class="thumbs" style="margin-top:10px">
        <?php $__currentLoopData = $req->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $name = $att->original_name ?? basename($att->file_path ?? $att->path ?? '');
            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $isImg = in_array($ext,['jpg','jpeg','png','gif','webp','bmp']);
            $url = isset($att->file_path) ? asset('storage/'.$att->file_path) : (isset($att->path) ? asset('storage/'.$att->path) : '#');
            $tag = $att->file_type ?? $att->type ?? 'other';
          ?>
          <figure class="thumb">
            <?php if($isImg && $url !== '#'): ?>
              <a href="<?php echo e($url); ?>" target="_blank" rel="noopener">
                <img src="<?php echo e($url); ?>" alt="<?php echo e($name); ?>">
              </a>
            <?php else: ?>
              <div style="height:130px;display:flex;align-items:center;justify-content:center;color:#a1a1aa">
                <i data-lucide="file"></i> <?php echo e(strtoupper($ext ?: 'FILE')); ?>

              </div>
            <?php endif; ?>
            <figcaption class="cap">
              <span class="chip"><i data-lucide="tag"></i><?php echo e($tag); ?></span>
              <?php if($url !== '#'): ?>
                <a class="chip" href="<?php echo e($url); ?>" target="_blank" rel="noopener"><i data-lucide="download"></i> เปิด</a>
              <?php endif; ?>
            </figcaption>
          </figure>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php else: ?>
      <p class="muted" style="margin-top:6px">ไม่มีไฟล์แนบ</p>
    <?php endif; ?>
  </div>

  
  <div class="card">
    <h3><i data-lucide="timeline"></i> ประวัติการดำเนินการ</h3>
    <div class="timeline" style="margin-top:6px">
      <?php $__empty_1 = true; $__currentLoopData = $req->logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
          $cls = match($log->action) {
            'complete_request' => 'good',
            'cancel_request'   => 'bad',
            'assign_technician','start_request' => 'warn',
            default => ''
          };
        ?>
        <article class="t-item">
          <div class="dot <?php echo e($cls); ?>"><i data-lucide="dot"></i></div>
          <header style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <strong>#<?php echo e($log->id); ?></strong>
            <span class="chip"><i data-lucide="workflow"></i><?php echo e($log->action); ?></span>
            <span class="chip">
              <i data-lucide="calendar-clock"></i>
              <time datetime="<?php echo e($log->created_at->toIso8601String()); ?>"><?php echo e($log->created_at->format('Y-m-d H:i')); ?></time>
            </span>
            <?php if($log->user_id): ?>
              <span class="chip"><i data-lucide="user"></i><?php echo e($log->user_id); ?></span>
            <?php endif; ?>
          </header>
          <?php if($log->note): ?>
            <p style="margin-top:6px"><?php echo e($log->note); ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="muted">ยังไม่มีบันทึก</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons({attrs:{width:18,height:18, 'stroke-width':1.8}});
  // toggle technician input visibility/required by action
  const actionSel = document.getElementById('action');
  const techWrap  = document.getElementById('techWrap');
  const techInput = document.getElementById('technician_id');
  function syncTech(){
    const show = actionSel?.value === 'assign';
    if(!techWrap) return;
    techWrap.style.display = show ? '' : 'none';
    if(techInput){
      techInput.required = show;
      techInput.toggleAttribute('aria-required', show);
      if(!show) techInput.value = '';
    }
  }
  actionSel?.addEventListener('change', syncTech);
  syncTech();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/maintenance/requests/show.blade.php ENDPATH**/ ?>