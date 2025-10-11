<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-lg sm:text-xl text-gray-100 leading-tight">
      {{ __('Asset Repair Dashboard — Graphs Only') }}
    </h2>
  </x-slot>

  <style>
    .chart-card{position:relative;height:220px}
    @media (min-width:1024px){.chart-card{height:260px}}
    .section-card{border-radius:.875rem}
    .section-head{padding:.6rem .9rem;font-weight:600}
    .section-body{padding:.9rem}
  </style>

  @php
    // เตรียมอาเรย์ primitive ให้เล็กสุด
    $trendLabels = collect($monthlyTrend)->map(fn($i)=>$i->ym)->all();
    $trendCounts = collect($monthlyTrend)->map(fn($i)=>(int)$i->cnt)->all();

    $typeLabels  = collect($byAssetType)->map(fn($i)=>$i->type)->all();
    $typeCounts  = collect($byAssetType)->map(fn($i)=>(int)$i->cnt)->all();

    $deptLabels  = collect($byDept)->map(fn($i)=>$i->dept)->all();
    $deptCounts  = collect($byDept)->map(fn($i)=>(int)$i->cnt)->all();
  @endphp

  <div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-4">

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

    </div>
  </div>

  <script>
    // โหลด Chart.js เมื่อจำเป็น
    function loadChartJsOnce(cb){
      if(window.Chart) return cb();
      const s=document.createElement('script');
      s.src="https://cdn.jsdelivr.net/npm/chart.js"; s.onload=cb; document.head.appendChild(s);
    }
    function makeChart(el, type){
      const labels = JSON.parse(el.dataset.labels||'[]');
      const values = JSON.parse(el.dataset.values||'[]');
      if(!labels.length || !values.length) return;

      const cfg = (type==='pie') ? {
        type:'pie',
        data:{labels, datasets:[{data:values}]},
        options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom',labels:{boxWidth:10}}}}
      } : (type==='bar') ? {
        type:'bar',
        data:{labels, datasets:[{data:values}]},
        options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
          scales:{y:{beginAtZero:true, grid:{color:'rgba(255,255,255,.06)'}}, x:{grid:{display:false}}}}
      } : {
        type:'line',
        data:{labels, datasets:[{data:values, tension:.35, pointRadius:2, borderWidth:2}]},
        options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
          scales:{y:{beginAtZero:true, grid:{color:'rgba(255,255,255,.06)'}}, x:{grid:{color:'rgba(255,255,255,.04)'}}}}
      };
      new Chart(el, cfg);
    }
    // lazy render ด้วย IntersectionObserver
    const obs = new IntersectionObserver((ents)=>{
      ents.forEach(e=>{
        if(e.isIntersecting){
          const c=e.target;
          loadChartJsOnce(()=>makeChart(c, c.id==='typePie'?'pie':(c.id==='deptBar'?'bar':'line')));
          obs.unobserve(c);
        }
      });
    },{threshold:0.1});
    ['trendChart','typePie','deptBar'].forEach(id=>{const el=document.getElementById(id); if(el) obs.observe(el);});
  </script>
</x-app-layout>
