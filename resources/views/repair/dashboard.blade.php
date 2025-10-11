{{-- resources/views/repair/dashboard.blade.php --}}
<x-app-layout>
  {{-- ===== Topbar header ===== --}}
  <x-slot name="header">
    <div class="flex items-center gap-3 w-full">
      <h2 class="font-semibold text-lg sm:text-xl text-gray-100 leading-tight">
        {{ __('Asset Repair Dashboard Compact') }}
      </h2>

      {{-- Quick stats mini badges --}}
      @php
        $stats = array_replace(['total'=>0,'pending'=>0,'inProgress'=>0,'completed'=>0,'monthCost'=>0], $stats ?? []);
      @endphp
      <div class="ml-auto hidden md:flex items-center gap-2 text-xs">
        <span class="px-2 py-0.5 rounded bg-zinc-800/70 text-zinc-300">Total: {{ number_format($stats['total']) }}</span>
        <span class="px-2 py-0.5 rounded bg-yellow-900/30 text-yellow-200">Pending: {{ number_format($stats['pending']) }}</span>
        <span class="px-2 py-0.5 rounded bg-sky-900/30 text-sky-200">In progress: {{ number_format($stats['inProgress']) }}</span>
        <span class="px-2 py-0.5 rounded bg-emerald-900/30 text-emerald-200">Completed: {{ number_format($stats['completed']) }}</span>
      </div>
    </div>
  </x-slot>

  {{-- ===== Page-only styles (เบา ๆ) ===== --}}
  <style>
    .chart-card { position: relative; height: 220px; }
    @media (min-width: 1024px) { .chart-card { height: 260px; } }
    .section-card { border-radius: .875rem; }
    .section-head { padding:.6rem .9rem; font-weight:600; }
    .section-body { padding:.9rem; }
    .kpi { border-radius:.875rem; padding:.8rem; }
    .kpi-title { font-size:.7rem; color:#9ca3af; }
    .kpi-value { font-size:1.35rem; font-weight:700; line-height:1.1; }
    .tbl th, .tbl td { padding:.5rem .6rem; font-size:.82rem; }
    .tbl thead th { font-size:.68rem; letter-spacing:.02em; }
    .empty-state { display:flex; align-items:center; justify-content:center; height:220px; color:#9ca3af; font-size:.9rem }
    @media (min-width:1024px){ .empty-state{ height:260px; } }
  </style>

  @php
    // ===== Normalize incoming collections =====
    $monthlyTrend = is_iterable($monthlyTrend ?? null) ? collect($monthlyTrend) : collect();
    $byAssetType  = is_iterable($byAssetType  ?? null) ? collect($byAssetType)  : collect();
    $byDept       = is_iterable($byDept       ?? null) ? collect($byDept)       : collect();
    $recent       = is_iterable($recent       ?? null) ? collect($recent)       : collect();

    $monthlyTrend = $monthlyTrend->take(6)->values();
    $byAssetType  = $byAssetType->take(9)->values();
    $byDept       = $byDept->take(8)->values();

    // Clean helpers
    $intVal   = fn($v)=> is_numeric($v) ? (int)$v : 0;
    $strVal   = fn($v,$f='')=> is_string($v) && $v!=='' ? $v : $f;

    $trendLabels = $monthlyTrend->map(fn($i)=> $strVal(is_array($i)?($i['ym']??''):($i->ym??'')))->all();
    $trendCounts = $monthlyTrend->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $typeLabels  = $byAssetType->map(fn($i)=> $strVal(is_array($i)?($i['type']??'Unspecified'):($i->type??'Unspecified'),'Unspecified'))->all();
    $typeCounts  = $byAssetType->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    $deptLabels  = $byDept->map(fn($i)=> $strVal(is_array($i)?($i['dept']??'Unspecified'):($i->dept??'Unspecified'),'Unspecified'))->all();
    $deptCounts  = $byDept->map(fn($i)=> $intVal(is_array($i)?($i['cnt']??0):($i->cnt??0)) )->all();

    // safe getter for array or object
    $get = function($row, $key, $fallback='-'){
      if (is_array($row))  return data_get($row, $key, $fallback);
      if (is_object($row)) return data_get((array)$row, $key, $fallback);
      return $fallback;
    };
  @endphp

  <div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-4">

      {{-- ===== Filters Row ===== --}}
      <form method="GET" class="section-card border border-zinc-800 bg-[#0f1a2a]" aria-label="Filters">
        <div class="section-head border-b border-zinc-700 text-zinc-100">Filters</div>
        <div class="section-body grid grid-cols-2 md:grid-cols-5 gap-3">
          <div>
            <label for="f_status" class="block text-xs text-zinc-400 mb-1">Status</label>
            <select id="f_status" name="status" class="w-full bg-zinc-900 border border-zinc-700 rounded p-2 text-sm">
              <option value="">All</option>
              <option value="pending"     {{ request('status')==='pending'?'selected':'' }}>Pending</option>
              <option value="in_progress" {{ request('status')==='in_progress'?'selected':'' }}>In progress</option>
              <option value="completed"   {{ request('status')==='completed'?'selected':'' }}>Completed</option>
            </select>
          </div>
          <div>
            <label for="f_from" class="block text-xs text-zinc-400 mb-1">From date</label>
            <input id="f_from" type="date" name="from" value="{{ e(request('from','')) }}" class="w-full bg-zinc-900 border border-zinc-700 rounded p-2 text-sm" />
          </div>
          <div>
            <label for="f_to" class="block text-xs text-zinc-400 mb-1">To date</label>
            <input id="f_to" type="date" name="to" value="{{ e(request('to','')) }}" class="w-full bg-zinc-900 border border-zinc-700 rounded p-2 text-sm" />
          </div>
          <div class="md:col-span-2 flex items-end gap-2">
            <button class="px-3 py-2 rounded bg-sky-600 hover:bg-sky-500 text-white text-sm">Apply</button>
            <a href="{{ route('repair.dashboard') }}" class="px-3 py-2 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-sm">Reset</a>
          </div>
        </div>
      </form>

      {{-- ===== KPIs ===== --}}
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-zinc-100">
          <div class="kpi-title">Total</div>
          <div class="kpi-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-yellow-200">
          <div class="kpi-title">Pending</div>
          <div class="kpi-value">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-sky-200">
          <div class="kpi-title">In progress</div>
          <div class="kpi-value">{{ number_format($stats['inProgress']) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-emerald-200">
          <div class="kpi-title">Completed</div>
          <div class="kpi-value">{{ number_format($stats['completed']) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-zinc-100">
          <div class="kpi-title">Monthly cost</div>
          <div class="kpi-value">{{ number_format($stats['monthCost'], 2) }}</div>
        </div>
      </div>

      {{-- ===== CHARTS ===== --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
          <div class="section-head border-b border-zinc-700 text-zinc-100">Monthly trend 6 months</div>
          <div class="section-body">
            @if (count($trendLabels) && count($trendCounts))
              <div class="chart-card">
                <canvas id="trendChart"
                        data-labels='@json($trendLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                        data-values='@json($trendCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
              </div>
            @else
              <div class="empty-state">No data</div>
            @endif
          </div>
        </div>

        <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
          <div class="section-head border-b border-zinc-700 text-zinc-100">Asset types Top 8 plus others</div>
          <div class="section-body">
            @if (count($typeLabels) && count($typeCounts))
              <div class="chart-card">
                <canvas id="typePie"
                        data-labels='@json($typeLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                        data-values='@json($typeCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
              </div>
            @else
              <div class="empty-state">No data</div>
            @endif
          </div>
        </div>
      </div>

      <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
        <div class="section-head border-b border-zinc-700 text-zinc-100">By department Top 8</div>
        <div class="section-body">
          @if (count($deptLabels) && count($deptCounts))
            <div class="chart-card">
              <canvas id="deptBar"
                      data-labels='@json($deptLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                      data-values='@json($deptCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
            </div>
          @else
            <div class="empty-state">No data</div>
          @endif
        </div>
      </div>

      {{-- ===== RECENT TABLE ===== --}}
      <div class="section-card border border-zinc-800 bg-[#0f1a2a] overflow-hidden">
        <div class="section-head border-b border-zinc-700 text-zinc-100 flex items-center">
          <span>Recent jobs</span>
          <span class="ml-2 text-xs text-zinc-400">up to 12 items</span>
        </div>

        <div class="section-body p-0">
          <div class="overflow-x-auto">
            <table class="tbl min-w-full divide-y divide-zinc-800 text-zinc-100" role="table" aria-label="Recent jobs">
              <thead class="bg-[#0b1422]">
                <tr>
                  <th class="text-left uppercase text-zinc-400">Reported at</th>
                  <th class="text-left uppercase text-zinc-400">Asset</th>
                  <th class="text-left uppercase text-zinc-400">Reporter</th>
                  <th class="text-left uppercase text-zinc-400">Status</th>
                  <th class="text-left uppercase text-zinc-400">Assignee</th>
                  <th class="text-left uppercase text-zinc-400">Completed at</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-zinc-800">
                @forelse($recent as $t)
                  @php
                    $status = (string) $get($t,'status','');
                    $badgeClass =
                      $status === \App\Models\MaintenanceRequest::STATUS_PENDING     ? 'bg-yellow-200/20 text-yellow-300' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_IN_PROGRESS ? 'bg-sky-200/20 text-sky-300' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_COMPLETED   ? 'bg-emerald-200/20 text-emerald-300' :
                                                                                         'bg-zinc-200/20 text-zinc-300'));
                    $assetId   = $get($t,'asset_id','-');
                    $assetName = $get($t,'asset_name') ?: $get($t,'asset.name','-');
                    $reporter  = $get($t,'reporter')   ?: $get($t,'reporter.name','-');
                    $tech      = $get($t,'technician')  ?: $get($t,'technician.name','-');
                    $reqAt     = $get($t,'request_date','-');
                    $doneAt    = $get($t,'completed_at') ?: $get($t,'completed_date','-');
                  @endphp
                  <tr class="hover:bg-[#0b1422]">
                    <td>{{ is_string($reqAt) ? $reqAt : optional($reqAt)->format('Y-m-d H:i') }}</td>
                    <td>#{{ e((string)$assetId) }} — {{ e((string)$assetName) }}</td>
                    <td>{{ e((string)$reporter) }}</td>
                    <td><span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] {{ $badgeClass }}">
                      {{ ucfirst(str_replace('_',' ', $status)) }}</span>
                    </td>
                    <td>{{ e((string)$tech) }}</td>
                    <td>{{ is_string($doneAt) ? $doneAt : (optional($doneAt)->format('Y-m-d H:i') ?? '-') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-zinc-400 py-10">
                      No recent data to display
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- ===== Footer (optional override) ===== --}}
  <x-slot name="footer">
    <div class="text-xs text-zinc-400">
      © {{ date('Y') }} {{ config('app.name','Asset Repair') }} — Dashboard
    </div>
  </x-slot>

  {{-- ===== Charts and UX helpers ===== --}}
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

        const cfg = (type === 'pie') ? {
          type:'pie',
          data:{ labels, datasets:[{ data: values }] },
          options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10 } } } }
        } : (type === 'bar') ? {
          type:'bar',
          data:{ labels, datasets:[{ data: values }] },
          options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } },
            scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(255,255,255,.06)' } },
                     x:{ grid:{ display:false } } } }
        } : {
          type:'line',
          data:{ labels, datasets:[{ data: values, tension:.35, pointRadius:2, borderWidth:2 }] },
          options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } },
            scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(255,255,255,.06)' } },
                     x:{ grid:{ color:'rgba(255,255,255,.04)' } } } }
        };
        new Chart(el, cfg);
      }catch(e){ console.warn('[ChartJS] render error', e); }
    }

    // lazy render when visible
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
</x-app-layout>
