@extends('layouts.app')
@section('title','My Jobs')

@section('content')
@php
  use Illuminate\Support\Str;

  $q        = $q ?? request('q');
  $status   = $status ?? request('status');
  $filter   = $filter ?? 'all';
  $tech     = $tech ?? request('tech');

  $filterLabels = [
    'my' => 'งานของฉัน',
    'available' => 'งานว่าง',
    'all' => 'ทั้งหมด',
  ];
  $human = fn($s) => [
    'pending'     => 'รอดำเนินการ',
    'accepted'    => 'รับงานแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
    'on_hold'     => 'พักไว้ชั่วคราว',
    'resolved'    => 'แก้ไขเสร็จสิ้น',
    'closed'      => 'ปิดงาน',
  ][$s] ?? Str::of($s)->replace('_',' ')->title();
@endphp

<div class="container mx-auto px-4 py-4 max-w-[1600px]" id="myJobsContainer">
  {{-- Header + Stats + Search/Filter in a single sticky card --}}
  <div class="mb-4 sticky top-[5.5rem] z-20 bg-slate-50/90 backdrop-blur">
    <div class="bg-white rounded-lg border border-gray-200 p-3 lg:p-4 shadow-sm">
      <div class="flex flex-col gap-3">
        {{-- Top row: Icon + Title + Stats --}}
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
          <div class="flex items-center gap-3">
            <svg class="h-8 w-8 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <rect x="3" y="7" width="18" height="13" rx="2" class="stroke-current"></rect>
              <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" class="stroke-current"></path>
              <path d="M3 12h18" class="stroke-current"></path>
            </svg>
            <div>
              <h1 class="text-xl font-bold text-gray-900">My Jobs</h1>
              <p class="text-xs text-gray-600 mt-0.5">จัดการและติดตามงานซ่อมบำรุงทรัพย์สิน</p>
            </div>
          </div>

          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 w-full lg:w-auto">
            <div class="text-center lg:px-2">
              <div class="text-[10px] text-gray-600 mb-0.5">รอดำเนินการ</div>
              <div class="text-xl font-bold text-amber-600" id="stat-pending">{{ $stats['pending'] ?? 0 }}</div>
            </div>
            <div class="text-center lg:border-l lg:border-gray-200 lg:pl-4">
              <div class="text-[10px] text-gray-600 mb-0.5">กำลังดำเนินการ</div>
              <div class="text-xl font-bold text-blue-600" id="stat-in-progress">{{ $stats['in_progress'] ?? 0 }}</div>
            </div>
            <div class="text-center lg:border-l lg:border-gray-200 lg:pl-4">
              <div class="text-[10px] text-gray-600 mb-0.5">เสร็จสิ้น</div>
              <div class="text-xl font-bold text-green-600" id="stat-completed">{{ $stats['completed'] ?? 0 }}</div>
            </div>
            <div class="text-center lg:border-l lg:border-gray-200 lg:pl-4">
              <div class="text-[10px] text-gray-600 mb-0.5">งานของฉัน</div>
              <div class="text-xl font-bold text-indigo-600" id="stat-my-active">{{ $stats['my_active'] ?? 0 }}</div>
            </div>
          </div>
        </div>

        {{-- Search/Filter Form --}}
        <div class="pt-3 border-t border-gray-200">
          <form method="GET" class="flex flex-col sm:flex-row gap-2">
            <div class="flex-1 min-w-[280px]">
              <input type="text" name="q" value="{{ $q }}" placeholder="ค้นหาเรื่อง, ทรัพย์สิน..."
                     class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <div class="w-full sm:w-52">
              <select name="filter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                @foreach(['all' => 'ทั้งหมด', 'my' => 'งานของฉัน', 'available' => 'งานว่าง'] as $key => $label)
                  <option value="{{ $key }}" @selected($filter===$key)>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="w-full sm:w-56">
              <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">ทุกสถานะ</option>
                @foreach(['pending','accepted','in_progress','on_hold','resolved','closed'] as $s)
                  <option value="{{ $s }}" @selected($status===$s)>{{ $human($s) }}</option>
                @endforeach
              </select>
            </div>

            @if($tech)
              <input type="hidden" name="tech" value="{{ $tech }}" />
            @endif

            <div class="flex gap-2 shrink-0">
              <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                ค้นหา
              </button>
              @if($q || $status)
                <a href="{{ route('repairs.my_jobs', ['filter' => $filter]) }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                  ล้าง
                </a>
              @endif
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Main Card --}}
  <div class="bg-white rounded-lg border border-gray-200">
    {{-- Table --}}
    <div class="overflow-x-auto">
      @php
        $activeTech = isset($tech) && isset($team) ? $team->firstWhere('id', (int)$tech) : null;
      @endphp
      @if($activeTech)
        <div class="px-4 py-2 bg-indigo-50 text-indigo-800 border-b border-indigo-200">
          <div class="text-sm">กำลังดูงานของ: <span class="font-medium">{{ $activeTech->name }}</span></div>
        </div>
      @endif
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-[35%]">เรื่อง</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-40">ทรัพย์สิน</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-28">ความสำคัญ</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-36">สถานะ</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-40">ช่างผู้รับผิดชอบ</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider whitespace-nowrap min-w-[200px]">ดำเนินการ</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        @forelse($list as $r)
          @php
            $canAccept = !$r->technician_id && in_array($r->status, ['pending','accepted']);

            $priorityClasses = [
              'low'    => 'bg-green-100 text-green-800 border-green-200',
              'medium' => 'bg-blue-100 text-blue-800 border-blue-200',
              'high'   => 'bg-amber-100 text-amber-800 border-amber-200',
              'urgent' => 'bg-red-100 text-red-800 border-red-200',
            ];
            $priorityClass = $priorityClasses[strtolower($r->priority ?? 'medium')] ?? $priorityClasses['medium'];

            $statusClasses = [
              'pending'     => 'bg-amber-100 text-amber-800 border-amber-200',
              'accepted'    => 'bg-blue-100 text-blue-800 border-blue-200',
              'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
              'on_hold'     => 'bg-gray-100 text-gray-800 border-gray-200',
              'resolved'    => 'bg-green-100 text-green-800 border-green-200',
              'closed'      => 'bg-gray-200 text-gray-700 border-gray-300',
            ];
            $statusClass = $statusClasses[$r->status] ?? $statusClasses['pending'];
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <a href="{{ route('maintenance.requests.show', $r) }}" class="block max-w-full truncate font-medium text-indigo-600 hover:text-indigo-900 hover:underline">
                #{{ $r->id }} — {{ $r->title }}
              </a>
              @if($r->description)
                <div class="text-xs text-gray-500 mt-0.5 max-w-full truncate">{{ Str::limit($r->description, 60) }}</div>
              @endif
            </td>
            <td class="px-4 py-3 text-gray-700 max-w-[220px] truncate">{{ $r->asset->name ?? '-' }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $priorityClass }}">
                {{ ucfirst(strtolower($r->priority ?? 'medium')) }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $statusClass }}">
                {{ $human($r->status) }}
              </span>
            </td>
            <td class="px-4 py-3">
              @if($r->technician)
                <span class="text-sm text-gray-700">{{ $r->technician->name }}</span>
              @else
                <span class="text-gray-400 text-sm">-</span>
              @endif
            </td>
            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
              <div class="h-full flex items-center justify-center gap-2">
                @if($canAccept)
                  <form method="POST" action="{{ route('repairs.accept', $r) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500">
                      รับงาน
                    </button>
                  </form>
                @endif
                <a href="{{ route('maintenance.requests.show', $r) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                  ดูรายละเอียด
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-12 text-center">
              <div class="text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">ไม่พบรายการ</p>
              </div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  @if($list->hasPages())
  <div class="mt-4">
    {{ $list->withQueryString()->links() }}
  </div>
  @endif
