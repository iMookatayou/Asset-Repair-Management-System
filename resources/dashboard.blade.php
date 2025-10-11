<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-lg sm:text-xl text-gray-100 leading-tight">
      {{ __('Asset Repair Dashboard (Compact)') }}
    </h2>
  </x-slot>

  <style>
    .chart-card { position: relative; height: 220px; } /* เตี้ยลง */
    @media (min-width: 1024px) { .chart-card { height: 260px; } }
    .section-card { border-radius: .875rem; }
    .section-head { padding:.6rem .9rem; font-weight:600; }
    .section-body { padding:.9rem; }
    .kpi { border-radius:.875rem; padding:.8rem; }
    .kpi-title { font-size:.7rem; color:#9ca3af; }
    .kpi-value { font-size:1.35rem; font-weight:700; line-height:1.1; }
    .tbl th, .tbl td { padding:.5rem .6rem; font-size:.82rem; }
    .tbl thead th { font-size:.68rem; letter-spacing:.02em; }
  </style>

  @php
    // --- บีบข้อมูลให้เล็กลง ตั้งแต่ Blade (กัน JSON ก้อนโต) ---
    $stats        = is_array($stats ?? null) ? $stats : [];
    $monthlyTrend = is_iterable($monthlyTrend ?? null) ? collect($monthlyTrend) : collect();
    $byAssetType  = is_iterable($byAssetType ?? null)  ? collect($byAssetType)  : collect();
    $byDept       = is_iterable($byDept ?? null)       ? collect($byDept)       : collect();
    $recent       = is_iterable($recent ?? null)       ? $recent                : [];

    // จำกัดจำนวนจุด/กลุ่ม
    $monthlyTrend = $monthlyTrend->take(6)->values();      // 6 เดือนล่าสุดพอ
    $byAssetType  = $byAssetType->take(9)->values();       // top 8 + อื่นๆ = 9
    $byDept       = $byDept->take(8)->values();            // top 8 แผนก

    // แปลงเป็น "อาเรย์แบบ primitive" เพื่อลดขนาด @json
    $trendLabels = $monthlyTrend->map(fn($i)=>$i['ym']   ?? ($i->ym   ?? ''))->all();
    $trendCounts = $monthlyTrend->map(fn($i)=>(int)($i['cnt'] ?? ($i->cnt ?? 0)))->all();

    $typeLabels  = $byAssetType->map(fn($i)=>$i['type'] ?? ($i->type ?? 'ไม่ระบุ'))->all();
    $typeCounts  = $byAssetType->map(fn($i)=>(int)($i['cnt'] ?? ($i->cnt ?? 0)))->all();

    $deptLabels  = $byDept->map(fn($i)=>$i['dept'] ?? ($i->dept ?? 'ไม่ระบุ'))->all();
    $deptCounts  = $byDept->map(fn($i)=>(int)($i['cnt'] ?? ($i->cnt ?? 0)))->all();
  @endphp

  <div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-4">

      {{-- KPI ROW (คงสั้น ๆ) --}}
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-zinc-100">
          <div class="kpi-title">ทั้งหมด</div>
          <div class="kpi-value">{{ number_format($stats['total'] ?? 0) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-yellow-200">
          <div class="kpi-title">รอดำเนินการ</div>
          <div class="kpi-value">{{ number_format($stats['pending'] ?? 0) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-sky-200">
          <div class="kpi-title">กำลังซ่อม</div>
          <div class="kpi-value">{{ number_format($stats['inProgress'] ?? 0) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-emerald-200">
          <div class="kpi-title">เสร็จแล้ว</div>
          <div class="kpi-value">{{ number_format($stats['completed'] ?? 0) }}</div>
        </div>
        <div class="kpi border border-zinc-800 bg-[#0f1a2a] text-zinc-100">
          <div class="kpi-title">ค่าใช้จ่ายเดือนนี้</div>
          <div class="kpi-value">{{ number_format($stats['monthCost'] ?? 0, 2) }}</div>
        </div>
      </div>

      {{-- CHARTS (Compact + Lazy render) --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
          <div class="section-head border-b border-zinc-700 text-zinc-100">แนวโน้มรายเดือน (6 เดือน)</div>
          <div class="section-body">
            <div class="chart-card">
              <canvas id="trendChart"
                      data-labels='@json($trendLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                      data-values='@json($trendCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
            </div>
          </div>
        </div>

        <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
          <div class="section-head border-b border-zinc-700 text-zinc-100">ประเภททรัพย์สิน (Top 8 + อื่นๆ)</div>
          <div class="section-body">
            <div class="chart-card">
              <canvas id="typePie"
                      data-labels='@json($typeLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                      data-values='@json($typeCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="section-card border border-zinc-800 bg-[#0f1a2a]">
        <div class="section-head border-b border-zinc-700 text-zinc-100">งานตามแผนก (Top 8)</div>
        <div class="section-body">
          <div class="chart-card">
            <canvas id="deptBar"
                    data-labels='@json($deptLabels, JSON_INVALID_UTF8_SUBSTITUTE)'
                    data-values='@json($deptCounts, JSON_INVALID_UTF8_SUBSTITUTE)'></canvas>
          </div>
        </div>
      </div>

      {{-- RECENT TABLE (แสดงแค่ไม่กี่แถว ถ้าอยากสุดเบาให้ paginate ที่ Controller) --}}
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
                @forelse($recent as $t)
                  @php
                    $status = (string) data_get($t,'status','');
                    $badgeClass =
                      $status === \App\Models\MaintenanceRequest::STATUS_PENDING     ? 'bg-yellow-200/20 text-yellow-300' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_IN_PROGRESS ? 'bg-sky-200/20 text-sky-300' :
                      ($status === \App\Models\MaintenanceRequest::STATUS_COMPLETED   ? 'bg-emerald-200/20 text-emerald-300' :
                                                                                         'bg-zinc-200/20 text-zinc-300'));
                  @endphp
                  <tr class="hover:bg-[#0b1422]">
                    <td>{{ optional(data_get($t,'request_date'))->format('Y-m-d H:i') }}</td>
                    <td>#{{ e((string) data_get($t,'asset_id','-')) }} — {{ e((string) data_get($t,'asset.name','-')) }}</td>
                    <td>{{ e((string) data_get($t,'reporter.name','-')) }}</td>
                    <td><span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] {{ $badgeClass }}">
                      {{ ucfirst(str_replace('_',' ', $status)) }}</span>
                    </td>
                    <td>{{ e((string) data_get($t,'technician.name','-')) }}</td>
                    <td>{{ optional(data_get($t,'completed_date'))->format('Y-m-d H:i') ?? '-' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center text-zinc-400 py-6">ไม่มีข้อมูล</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- Lazy-load Chart.js & lazy-render charts --}}
  <script>
    // โหลด Chart.js เฉพาะเมื่อจำเป็น
    function loadChartJsOnce(cb){
      if (window.Chart) return cb();
      const s=document.createElement('script');
      s.src="https://cdn.jsdelivr.net/npm/chart.js"; s.onload=cb; document.head.appendChild(s);
    }

    function makeChart(el, type){
      const labels = JSON.parse(el.dataset.labels || '[]');
      const values = JSON.parse(el.dataset.values || '[]');
      if (!labels.length || !values.length) return;

      const cfg = (type === 'pie') ? {
        type:'pie',
        data:{ labels, datasets:[{ data: values }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10 } } } }
      } : (type === 'bar') ? {
        type:'bar',
        data:{ labels, datasets:[{ data: values }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } },
          scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(255,255,255,.06)' } }, x:{ grid:{ display:false } } } }
      } : {
        type:'line',
        data:{ labels, datasets:[{ data: values, tension:.35, pointRadius:2, borderWidth:2 }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } },
          scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(255,255,255,.06)' } }, x:{ grid:{ color:'rgba(255,255,255,.04)' } } } }
      };

      new Chart(el, cfg);
    }

    // วาดเมื่อเข้าสายตา (ลดงานตอนโหลด)
    const obs = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          const c = e.target;
          loadChartJsOnce(()=> makeChart(c,
            c.id==='typePie' ? 'pie' : (c.id==='deptBar' ? 'bar' : 'line')));
          obs.unobserve(c);
        }
      });
    },{ root:null, threshold:0.1 });

    ['trendChart','typePie','deptBar'].forEach(id=>{
      const el = document.getElementById(id);
      if(el) obs.observe(el);
    });
  </script>
</x-app-layout>
