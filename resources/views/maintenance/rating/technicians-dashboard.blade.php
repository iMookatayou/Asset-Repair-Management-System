@extends('layouts.app')

@section('title', 'รายงานผลการประเมินช่างบริการ')

@php
    $avg = round($technicians->avg('technician_ratings_avg_score'), 2);
    $sumReviews = $technicians->sum('technician_ratings_count');
    $totalTech = $technicians->count();
    $percent = ($avg > 0) ? ($avg / 5) * 100 : 0;

    $chartTechs  = $technicians->sortByDesc('technician_ratings_avg_score')->take(6);
    $chartLabels = $chartTechs->pluck('name');
    $chartScores = $chartTechs->pluck('technician_ratings_avg_score')->map(fn($v)=>round($v,2));
@endphp

{{-- ✅ กล่องได้แค่นี่: Sticky Header เท่านั้น --}}
@section('page-header')
  <div class="px-4 sm:px-6 lg:px-10 2xl:px-20 pt-3">
    <div class="overflow-hidden bg-white/90 backdrop-blur-sm border border-slate-200 rounded-2xl shadow-sm">
      <div class="flex flex-wrap items-start justify-between gap-4 px-4 sm:px-6 py-4">

        <div class="flex items-start gap-3 flex-1 min-w-0">
          <div class="hidden sm:flex">
            <div class="h-11 w-11 rounded-xl bg-slate-800 text-slate-50 grid place-items-center">
              <svg xmlns="http://www.w3.org/2000/svg"
                   class="h-5 w-5"
                   viewBox="0 0 24 24"
                   fill="none"
                   stroke="currentColor"
                   stroke-width="1.8"
                   stroke-linecap="round"
                   stroke-linejoin="round">
                <path d="M3 5a4 4 0 0 1 6.5-2.9L7 4.6 9.4 7l2.5-2.5A4 4 0 1 1 13 11L9 15H7v-2L11 9a2 2 0 1 0-2.8-2.8L6 8.4 3.6 6z" />
                <circle cx="18" cy="18" r="3" />
              </svg>
            </div>
          </div>

          <div class="flex-1 min-w-0">
            <div class="inline-flex items-center gap-2 rounded-full bg-slate-900/5 px-4 py-1 text-[11px] font-semibold text-slate-800">
              รายงานผลการประเมินช่างผู้ให้บริการซ่อมบำรุงครุภัณฑ์
            </div>

            <h1 class="mt-2 text-lg md:text-xl font-semibold text-slate-900 leading-snug">
              รายงานผลการประเมินช่างผู้ให้บริการซ่อมบำรุง
            </h1>

            <p class="mt-1 text-xs text-slate-700">
              สรุปผลคะแนนเฉลี่ย จำนวนครั้งประเมิน และระดับผลการประเมินของช่างผู้ให้บริการ
            </p>
          </div>
        </div>

        <div class="text-[11px] sm:text-xs text-slate-700 text-right">
          หน่วยงานที่รับผิดชอบ : กลุ่มงานเทคโนโลยีสารสนเทศ
        </div>

      </div>

      <div class="border-t border-slate-200 bg-slate-50/70 px-4 sm:px-6 py-1.5">
        <span class="text-[11px] text-slate-800">
          ข้อมูลประเมินจากระบบงานซ่อมบำรุง ใช้เพื่อการบริหารจัดการภายในหน่วยงาน
        </span>
      </div>
    </div>
  </div>
@endsection