</div>

@push('scripts')
<script>
// Real-time updates every 30 seconds
let refreshInterval;

function refreshMyJobs() {
  fetch(window.location.href, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.text())
  .then(html => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    // Update stats
    const statIds = ['stat-pending', 'stat-in-progress', 'stat-completed', 'stat-my-active'];
    statIds.forEach(id => {
      const newStat = doc.getElementById(id);
      const currentStat = document.getElementById(id);
      if (newStat && currentStat && newStat.textContent !== currentStat.textContent) {
        currentStat.textContent = newStat.textContent;
        currentStat.classList.add('animate-pulse');
        setTimeout(() => currentStat.classList.remove('animate-pulse'), 1000);
      }
    });

    // Update table body
    const newTbody = doc.querySelector('tbody.bg-white');
    const currentTbody = document.querySelector('tbody.bg-white');
    if (newTbody && currentTbody) {
      const currentRows = currentTbody.querySelectorAll('tr').length;
      const newRows = newTbody.querySelectorAll('tr').length;

      if (currentRows !== newRows || newTbody.innerHTML !== currentTbody.innerHTML) {
        currentTbody.innerHTML = newTbody.innerHTML;

        if (newRows > currentRows && currentRows > 0) {
          showNotification('มีงานใหม่เข้ามา!');
        }
      }
    }
  })
  .catch(error => console.error('Error refreshing:', error));
}

function showNotification(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-bounce';
  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.remove('animate-bounce');
    toast.classList.add('opacity-0', 'transition-opacity');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

refreshInterval = setInterval(refreshMyJobs, 30000);

window.addEventListener('beforeunload', () => {
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>
@endpush
@endsection
