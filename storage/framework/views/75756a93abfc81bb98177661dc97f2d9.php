<?php $__env->startSection('title', 'Asset Repair Dashboard Compact'); ?>


<?php $__env->startSection('topbadges'); ?>
  <?php
    $stats = array_replace(['total'=>0,'pending'=>0,'inProgress'=>0,'completed'=>0,'monthCost'=>0], $stats ?? []);
  ?>
  <span class="status-badge status-total">Total: <strong><?php echo e(number_format($stats['total'])); ?></strong></span>
  <span class="status-badge status-pending">Pending: <strong><?php echo e(number_format($stats['pending'])); ?></strong></span>
  <span class="status-badge status-progress">In&nbsp;progress: <strong><?php echo e(number_format($stats['inProgress'])); ?></strong></span>
  <span class="status-badge status-done">Completed: <strong><?php echo e(number_format($stats['completed'])); ?></strong></span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  
  <style>
    .chart-card { position: relative; height: 220px; }
    @media (min-width: 1024px) { .chart-card { height: 260px; } }
    .section-card { border-radius: .875rem; background:#fff; border:1px solid #e5e7eb; }
    .section-head { padding:.6rem .9rem; font-weight:600; border-bottom:1px solid #e5e7eb; color:#111827; }
    .section-body { padding:.9rem; }
    .kpi { border-radius:.875rem; padding:.8rem; background:#fff; border:1px solid #e5e7eb; }
    .kpi-title { font-size:.72rem; color:#6b7280; }
    .kpi-value { font-size:1.35rem; font-weight:700; line-height:1.1; color:#111827; }
    .tbl th, .tbl td { padding:.5rem .6rem; font-size:.82rem; color:#111827; }
    .tbl thead th { font-size:.68rem; letter-spacing:.02em; color:#6b7280; }
    .empty-state { display:flex; align-items:center; justify-content:center; height:220px; color:#9ca3af; font-size:.9rem }
    @media (min-width:1024px){ .empty-state{ height:260px; } }
  </style>

  <?php
    // ===== Normalize incoming collections =====
    $monthlyTrend = is_iterable($monthlyTrend ?? null) ? collect($monthlyTrend) : collect();
    $byAssetType  = is_iterable($byAssetType  ?? null) ? collect($byAssetType)  : collect();
    $byDept       = is_iterable($byDept       ?? null) ? collect($byDept)       : collect();
    $recent       = is_iterable($recent       ?? null) ? collect($recent)       : collect();

    $monthlyTrend = $monthlyTrend->take(6)->values();
    $byAssetType  = $byAssetType->take(9)->values();
    $byDept       = $byDept->take(8)->values();

    $intVal   = fn($v)=> is_numeric($v) ? (int)$v : 0;
    $strVal   = fn($v,$f='')=> is_string($v) && $v!=='' ? $v : $f;

    $trendLabels = $monthlyTrend->map(fn($i)=> $strVal(is_array($i)?($i['ym']??''):($i->ym??'')))->all();
    $trendCounts = $monthlyTrend->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $typeLabels  = $byAssetType->map(fn($i)=> $strVal(is_array($i)?($i['type']??'Unspecified'):($i->type??'Unspecified'),'Unspecified'))->all();
    $typeCounts  = $byAssetType->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $deptLabels  = $byDept->map(fn($i)=> $strVal(is_array($i)?($i['dept']??'Unspecified'):($i->dept??'Unspecified'),'Unspecified'))->all();
    $deptCounts  = $byDept->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $get = function($row, $key, $fallback='-'){
      if (is_array($row))  return data_get($row, $key, $fallback);
      if (is_object($row)) return data_get((array)$row, $key, $fallback);
      return $fallback;
    };
  ?>

  <div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-4">

      
      <form method="GET" class="section-card" aria-label="Filters">
        <div class="section-head">Filters</div>
        <div class="section-body grid grid-cols-2 md:grid-cols-5 gap-3">
          <div>
            <label for="f_status" class="block text-xs text-gray-600 mb-1">Status</label>
            <select id="f_status" name="status" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
              <option value="">All</option>
              <option value="pending"     <?php echo e(request('status')==='pending'?'selected':''); ?>>Pending</option>
              <option value="in_progress" <?php echo e(request('status')==='in_progress'?'selected':''); ?>>In progress</option>
              <option value="completed"   <?php echo e(request('status')==='completed'?'selected':''); ?>>Completed</option>
            </select>
          </div>
          <div>
            <label for="f_from" class="block text-xs text-gray-600 mb-1">From date</label>
            <input id="f_from" type="date" name="from" value="<?php echo e(e(request('from',''))); ?>" class="w-full bg-white border border-gray-300 rounded p-2 text-sm" />
          </div>
          <div>
            <label for="f_to" class="block text-xs text-gray-600 mb-1">To date</label>
            <input id="f_to" type="date" name="to" value="<?php echo e(e(request('to',''))); ?>" class="w-full bg-white border border-gray-300 rounded p-2 text-sm" />
          </div>
          <div class="md:col-span-2 flex items-end gap-2">
            <button class="px-3 py-2 rounded bg-sky-600 hover:bg-sky-500 text-white text-sm">Apply</button>
            <a href="<?php echo e(route('repair.dashboard')); ?>" class="px-3 py-2 rounded bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-sm">Reset</a>
          </div>
        </div>
      </form>

      
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <div class="kpi">
          <div class="kpi-title">Total</div>
          <div class="kpi-value"><?php echo e(number_format($stats['total'])); ?></div>
        </div>
        <div class="kpi">
          <div class="kpi-title text-yellow-700">Pending</div>
          <div class="kpi-value text-yellow-700"><?php echo e(number_format($stats['pending'])); ?></div>
        </div>
        <div class="kpi">
          <div class="kpi-title text-sky-700">In progress</div>
          <div class="kpi-value text-sky-700"><?php echo e(number_format($stats['inProgress'])); ?></div>
        </div>
        <div class="kpi">
          <div class="kpi-title text-emerald-700">Completed</div>
          <div class="kpi-value text-emerald-700"><?php echo e(number_format($stats['completed'])); ?></div>
        </div>
        <div class="kpi">
          <div class="kpi-title">Monthly cost</div>
          <div class="kpi-value"><?php echo e(number_format($stats['monthCost'], 2)); ?></div>
        </div>
      </div>

      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="section-card">
          <div class="section-head">Monthly trend 6 months</div>
          <div class="section-body">
            <?php if(count($trendLabels) && count($trendCounts)): ?>
              <div class="chart-card">
                <canvas id="trendChart"
                        data-labels='<?php echo json_encode($trendLabels, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>'
                        data-values='<?php echo json_encode($trendCounts, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>'></canvas>
              </div>
            <?php else: ?>
              <div class="empty-state">No data</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="section-card">
          <div class="section-head">Asset types Top 8 plus others</div>
          <div class="section-body">
            <?php if(count($typeLabels) && count($typeCounts)): ?>
              <div class="chart-card">
                <canvas id="typePie"
                        data-labels='<?php echo json_encode($typeLabels, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>'
                        data-values='<?php echo json_encode($typeCounts, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>'></canvas>
              </div>
            <?php else: ?>
              <div class="empty-state">No data</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="section-card">
        <div class="section-head">By department Top 8</div>
        <div class="section-body">
          <?php if(count($deptLabels) && count($deptCounts)): ?>
            <div class="chart-card">
              <canvas id="deptBar"
                      data-labels='<?php echo json_encode($deptLabels, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>'
                      data-values='<?php echo json_encode($deptCounts, JSON_INVALID_UTF8_SUBSTITUTE, 512) ?>'></canvas>
            </div>
          <?php else: ?>
            <div class="empty-state">No data</div>
          <?php endif; ?>
        </div>
      </div>

      
      <div class="section-card overflow-hidden">
        <div class="section-head flex items-center">
          <span>Recent jobs</span>
          <span class="ml-2 text-xs text-gray-500">up to 12 items</span>
        </div>

        <div class="section-body p-0">
          <div class="overflow-x-auto">
            <table class="tbl min-w-full divide-y divide-gray-200" role="table" aria-label="Recent jobs">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left uppercase">Reported at</th>
                  <th class="text-left uppercase">Asset</th>
                  <th class="text-left uppercase">Reporter</th>
                  <th class="text-left uppercase">Status</th>
                  <th class="text-left uppercase">Assignee</th>
                  <th class="text-left uppercase">Completed at</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <?php
                    $status = (string) $get($t,'status','');
                    $badgeClass =
                      $status === \App\Models\MaintenanceRequest::STATUS_PENDING     ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_IN_PROGRESS ? 'bg-sky-50 text-sky-700 border border-sky-200' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_COMPLETED   ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                                                                         'bg-gray-50 text-gray-700 border border-gray-200'));
                    $assetId   = $get($t,'asset_id','-');
                    $assetName = $get($t,'asset_name') ?: $get($t,'asset.name','-');
                    $reporter  = $get($t,'reporter')   ?: $get($t,'reporter.name','-');
                    $tech      = $get($t,'technician')  ?: $get($t,'technician.name','-');
                    $reqAt     = $get($t,'request_date','-');
                    $doneAt    = $get($t,'completed_at') ?: $get($t,'completed_date','-');
                  ?>
                  <tr class="hover:bg-gray-50">
                    <td><?php echo e(is_string($reqAt) ? $reqAt : optional($reqAt)->format('Y-m-d H:i')); ?></td>
                    <td>#<?php echo e(e((string)$assetId)); ?> — <?php echo e(e((string)$assetName)); ?></td>
                    <td><?php echo e(e((string)$reporter)); ?></td>
                    <td>
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] <?php echo e($badgeClass); ?>">
                        <?php echo e(ucfirst(str_replace('_',' ', $status))); ?>

                      </span>
                    </td>
                    <td><?php echo e(e((string)$tech)); ?></td>
                    <td><?php echo e(is_string($doneAt) ? $doneAt : (optional($doneAt)->format('Y-m-d H:i') ?? '-')); ?></td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="6" class="text-center text-gray-500 py-10">
                      No recent data to display
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  
  <script>
    function loadChartJsOnce(cb){
      if (window.Chart) return cb();
      const s=document.createElement('script');
      s.src="https://cdn.jsdelivr.net/npm/chart.js";
      s.async = true;
      s.onload=()=> typeof cb==='function' && cb();
      s.onerror=()=> console.warn('[ChartJS] failed to load');
      document.head.appendChild(s);
    }
    function makeChart(el, type){
      try{
        const labels = JSON.parse(el.dataset.labels || '[]');
        const values = JSON.parse(el.dataset.values || '[]');
        if (!labels.length || !values.length) return;

        const axisStyle = {
          ticks: { color: '#374151' },                  // text-gray-700
          grid:  { color: '#e5e7eb' }                   // border-gray-200
        };

        const cfg = (type === 'pie') ? {
          type:'pie',
          data:{ labels, datasets:[{ data: values }] },
          options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{
              legend:{ position:'bottom', labels:{ boxWidth:10, color:'#111827' } } // text-gray-900
            }
          }
        } : (type === 'bar') ? {
          type:'bar',
          data:{ labels, datasets:[{ data: values }] },
          options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ y: axisStyle, x: { ...axisStyle, grid:{ display:false } } }
          }
        } : {
          type:'line',
          data:{ labels, datasets:[{ data: values, tension:.35, pointRadius:2, borderWidth:2 }] },
          options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ y: axisStyle, x: axisStyle }
          }
        };
        new Chart(el, cfg);
      }catch(e){ console.warn('[ChartJS] render error', e); }
    }

    const obs = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          const c = e.target;
          loadChartJsOnce(()=> makeChart(c,
            c.id==='typePie' ? 'pie' : (c.id==='deptBar' ? 'bar' : 'line')));
          obs.unobserve(c);
        }
      });
    },{ root:null, threshold:0.12 });

    ['trendChart','typePie','deptBar'].forEach(id=>{
      const el = document.getElementById(id);
      if(el) obs.observe(el);
    });
  </script>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('footer'); ?>
  <div class="text-xs text-gray-500">
    © <?php echo e(date('Y')); ?> <?php echo e(config('app.name','Asset Repair')); ?> — Dashboard
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/repair/dashboard.blade.php ENDPATH**/ ?>