@extends('layouts.app')

@section('title', 'รายงานผลการประเมินช่างบริการ')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-0 pt-10 md:pt-16 lg:pt-20 pb-12">

    {{-- หัวรายงาน --}}
    <div class="border-b border-slate-200 pb-5 mb-7">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div>
                <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight">
                    Service technician evaluation list
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    ภาพรวมคะแนนเฉลี่ยและจำนวนการประเมินของช่างทั้งหมดในระบบ
                </p>
            </div>
            <div class="text-[11px] md:text-xs text-slate-500 md:text-right leading-relaxed">
                <div>จัดทำโดย : หน่วยงานที่รับผิดชอบ</div>
                <div>อัปเดตข้อมูลล่าสุด : {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- แถวค้นหา / เรียงลำดับ --}}
    <div class="bg-white border border-slate-200 rounded-md p-4 md:p-5 mb-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-700 mb-1.5">
                    ค้นหาชื่อช่าง
                </label>
                <div class="relative">
                    <input
                        type="text"
                        placeholder="ระบุชื่อช่างที่ต้องการค้นหา (สามารถเชื่อมต่อกับ Controller ภายหลัง)"
                        class="w-full border border-slate-300 rounded-md text-sm px-3 py-2.5 pr-9 focus:border-blue-700 focus:ring-blue-700"
                    >
                    <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1.5">
                    รูปแบบการเรียงลำดับ
                </label>
                <select
                    class="w-full border border-slate-300 rounded-md text-sm px-3 py-2.5 focus:border-blue-700 focus:ring-blue-700 bg-white"
                >
                    <option value="">คะแนนเฉลี่ยจากมากไปน้อย</option>
                    <option value="">คะแนนเฉลี่ยจากน้อยไปมาก</option>
                    <option value="">จำนวนรีวิวจากมากไปน้อย</option>
                    <option value="">จำนวนรีวิวจากน้อยไปมาก</option>
                </select>
            </div>
        </div>
        <p class="mt-3 text-[11px] text-slate-500">
            * หมายเหตุ: ส่วนของการค้นหาและการเรียงลำดับสามารถเชื่อมต่อกับฐานข้อมูลและ Controller ตามความเหมาะสม
        </p>
    </div>

    @php
        $overallAvg     = round($technicians->avg('technician_ratings_avg_score'), 2);
        $overallReviews = $technicians->sum('technician_ratings_count');
        $overallTechs   = $technicians->count();
        $overallPercent = $overallAvg > 0 ? min(100, max(0, ($overallAvg / 5) * 100)) : 0;
    @endphp

    {{-- การ์ดสรุป 3 ใบ --}}
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-md p-4">
            <p class="text-[11px] font-medium text-slate-600 tracking-wide">
                จำนวนช่างทั้งหมด
            </p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">
                {{ $overallTechs }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                ช่างที่มีข้อมูลคะแนนประเมินในระบบ
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-md p-4">
            <p class="text-[11px] font-medium text-slate-600 tracking-wide">
                คะแนนเฉลี่ยรวม
            </p>
            <div class="mt-2 flex items-baseline gap-2">
                <p class="text-3xl font-semibold text-slate-900">
                    {{ number_format($overallAvg, 2) }}
                </p>
                <span class="text-sm text-slate-500">/ 5 คะแนน</span>
            </div>
            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                <div
                    class="h-full bg-blue-600"
                    style="width: {{ $overallPercent }}%;"
                ></div>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                ค่าเฉลี่ยจากคะแนนประเมินของช่างทุกคน
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-md p-4">
            <p class="text-[11px] font-medium text-slate-600 tracking-wide">
                จำนวนรีวิวรวมทั้งหมด
            </p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">
                {{ number_format($overallReviews) }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                จำนวนครั้งที่มีการประเมินจากผู้รับบริการทั้งหมด
            </p>
        </div>
    </div>

    {{-- กราฟคะแนนเฉลี่ยช่าง (Chart.js) --}}
    @if($technicians->count() > 0)
        @php
            $chartTechs  = $technicians->sortByDesc('technician_ratings_avg_score')->take(6);
            $chartLabels = $chartTechs->pluck('name');
            $chartScores = $chartTechs->pluck('technician_ratings_avg_score')->map(fn($v)=>round($v,2));
        @endphp

        <div class="bg-white border border-slate-200 rounded-md p-4 md:p-5 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
                <p class="text-sm font-medium text-slate-800 mb-0">
                    กราฟคะแนนเฉลี่ยช่าง (Top {{ $chartTechs->count() }})
                </p>
                <p class="text-xs text-slate-500 mb-0">
                    แสดงคะแนนเฉลี่ยของช่างที่มีคะแนนสูงสุดเรียงจากมากไปน้อย
                </p>
            </div>
            <div style="height: 260px;">
                <canvas id="techRatingChart"></canvas>
            </div>
        </div>
    @endif

    {{-- ตารางรายละเอียดคะแนนช่าง --}}
    <div class="bg-white border border-slate-200 rounded-md overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <p class="text-sm font-medium text-slate-800">
                รายละเอียดคะแนนแบบประเมินช่างบริการ
            </p>
            <p class="text-xs text-slate-500">
                แสดงข้อมูลช่างทั้งหมดจำนวน {{ $overallTechs }} ราย
            </p>
        </div>

        @if ($technicians->isEmpty())
            <div class="px-4 py-8 text-center text-sm text-slate-500">
                ยังไม่มีข้อมูลคะแนนช่างในระบบ
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-t border-slate-200">
                    <thead>
                        <tr class="bg-blue-50 border-b border-slate-200">
                            <th class="border border-slate-200 px-2 py-2 text-center w-14 text-xs font-semibold text-slate-800">
                                ลำดับ
                            </th>
                            <th class="border border-slate-200 px-3 py-2 text-left text-xs font-semibold text-slate-800">
                                ชื่อช่าง
                            </th>
                            <th class="border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-800 w-32">
                                คะแนนเฉลี่ย (เต็ม 5)
                            </th>
                            <th class="border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-800 w-40">
                                แสดงผลเป็นดาว
                            </th>
                            <th class="border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-800 w-32">
                                จำนวนรีวิว (ครั้ง)
                            </th>
                            <th class="border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-800 w-32">
                                ระดับผลประเมิน
                            </th>
                            <th class="border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-800 w-32">
                                การดำเนินการ
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($technicians as $index => $tech)
                            @php
                                $avg        = round($tech->technician_ratings_avg_score, 2);
                                $avgRounded = round($tech->technician_ratings_avg_score);

                                if ($avg >= 4.5)      $levelLabel = 'ดีมาก';
                                elseif ($avg >= 4.0) $levelLabel = 'ดี';
                                elseif ($avg >= 3.0) $levelLabel = 'ปานกลาง';
                                else                  $levelLabel = 'ควรปรับปรุง';
                            @endphp
                            <tr class="{{ $loop->odd ? 'bg-white' : 'bg-slate-50/70' }}">
                                <td class="border border-slate-200 px-2 py-2 text-center text-xs text-slate-800">
                                    {{ $index + 1 }}
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-sm text-slate-900">
                                    {{ $tech->name }}
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-center text-sm text-slate-900">
                                    {{ number_format($avg, 2) }}
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-center">
                                    <div class="inline-flex items-center gap-0.5 text-xs">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $avgRounded)
                                                <i class="fa-solid fa-star text-yellow-400"></i>
                                            @else
                                                <i class="fa-regular fa-star text-slate-300"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-1 text-[11px] text-slate-500">
                                            ({{ number_format($avg, 2) }})
                                        </span>
                                    </div>
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-center text-sm text-slate-900">
                                    {{ $tech->technician_ratings_count }}
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-center text-xs text-slate-900">
                                    {{ $levelLabel }}
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-center text-xs">
                                    <a
                                        href="#"
                                        class="inline-flex items-center justify-center px-2.5 py-1 border border-slate-300 rounded-sm text-[11px] text-slate-700 hover:bg-slate-100"
                                    >
                                        ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50 text-[11px] text-slate-600">
                หมายเหตุ: ข้อมูลดังกล่าวเป็นผลการประเมินเบื้องต้นจากผู้รับบริการ
                และสามารถใช้ประกอบการพิจารณาแผนพัฒนาศักยภาพช่างบริการในระยะต่อไปได้ตามความเหมาะสม
            </div>
        @endif
    </div>

</div>
@endsection

{{-- สคริปต์สำหรับกราฟ --}}
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('techRatingChart');
            if (!ctx) return;

            const labels = @json($chartLabels ?? []);
            const data   = @json($chartScores ?? []);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'คะแนนเฉลี่ย (เต็ม 5)',
                        data: data,
                        borderWidth: 1,
                        backgroundColor: 'rgba(37, 99, 235, 0.75)',
                        borderColor: 'rgba(30, 64, 175, 1)',
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            ticks: { stepSize: 1 }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ' ' + ctx.parsed.y.toFixed(2) + ' คะแนน';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