@section('content')
<div class="px-4 sm:px-6 lg:px-10 2xl:px-20 pt-2 pb-10">

  {{-- ✅ ทั้งหน้ารวมเป็น “ส่วนเดียว” ไม่มีการ์ด/กล่อง --}}
  <section class="space-y-8">

    {{-- แถบควบคุม + สรุปภาพรวม (อยู่ส่วนเดียวกัน) --}}
    <div class="space-y-5">

      {{-- ค้นหา + เรียง --}}
      <div class="grid gap-4 lg:gap-6 md:grid-cols-3 md:items-end">
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-slate-800 mb-1.5">
            ค้นหาชื่อช่างผู้ให้บริการ
          </label>
          <div class="relative">
            <input type="text"
                   class="w-full border border-slate-300 text-sm px-3 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-700 bg-white"
                   placeholder="กรอกชื่อช่างที่ต้องการค้นหา">
            <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-xs">
              <i class="fa-solid fa-magnifying-glass"></i>
            </span>
          </div>
          <p class="mt-2 text-[11px] text-slate-600">
            * ฟังก์ชันนี้สามารถเชื่อมต่อ Controller ภายหลังเพื่อใช้งานจริง
          </p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-800 mb-1.5">
            รูปแบบการเรียงลำดับข้อมูล
          </label>
          <select class="w-full border border-slate-300 text-sm px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-700">
            <option>คะแนนเฉลี่ยมาก → น้อย</option>
            <option>คะแนนเฉลี่ยน้อย → มาก</option>
            <option>จำนวนครั้งมาก → น้อย</option>
            <option>จำนวนครั้งน้อย → มาก</option>
          </select>
        </div>
      </div>

      <div class="h-px bg-slate-200"></div>

      {{-- สรุปตัวเลขภาพรวม (ไม่มีการ์ด) --}}
      <div>
        <h2 class="text-sm font-semibold text-slate-900">
          สรุปภาพรวมผลการประเมินช่างผู้ให้บริการ
        </h2>

        <div class="mt-3 grid md:grid-cols-3 gap-4">
          <div>
            <p class="text-[11px] text-slate-700">จำนวนช่างที่มีข้อมูลประเมิน</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $totalTech }}</p>
          </div>

          <div>
            <p class="text-[11px] text-slate-700">คะแนนเฉลี่ยรวม</p>
            <div class="flex items-baseline gap-2 mt-1">
              <p class="text-3xl font-semibold text-slate-900">{{ number_format($avg,2) }}</p>
              <span class="text-xs text-slate-600">เต็ม 5</span>
            </div>

            <div class="h-2 bg-slate-200 rounded-full mt-2 overflow-hidden">
              <div class="h-full bg-blue-700 rounded-full" style="width: {{ $percent }}%"></div>
            </div>
          </div>

          <div>
            <p class="text-[11px] text-slate-700">จำนวนครั้งการประเมินรวม</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">{{ number_format($sumReviews) }}</p>
          </div>
        </div>
      </div>

    </div>


    {{-- กราฟ (อยู่ในหน้าแบบโปร่ง ไม่มีการ์ด) --}}
    @if($technicians->count())
      <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold text-slate-900">
            คะแนนเฉลี่ยของช่างผู้ให้บริการ (อันดับสูงสุด)
          </p>
          <p class="text-[11px] text-slate-600">
            แสดง 6 อันดับแรก
          </p>
        </div>

        <div style="height:280px">
          <canvas id="techRatingChart"></canvas>
        </div>

        <div class="h-px bg-slate-200"></div>
      </div>
    @endif


    {{-- ตารางรายละเอียด --}}
    <div class="space-y-3">
      <div class="flex items-end justify-between gap-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-900">
            รายละเอียดผลการประเมินช่างผู้ให้บริการ (รายบุคคล)
          </h2>
          <p class="mt-1 text-xs text-slate-600">
            จำนวนช่างทั้งหมด {{ $totalTech }} ราย
          </p>
        </div>

        {{-- legend แบบเรียบ ๆ ไม่ครอบ --}}
        <div class="hidden sm:flex items-center gap-5 text-[11px] text-slate-700">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                ดีมาก / ดี
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                ปานกลาง
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                ควรปรับปรุง
            </span>
        </div>
      </div>

      @if($technicians->isEmpty())
        <div class="py-10 text-center text-sm text-slate-600">
          ยังไม่มีข้อมูลประเมิน
        </div>
      @else
        <div class="overflow-x-auto border border-slate-300 rounded-lg bg-white">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-300 text-xs text-slate-900">
                <th class="px-2 py-2 text-center w-14">ลำดับ</th>
                <th class="px-3 py-2 text-left">ชื่อ–สกุล</th>
                <th class="px-3 py-2 text-center w-32">คะแนนเฉลี่ย</th>
                <th class="px-3 py-2 text-center w-40">รูปแบบดาว</th>
                <th class="px-3 py-2 text-center w-32">จำนวนครั้ง</th>
                <th class="px-3 py-2 text-center w-32">ระดับ</th>
                <th class="px-3 py-2 text-center w-32">ดูข้อมูล</th>
              </tr>
            </thead>

            <tbody>
              @foreach($technicians as $i => $t)
                @php
                  $avgScore  = round($t->technician_ratings_avg_score,2);
                  $roundStar = round($t->technician_ratings_avg_score);

                  $level =
                      $avgScore >= 4.5 ? 'ดีมาก' :
                      ($avgScore >= 4.0 ? 'ดี' :
                      ($avgScore >= 3.0 ? 'ปานกลาง' : 'ควรปรับปรุง'));

                  $levelClass =
                      $avgScore >= 4.0 ? 'text-emerald-700 font-semibold' :
                      ($avgScore >= 3.0 ? 'text-amber-700 font-semibold' :
                      'text-rose-700 font-semibold');
                @endphp

                <tr class="{{ $loop->odd ? 'bg-white' : 'bg-slate-50' }} hover:bg-slate-100/70 transition">
                  <td class="px-2 py-2 text-center">{{ $i+1 }}</td>
                  <td class="px-3 py-2">{{ $t->name }}</td>
                  <td class="px-3 py-2 text-center">{{ number_format($avgScore,2) }}</td>

                  <td class="px-3 py-2 text-center">
                    <div class="inline-flex items-center gap-0.5">
                      @for($s=1;$s<=5;$s++)
                        @if($s <= $roundStar)
                          <i class="fa-solid fa-star text-yellow-400 text-xs"></i>
                        @else
                          <i class="fa-regular fa-star text-slate-300 text-xs"></i>
                        @endif
                      @endfor
                      <span class="ml-1 text-[11px] text-slate-600">({{ number_format($avgScore,2) }})</span>
                    </div>
                  </td>

                  <td class="px-3 py-2 text-center">{{ number_format($t->technician_ratings_count) }}</td>

                  {{-- ✅ ไม่ครอบ ไม่ทำ badge แค่สี/ตัวหนา --}}
                  <td class="px-3 py-2 text-center text-xs {{ $levelClass }}">{{ $level }}</td>

                  <td class="px-3 py-2 text-center">
                    <a href="#" class="text-[11px] px-2.5 py-1 border border-slate-400 hover:bg-slate-100 transition">
                      ดูรายละเอียด
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>

          </table>
        </div>
      @endif
    </div>

  </section>
</div>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById('techRatingChart');
  if (!ctx) return;

  const labels = @json($chartLabels);
  const data   = @json($chartScores);

  new Chart(ctx,{
    type:'bar',
    data:{
      labels,
      datasets:[{
        data,
        backgroundColor:'rgba(37,99,235,0.75)',
        borderColor:'rgba(30,64,175,1)',
        borderWidth:1
      }]
    },
    options:{
      maintainAspectRatio:false,
      scales:{
        y:{ beginAtZero:true, max:5, ticks:{ stepSize:1 } }
      },
      plugins:{
        legend:{ display:false },
        tooltip:{
          callbacks:{
            label:ctx=>` ${ctx.parsed.y.toFixed(2)} คะแนน`
          }
        }
      }
    }
  });
});
</script>
@endsection
