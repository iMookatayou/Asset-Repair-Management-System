<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
   <?php $__env->slot('header', null, []); ?> 
    <h2 class="font-semibold text-lg sm:text-xl text-gray-100 leading-tight">
      <?php echo e(__('Asset Repair Dashboard')); ?>

    </h2>
   <?php $__env->endSlot(); ?>

  
  <style>
    .chart-card { position: relative; height: 280px; }
    @media (min-width: 1024px) { .chart-card { height: 320px; } }
    .section-card { border-radius: .875rem; }
    .section-head { padding:.75rem 1rem; font-weight:600; }
    .section-body { padding:1rem; }
    .kpi { border-radius:.875rem; padding:1rem; }
    .kpi-title { font-size:.75rem; color:#9ca3af; }
    .kpi-value { font-size:1.625rem; font-weight:700; line-height:1.1; }
    .tbl th, .tbl td { padding:.55rem .75rem; font-size:.875rem; }
    .tbl thead th { font-size:.70rem; letter-spacing:.02em; }
  </style>

  <?php
    // กันตัวแปรว่าง + บีบข้อมูลให้อยู่รูปแบบที่คาดหวังเสมอ
    $stats        = is_array($stats ?? null) ? $stats : [];
    $monthlyTrend = is_array($monthlyTrend ?? null) ? $monthlyTrend : [];
    $byAssetType  = is_array($byAssetType ?? null) ? $byAssetType : [];
    $byDept       = is_array($byDept ?? null) ? $byDept : [];
    $recent       = is_iterable($recent ?? null) ? $recent : [];
  ?>

  <div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-4">

      
      <form method="GET" class="section-card border border-zinc-800 bg-[#0f1a2a] text-zinc-200">
        <div class="section-head border-b border-zinc-700">ตัวกรอง</div>
        <div class="section-body grid grid-cols-1 md:grid-cols-4 gap-3">
          <div>
            <label class="block text-xs text-zinc-400 mb-1">สถานะ</label>
            <select name="status" class="w-full rounded-md bg-zinc-900 border-zinc-700 text-zinc-100">
              <option value="">ทั้งหมด</option>
              <?php $__currentLoopData = [
                \App\Models\MaintenanceRequest::STATUS_PENDING => 'Pending',
                \App\Models\MaintenanceRequest::STATUS_IN_PROGRESS => 'In Progress',
                \App\Models\MaintenanceRequest::STATUS_COMPLETED => 'Completed',
                \App\Models\MaintenanceRequest::STATUS_CANCELLED => 'Cancelled',
              ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php if(request('status')===$val): echo 'selected'; endif; ?>><?php echo e($lbl); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div>
            <label class="block text-xs text-zinc-400 mb-1">จากวันที่</label>
            <input type="date" name="from" value="<?php echo e(request('from')); ?>"
                   class="w-full rounded-md bg-zinc-900 border-zinc-700 text-zinc-100">
          </div>
          <div>
            <label class="block text-xs text-zinc-400 mb-1">ถึงวันที่</label>
            <input type="date" name="to" value="<?php echo e(request('to')); ?>"
                   class="w-full rounded-md bg-zinc-900 border-zinc-700 text-zinc-100">
          </div>
          <div class="flex items-end">
            <button class="w-full md:w-auto px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white">
              กรอง
            </button>
          </div>
        </div>
      </form>

      
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-zinc-100">
          <div class="kpi-title">งานทั้งหมด</div>
          <div class="kpi-value"><?php echo e(number_format($stats['total'] ?? 0)); ?></div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-yellow-200">
          <div class="kpi-title">รอดำเนินการ</div>
          <div class="kpi-value"><?php echo e(number_format($stats['pending'] ?? 0)); ?></div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-sky-200">
          <div class="kpi-title">กำลังซ่อม</div>
          <div class="kpi-value"><?php echo e(number_format($stats['inProgress'] ?? 0)); ?></div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-emerald-200">
          <div class="kpi-title">เสร็จแล้ว</div>
          <div class="kpi-value"><?php echo e(number_format($stats['completed'] ?? 0)); ?></div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-zinc-100">
          <div class="kpi-title">ค่าใช้จ่ายเดือนนี้</div>
          <div class="kpi-value"><?php echo e(number_format($stats['monthCost'] ?? 0, 2)); ?> <span class="text-xs font-normal">THB</span></div>
        </div>
      </div>

      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
          <div class="section-head border-b border-zinc-700 text-zinc-100">แนวโน้มงานซ่อม (รายเดือน)</div>
          <div class="section-body">
            <div class="chart-card"><canvas id="trendChart"></canvas></div>
          </div>
        </div>

        <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
          <div class="section-head border-b border-zinc-700 text-zinc-100">สัดส่วนตามประเภททรัพย์สิน</div>
          <div class="section-body">
            <div class="chart-card"><canvas id="typePie"></canvas></div>
          </div>
        </div>
      </div>

      <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
        <div class="section-head border-b border-zinc-700 text-zinc-100">งานตามแผนก</div>
        <div class="section-body">
          <div class="chart-card"><canvas id="deptBar"></canvas></div>
        </div>
      </div>

      
      <div class="section-card border border-zinc-800 bg-[#0f1a2a] overflow-hidden">
        <div class="section-head border-b border-zinc-700 text-zinc-100">งานล่าสุด</div>
        <div class="section-body p-0">
          <div class="overflow-x-auto">
            <table class="tbl min-w-full divide-y divide-zinc-800 text-zinc-100">
              <thead class="bg-[#0b1422]">
                <tr>
                  <th class="text-left uppercase text-zinc-400">วันที่แจ้ง</th>
                  <th class="text-left uppercase text-zinc-400">ทรัพย์สิน</th>
                  <th class="text-left uppercase text-zinc-400">ผู้แจ้ง</th>
                  <th class="text-left uppercase text-zinc-400">สถานะ</th>
                  <th class="text-left uppercase text-zinc-400">ผู้รับผิดชอบ</th>
                  <th class="text-left uppercase text-zinc-400">เสร็จเมื่อ</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-zinc-800">
                <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <?php
                    $status = (string) data_get($t,'status','');
                    $badgeClass =
                      $status === \App\Models\MaintenanceRequest::STATUS_PENDING     ? 'bg-yellow-200/20 text-yellow-300' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_IN_PROGRESS ? 'bg-sky-200/20 text-sky-300' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_COMPLETED   ? 'bg-emerald-200/20 text-emerald-300' :
                                                                                         'bg-zinc-200/20 text-zinc-300'));
                  ?>
                  <tr class="hover:bg-[#0b1422]">
                    <td><?php echo e(optional(data_get($t,'request_date'))->format('Y-m-d H:i')); ?></td>
                    <td>#<?php echo e(e((string) data_get($t,'asset_id','-'))); ?> — <?php echo e(e((string) data_get($t,'asset.name','-'))); ?></td>
                    <td><?php echo e(e((string) data_get($t,'reporter.name','-'))); ?></td>
                    <td>
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] <?php echo e($badgeClass); ?>">
                        <?php echo e(ucfirst(str_replace('_',' ', $status))); ?>

                      </span>
                    </td>
                    <td><?php echo e(e((string) data_get($t,'technician.name','-'))); ?></td>
                    <td><?php echo e(optional(data_get($t,'completed_date'))->format('Y-m-d H:i') ?? '-'); ?></td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="6" class="text-center text-zinc-400 py-6">ไม่มีข้อมูล</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    (function(){
      try {
        const monthlyTrend = <?php echo json_encode($monthlyTrend, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>;
        const byAssetType  = <?php echo json_encode($byAssetType, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>;
        const byDept       = <?php echo json_encode($byDept, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>;

        const trendLabels = Array.isArray(monthlyTrend) ? monthlyTrend.map(i => i?.ym ?? '') : [];
        const trendCounts = Array.isArray(monthlyTrend) ? monthlyTrend.map(i => Number(i?.cnt ?? 0)) : [];
        const typeLabels  = Array.isArray(byAssetType)  ? byAssetType.map(i => i?.type ?? 'ไม่ระบุ') : [];
        const typeCounts  = Array.isArray(byAssetType)  ? byAssetType.map(i => Number(i?.cnt ?? 0)) : [];
        const deptLabels  = Array.isArray(byDept)       ? byDept.map(i => i?.dept ?? 'ไม่ระบุ') : [];
        const deptCounts  = Array.isArray(byDept)       ? byDept.map(i => Number(i?.cnt ?? 0)) : [];

        const mk = (id, cfg) => { const el = document.getElementById(id); if (el) new Chart(el, cfg); };

        mk('trendChart', {
          type: 'line',
          data: { labels: trendLabels, datasets: [{ label: 'จำนวนงาน', data: trendCounts, tension: .35 }]},
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display:false } },
            scales: { y: { beginAtZero:true, grid:{ color:'rgba(255,255,255,.06)' } },
                      x: { grid:{ color:'rgba(255,255,255,.04)' } } }
          }
        });

        mk('typePie', {
          type: 'pie',
          data: { labels: typeLabels, datasets: [{ data: typeCounts }]},
          options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12 } } } }
        });

        mk('deptBar', {
          type: 'bar',
          data: { labels: deptLabels, datasets: [{ label: 'งานซ่อม', data: deptCounts }]},
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display:false } },
            scales: { y:{ beginAtZero:true, grid:{ color:'rgba(255,255,255,.06)'} },
                     x:{ grid:{ display:false } } }
          }
        });
      } catch (e) {
        console?.error('chart init error', e);
      }
    })();
  </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\12tec\asset-repair-management-system\resources\views/repair/dashboard.blade.php ENDPATH**/ ?>