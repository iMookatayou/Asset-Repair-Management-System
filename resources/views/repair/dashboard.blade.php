@extends('layouts.app')

@section('title', 'Asset Repair Dashboard — Compact')

@section('content')

  @php
    $toast = session('toast');
    if ($toast) { session()->forget('toast'); } // ใช้ครั้งเดียว

    // base fields
    $type     = $toast['type']     ?? null;   // success|info|warning|error
    $message  = $toast['message']  ?? null;
    $position = $toast['position'] ?? 'center';  // tr|tl|br|bl|center|tc|bc
    $timeout  = (int)($toast['timeout'] ?? 3200);
    $size     = $toast['size']     ?? 'lg';      // sm|md|lg

    // map error/status → toast อัตโนมัติ
    $firstError = ($errors ?? null)?->first();
    if (!$message && $firstError) { $message = $firstError; $type = $type ?: 'error'; }
    if (!$message && session('error')) { $message = session('error'); $type = $type ?: 'error'; }
    if (!$message && session('status')) { $message = session('status'); $type = $type ?: 'success'; }
  @endphp

  @php
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

    // เปิดแผง Filters อัตโนมัติถ้ามีพารามิเตอร์กรอง
    $filtersActive = (string)request('status','') !== '' || (string)request('from','') !== '' || (string)request('to','') !== '';

    // สี pill สำหรับสถานะ (ยูทิลิตี้ล้วน ๆ ไม่ใช้ .badge)
    $statusPillClass = function (string $status): string {
      return match ($status) {
        \App\Models\MaintenanceRequest::STATUS_PENDING     => 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-200',
        \App\Models\MaintenanceRequest::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-800 ring-1 ring-inset ring-sky-200',
        \App\Models\MaintenanceRequest::STATUS_COMPLETED   => 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-200',
        default                                            => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200',
      };
    };
  @endphp

  <div class="py-4">
    <div class="mx-auto max-w-7xl px-3 lg:px-6 space-y-4">

      <div class="sticky top-16 z-30 -mt-2">
        <div class="rounded-2xl border border-zinc-200 bg-white/90 backdrop-blur shadow-sm">
          <div class="flex flex-wrap items-center gap-3 px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="h-9 w-9 rounded-xl bg-zinc-100 grid place-items-center text-xs text-zinc-500">AR</div>
              <div>
                <h1 class="text-base font-semibold text-zinc-900 leading-tight">Asset Repair — Dashboard</h1>
                <p class="text-xs text-zinc-500">สรุปสถานะงานซ่อม • อัปเดตเรียลไทม์</p>
              </div>
            </div>

            <div class="ms-auto flex items-center gap-2">
              @if ($filtersActive)
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200">
                  Filters ON
                </span>
              @endif

              <button id="filterToggle" type="button"
                      class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50"
                      aria-expanded="{{ $filtersActive ? 'true' : 'false' }}"
                      aria-controls="filtersPanel">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4h18M6 8h12M9 12h6M11 16h2"/>
                </svg>
                กรอง
              </button>
            </div>
          </div>
          {{-- Quick row removed in favor of dropdown --}}
        </div>
      </div>

      <div id="filtersPanel" class="{{ $filtersActive ? '' : 'hidden' }}">
        <form method="GET" class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
          <div class="px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-medium text-zinc-900">Filters</h2>
              <div class="text-xs text-zinc-500">ปรับเงื่อนไขเพื่อจำกัดผลลัพธ์</div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-6">
              <div class="md:col-span-2">
                <label for="f_status" class="block text-xs font-medium text-zinc-700">Status</label>
                <select id="f_status" name="status"
                        class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                  <option value="">ทั้งหมด</option>
                  <option value="pending"     {{ request('status')==='pending'?'selected':'' }}>รอดำเนินการ</option>
                  <option value="in_progress" {{ request('status')==='in_progress'?'selected':'' }}>กำลังดำเนินการ</option>
                  <option value="completed"   {{ request('status')==='completed'?'selected':'' }}>เสร็จสิ้น</option>
                </select>
              </div>

              <div>
                <label for="f_from" class="block text-xs font-medium text-zinc-700">From</label>
                <input id="f_from" type="date" name="from" value="{{ e(request('from','')) }}"
                       class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
              </div>

              <div>
                <label for="f_to" class="block text-xs font-medium text-zinc-700">To</label>
                <input id="f_to" type="date" name="to" value="{{ e(request('to','')) }}"
                       class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
              </div>

              <div class="md:col-span-2 flex items-end gap-2">
                <button class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/30">
                  ค้นหา
                </button>
                <a href="{{ route('repair.dashboard') }}"
                   class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">
                  คืนค่า
                </a>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
          <div class="text-sm text-zinc-500">Total</div>
          <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ number_format($stats['total'] ?? 0) }}</div>
          <div class="text-xs text-zinc-400">ทั้งหมด</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
          <div class="text-sm text-amber-600">Pending</div>
          <div class="mt-1 text-2xl font-semibold text-amber-700">{{ number_format($stats['pending'] ?? 0) }}</div>
          <div class="text-xs text-amber-500">รอรับงาน</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
          <div class="text-sm text-sky-600">In progress</div>
          <div class="mt-1 text-2xl font-semibold text-sky-700">{{ number_format($stats['inProgress'] ?? 0) }}</div>
          <div class="text-xs text-sky-500">กำลังดำเนินการ</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
          <div class="text-sm text-emerald-600">Completed</div>
          <div class="mt-1 text-2xl font-semibold text-emerald-700">{{ number_format($stats['completed'] ?? 0) }}</div>
          <div class="text-xs text-emerald-500">เสร็จสิ้น</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
          <div class="text-sm text-zinc-600">Monthly cost</div>
          <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ number_format($stats['monthCost'] ?? 0, 2) }}</div>
          <div class="text-xs text-zinc-400">ค่าใช้จ่ายเดือนนี้</div>
        </div>
      </div>

      <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="px-4 py-3 sm:px-6 sm:py-4">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-medium text-zinc-900">By Department (Top 8)</h2>
            <div class="text-xs text-zinc-500">ภาพรวมแผนกที่มีงานสูงสุด</div>
          </div>

          @if (count($deptLabels) && count($deptCounts))
            <div class="h-64">
              <canvas id="deptBar"
                      data-labels='@json($deptLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                      data-values='@json($deptCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
            </div>
          @else
            <div class="grid h-48 place-items-center text-zinc-400">No data</div>
          @endif
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
          <div class="px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-medium text-zinc-900">Monthly Trend (6 months)</h2>
              <div class="text-xs text-zinc-500">แนวโน้มจำนวนงานตามเดือน</div>
            </div>

            @if (count($trendLabels) && count($trendCounts))
              <div class="h-64">
                <canvas id="trendChart"
                        data-labels='@json($trendLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                        data-values='@json($trendCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
              </div>
            @else
              <div class="grid h-48 place-items-center text-zinc-400">No data</div>
            @endif
          </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
          <div class="px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-medium text-zinc-900">Asset Types (Top 8 + others)</h2>
              <div class="text-xs text-zinc-500">สัดส่วนหมวดหมู่ครุภัณฑ์</div>
            </div>

            @if (count($typeLabels) && count($typeCounts))
              <div class="h-64">
                <canvas id="typePie"
                        data-labels='@json($typeLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                        data-values='@json($typeCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
              </div>
            @else
              <div class="grid h-48 place-items-center text-zinc-400">No data</div>
            @endif
          </div>
        </div>
      </div>


      <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="px-4 py-3 sm:px-6 sm:py-4">
          <div class="flex items-center gap-2">
            <h2 class="text-base font-medium text-zinc-900">Recent Jobs</h2>
            <span class="text-xs text-zinc-500">up to 12 items</span>
          </div>
        </div>
        <div class="overflow-x-auto px-4 pb-4">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-xs uppercase text-zinc-500">
                <th class="py-2 text-left">Reported at</th>
                <th class="py-2 text-left">Asset</th>
                <th class="py-2 text-left">Reporter</th>
                <th class="py-2 text-left">Status</th>
                <th class="py-2 text-left">Assignee</th>
                <th class="py-2 text-left">Completed at</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
              @forelse($recent as $t)
                @php
                  $status = (string) $get($t,'status','');
                  $pill   = $statusPillClass($status);
                  $assetId   = $get($t,'asset_id','-');
                  $assetName = $get($t,'asset_name') ?: $get($t,'asset.name','-');
                  $reporter  = $get($t,'reporter')   ?: $get($t,'reporter.name','-');
                  $tech      = $get($t,'technician')  ?: $get($t,'technician.name','-');
                  $reqAt     = $get($t,'request_date','-');
                  $doneAt    = $get($t,'completed_at') ?: $get($t,'completed_date','-');
                @endphp
                <tr class="hover:bg-zinc-50">
                  <td class="py-2 pr-3 text-zinc-800">{{ is_string($reqAt) ? e($reqAt) : optional($reqAt)->format('Y-m-d H:i') }}</td>
                  <td class="py-2 pr-3 text-zinc-800">#{{ e((string)$assetId) }} — {{ e((string)$assetName) }}</td>
                  <td class="py-2 pr-3 text-zinc-800">{{ e((string)$reporter) }}</td>
                  <td class="py-2 pr-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pill }}">
                      {{ ucfirst(str_replace('_',' ', $status)) }}
                    </span>
                  </td>
                  <td class="py-2 pr-3 text-zinc-800">{{ e((string)$tech) }}</td>
                  <td class="py-2 pr-3 text-zinc-800">{{ is_string($doneAt) ? e($doneAt) : (optional($doneAt)->format('Y-m-d H:i') ?? '-') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="py-10 text-center text-zinc-400">No recent data to display</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <style>
    .toast-overlay{position:fixed;inset:0;z-index:100001;pointer-events:none}
    .toast-pos{display:flex;width:100%;height:100%;padding:1rem}
    .toast-pos.tr{align-items:flex-start;justify-content:flex-end}
    .toast-pos.tl{align-items:flex-start;justify-content:flex-start}
    .toast-pos.br{align-items:flex-end;justify-content:flex-end}
    .toast-pos.bl{align-items:flex-end;justify-content:flex-start}
    .toast-pos.center{align-items:center;justify-content:center}
    .toast-pos.tc{align-items:flex-start;justify-content:center;padding-top:calc(var(--topbar-h,0px) + .75rem)}
    .toast-pos.bc{align-items:flex-end;justify-content:center;padding-bottom:.75rem}
    .toast-card{
      --toast-max-w:min(92vw,680px);--toast-min-w:440px;--toast-pad-x:24px;--toast-pad-y:18px;
      --toast-fs:16px;--toast-icon:36px;--toast-radius:16px;--toast-bar-h:4px;
      pointer-events:auto;width:max-content;max-width:var(--toast-max-w);min-width:var(--toast-min-w);
      background:#fff;border-radius:var(--toast-radius);border:1px solid #e5eef7;box-shadow:0 14px 48px rgba(15,23,42,.14);
      opacity:0;transform:translateY(-6px);transition:opacity .22s ease, transform .22s ease;
      display:flex;align-items:center;gap:.9rem;padding:var(--toast-pad-y) var(--toast-pad-x);position:relative;overflow:hidden;
    }
    .toast-card.show{opacity:1;transform:translateY(0)}
    .toast--sm{--toast-max-w:min(92vw,420px);--toast-min-w:320px;--toast-pad-x:16px;--toast-pad-y:10px;--toast-fs:14px;--toast-icon:28px;--toast-radius:12px;--toast-bar-h:3px}
    .toast--md{--toast-max-w:min(92vw,520px);--toast-min-w:380px;--toast-pad-x:18px;--toast-pad-y:14px;--toast-fs:15px;--toast-icon:32px;--toast-radius:12px;--toast-bar-h:4px}
    .toast-icon{flex:0 0 var(--toast-icon);display:flex;align-items:center;justify-content:center}
    .toast-msg{font-size:var(--toast-fs);color:#0f172a;line-height:1.5;white-space:normal;word-break:break-word;flex:1}
    .toast-close{border:0;background:transparent;font-size:calc(var(--toast-fs) + 1px);color:#64748b;cursor:pointer;line-height:1}
    .toast-close:hover{color:#0f172a}
    .toast-bar{position:absolute;bottom:0;left:0;height:var(--toast-bar-h);width:100%;background:#f1f5f9}
    .toast-fill{height:var(--toast-bar-h);width:0;transition:width linear}
    .fill-success{background:#10b981}.fill-info{background:#3b82f6}.fill-warning{background:#f59e0b}.fill-error{background:#ef4444}
    @media (max-width:480px){.toast-card{min-width:calc(100vw - 2rem)}}
  </style>
  <div class="toast-overlay" aria-live="polite" aria-atomic="true"></div>

  @php
    // ค่า default ของ Lottie ถ้า Controller ไม่ได้ส่งมา
    $lottieMap = $lottieMap ?? [
      'success' => asset('lottie/lock_with_green_tick.json'),
      'info'    => asset('lottie/lock_with_blue_info.json'),
      'warning' => asset('lottie/lock_with_yellow_alert.json'),
      'error'   => asset('lottie/lock_with_red_tick.json'),
    ];
  @endphp

  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" defer></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>

  <script>
  (function(){
    // ===== Filters toggle =====
    document.addEventListener('DOMContentLoaded', () => {
      // Filters panel
      const btn = document.getElementById('filterToggle');
      const panel = document.getElementById('filtersPanel');
      if (btn && panel) {
        btn.addEventListener('click', () => {
          const hidden = panel.classList.toggle('hidden');
          btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        });
      }
    });

    function waitFor(condFn, {tries=50, interval=60} = {}) {
      return new Promise((resolve, reject) => {
        const t = setInterval(() => {
          if (condFn()) { clearInterval(t); resolve(true); }
          else if (--tries <= 0) { clearInterval(t); reject(new Error('waitFor timeout')); }
        }, interval);
      });
    }

    function parseData(el){
      try {
        const labels = JSON.parse(el.dataset.labels || '[]');
        const values = JSON.parse(el.dataset.values || '[]');
        return { labels, values };
      } catch(e) {
        return { labels: [], values: [] };
      }
    }
    const palette = [
      '#2563eb','#10b981','#f59e0b','#ef4444','#0ea5e9','#8b5cf6','#14b8a6','#f97316',
      '#22c55e','#a855f7','#e11d48','#06b6d4'
    ];
    const CHART_INSTANCES = {};

    function makeBarChart(el){
      const { labels, values } = parseData(el);
      if (!labels.length || !values.length) return;
      const id = el.id || 'deptBar';
      if (CHART_INSTANCES[id]) CHART_INSTANCES[id].destroy();

      CHART_INSTANCES[id] = new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Jobs',
            data: values,
            backgroundColor: labels.map((_,i)=> palette[i % palette.length]),
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: { grid: { display:false }, ticks: { color:'#475569' } },
            y: { grid: { color:'rgba(0,0,0,.05)' }, ticks: { color:'#475569' }, beginAtZero:true, precision:0 }
          },
          plugins: {
            legend: { display:false },
            tooltip: { mode:'index', intersect:false }
          }
        }
      });
    }

    function makeLineChart(el){
      const { labels, values } = parseData(el);
      if (!labels.length || !values.length) return;
      const id = el.id || 'trendChart';
      if (CHART_INSTANCES[id]) CHART_INSTANCES[id].destroy();

      CHART_INSTANCES[id] = new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Jobs',
            data: values,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,.12)',
            tension: .3,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 4
          }]
        },
        options: {
          responsive:true,
          maintainAspectRatio:false,
          scales:{
            x:{ grid:{ display:false }, ticks:{ color:'#475569' } },
            y:{ grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ color:'#475569' }, beginAtZero:true, precision:0 }
          },
          plugins:{
            legend:{ display:false },
            tooltip:{ mode:'index', intersect:false }
          }
        }
      });
    }

    function makePieChart(el){
      const { labels, values } = parseData(el);
      if (!labels.length || !values.length) return;
      const id = el.id || 'typePie';
      if (CHART_INSTANCES[id]) CHART_INSTANCES[id].destroy();

      CHART_INSTANCES[id] = new Chart(el.getContext('2d'), {
        type: 'pie',
        data: {
          labels,
          datasets: [{
            data: values,
            backgroundColor: labels.map((_,i)=> palette[i % palette.length]),
            borderWidth: 0
          }]
        },
        options: {
          responsive:true,
          maintainAspectRatio:false,
          plugins:{
            legend:{ position:'bottom', labels:{ color:'#334155' } }
          }
        }
      });
    }

    async function renderCharts(){
      try { await waitFor(()=> window.Chart?.registry); } catch(_) { return; }
      const deptBar    = document.getElementById('deptBar');
      const trendChart = document.getElementById('trendChart');
      const typePie    = document.getElementById('typePie');

      if (deptBar)    makeBarChart(deptBar);
      if (trendChart) makeLineChart(trendChart);
      if (typePie)    makePieChart(typePie);
    }

    const LOTTIE = {
      success: @json($lottieMap['success'] ?? null),
      info:    @json($lottieMap['info']    ?? null),
      warning: @json($lottieMap['warning'] ?? null),
      error:   @json($lottieMap['error']   ?? null),
    };
    const SVG = {
      success: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>',
      info:    '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
      warning: '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
      error:   '<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>',
    };
    function lottieReady(){
      if (window.customElements && window.customElements.whenDefined) {
        return window.customElements.whenDefined('lottie-player').catch(()=>{});
      }
      return Promise.resolve();
    }
    function makeIconEl(type){
      const wrap = document.createElement('div');
      wrap.className = 'toast-icon';
      const src = LOTTIE[type];
      const canUseLottie = !!src && window.customElements && !!window.customElements.get('lottie-player');
      if (canUseLottie) {
        wrap.innerHTML = `<lottie-player src="${src}" style="width:var(--toast-icon);height:var(--toast-icon)" background="transparent" speed="1" autoplay></lottie-player>`;
        setTimeout(() => {
          const lp = wrap.querySelector('lottie-player');
          if (!lp || !lp.clientWidth) {
            wrap.innerHTML = `<div style="width:var(--toast-icon);height:var(--toast-icon);display:grid;place-items:center;">${SVG[type] ?? ''}</div>`;
          }
        }, 800);
      } else {
        wrap.innerHTML = `<div style="width:var(--toast-icon);height:var(--toast-icon);display:grid;place-items:center;">${SVG[type] ?? ''}</div>`;
      }
      return wrap;
    }
    const FORCE_POSITION = null;
    function ensurePos(position){
      const overlay = document.querySelector('.toast-overlay');
      overlay.innerHTML = '';
      const posEl = document.createElement('div');
      posEl.className = 'toast-pos ' + position;
      overlay.appendChild(posEl);
      return { overlay, posEl };
    }
    function showToast({type='info', message='', position='tc', timeout=3200, size='lg'} = {}){
      position = FORCE_POSITION || position || 'tc';
      const allowed = ['tr','tl','br','bl','center','tc','bc'];
      if (!allowed.includes(position)) position = 'tc';
      timeout = Number(timeout) || 3200;

      const { posEl } = ensurePos(position);
      const card = document.createElement('section');
      const sizeClass = (['sm','md','lg'].includes(size) ? `toast--${size}` : 'toast--lg');
      card.className = `toast-card ${sizeClass} toast-${type}`;
      card.setAttribute('role','status');

      const icon = makeIconEl(type);
      const msg = document.createElement('div');
      msg.className = 'toast-msg';
      msg.textContent = message ?? '';

      const btn = document.createElement('button');
      btn.className = 'toast-close';
      btn.setAttribute('aria-label','Close');
      btn.innerHTML = '&times;';

      const bar = document.createElement('div');
      bar.className = 'toast-bar';
      const fill = document.createElement('div');
      fill.className = `toast-fill fill-${type}`;
      bar.appendChild(fill);

      card.append(icon, msg, btn, bar);
      posEl.appendChild(card);

      requestAnimationFrame(() => {
        card.classList.add('show');
        requestAnimationFrame(() => {
          fill.style.transition = `width ${timeout}ms linear`;
          fill.style.width = '100%';
        });
      });

      let timer = setTimeout(close, timeout + 60);
      function close(){ card.classList.remove('show'); setTimeout(()=> card.remove(), 200); }
      btn.addEventListener('click', close);

      card.addEventListener('mouseenter', () => {
        fill.style.transition = 'none';
        const w = getComputedStyle(fill).width;
        fill.style.width = w;
        clearTimeout(timer);
      });
      card.addEventListener('mouseleave', () => {
        const done = parseFloat(getComputedStyle(fill).width) / card.clientWidth;
        const remainMs = Math.max(0, 1 - done) * timeout;
        requestAnimationFrame(() => {
          fill.style.transition = `width ${remainMs}ms linear`;
          fill.style.width = '100%';
        });
        timer = setTimeout(close, remainMs + 50);
      });

      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); }, { once:true });
    }
    window.showToast = showToast;
    window.addEventListener('app:toast', e => showToast(e.detail || {}));

    document.addEventListener('DOMContentLoaded', async () => {
      // Charts
      try { await renderCharts(); } catch(e) { /* noop */ }

      // Toast จาก session
      @if (($type ?? null) && ($message ?? null))
        await lottieReady().catch(()=>{});
        window.showToast({
          type: @json($type),
          message: @json($message),
          position: @json($position),
          timeout: @json($timeout),
          size: @json($size ?? 'lg')
        });
      @endif
    });
  })();
  </script>
@endsection

@section('footer')
  <div class="text-xs text-zinc-500">
    © {{ date('Y') }} {{ config('app.name','Asset Repair') }} — Dashboard
  </div>
@endsection
