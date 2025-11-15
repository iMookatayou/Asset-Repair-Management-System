@extends('layouts.app')
@section('title', 'Repair Queue — Pending')

@section('content')
@php
  use Illuminate\Support\Str;
  /** @var \Illuminate\Pagination\LengthAwarePaginator $list */

  $q      = request('q');
  $status = request('status'); // null|pending|in_progress|completed

  // สรุปตัวเลข (กัน null → 0 เสมอ)
  $stats   = is_array($stats ?? null) ? $stats : [];
  $total   = (int) ($stats['total']       ?? $list->total());
  $pending = (int) ($stats['pending']     ?? 0);
  $doing   = (int) ($stats['in_progress'] ?? 0);
  $done    = (int) ($stats['completed']   ?? 0);

  // ป้ายระดับความเร่งด่วน (โทนเรียบ + เส้นกรอบ)
  $priBadge = function (?string $p) {
    $p = strtolower((string)$p);
    return match (true) {
      in_array($p, ['urgent','high']) => 'ring-1 ring-rose-300 text-rose-800 bg-white',
      $p === 'medium'                 => 'ring-1 ring-amber-300 text-amber-800 bg-white',
      default                         => 'ring-1 ring-emerald-300 text-emerald-800 bg-white',
    };
  };
  $showActions = auth()->user()?->can('tech-only') ?? false;
@endphp

<div class="pt-3 md:pt-4"></div>

<div class="w-full px-4 md:px-6 lg:px-8 flex flex-col gap-5">
  <div class="rounded-lg border border-zinc-300 bg-white" id="queueTableWrapper">
    <div class="px-5 py-4">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="grid h-9 w-9 place-items-center rounded-md bg-zinc-100 text-zinc-700 ring-1 ring-inset ring-zinc-300">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 8l-2 2 9 9 2-2-9-9zM16 3l5 5-3 3-5-5 3-3z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-[17px] font-semibold text-zinc-900">Pending Repair Requests</h1>
            <p class="text-[13px] text-zinc-600">รอรับเข้าคิว / มอบหมาย / เริ่มทำงาน</p>
          </div>
        </div>

        @can('view-my-jobs')
          <div class="flex items-center">
            <a href="{{ route('repairs.my_jobs') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-indigo-700 bg-indigo-700 px-4 py-2 text-[13px] font-medium text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 2h6a2 2 0 0 1 2 2v2h-2V4H9v2H7V4a2 2 0 0 1 2-2zm3 8h4m-8 0h.01M9 16h6m-8 0h.01M5 8h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2z"/>
              </svg>
              My Jobs
            </a>
          </div>
        @endcan
      </div>

      <div class="mt-4 h-px bg-zinc-200"></div>

      <form method="GET" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12">
        <div class="md:col-span-4">
          <label for="status" class="mb-1 block text-[12px] text-zinc-600">สถานะ</label>
          <select id="status" name="status"
                  class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                  onchange="this.form.submit()">
            <option value="" @selected(empty($status))>ทั้งหมด</option>
            <option value="pending" @selected($status==='pending')>รอคิว</option>
            <option value="in_progress" @selected($status==='in_progress')>ระหว่างดำเนินการ</option>
            <option value="completed" @selected($status==='completed')>เสร็จสิ้น</option>
          </select>
        </div>

        <div class="md:col-span-8">
          <label for="q" class="mb-1 block text-[12px] text-zinc-600">คำค้นหา</label>
          <div class="flex gap-2">
            <div class="relative grow">
              <input id="q" name="q" value="{{ $q }}"
                     placeholder="เช่น ชื่องาน, รายละเอียด, ผู้แจ้ง, หมายเลขทรัพย์สิน"
                     class="w-full rounded-md border border-zinc-300 pl-9 pr-3 py-2 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </span>
            </div>
            <button type="submit"
                    class="rounded-md border border-emerald-700 bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800">
              ค้นหา
            </button>
            <a href="{{ request()->url() }}"
               class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50">
              ล้าง
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="rounded-lg border border-zinc-300 bg-white">
    <div class="relative overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-zinc-50">
          <tr class="text-zinc-700 border-b border-zinc-200">
            <th class="p-3 text-left font-medium w-[40%]">เรื่อง</th>
            <th class="p-3 text-left font-medium w-[20%]">ทรัพย์สิน</th>
            <th class="p-3 text-left font-medium w-[18%]">ผู้แจ้ง</th>
            <th class="p-3 text-left font-medium w-[14%]">วันที่แจ้ง</th>
            @if($showActions)
              <th class="p-3 text-center font-medium whitespace-nowrap min-w-[200px]">การดำเนินการ</th>
            @endif
          </tr>
        </thead>

        <tbody>
        @forelse($list as $r)
          @php $isJust = isset($just) && (int)$just === (int)$r->id; @endphp
          <tr class="queue-row align-top border-b last:border-0 transition-colors duration-700 {{ $isJust ? 'is-just' : 'hover:bg-zinc-50' }}" data-row-id="{{ $r->id }}">
            <td class="p-3">
              <a href="{{ route('maintenance.requests.show', $r) }}"
                 class="block max-w-full truncate font-medium text-zinc-900 hover:underline">
                {{ Str::limit($r->title, 90) }}
              </a>
              @if(!empty($r->description))
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">
                  {{ Str::limit($r->description, 140) }}
                </p>
              @endif
              <div class="mt-2 flex flex-wrap gap-2">
                @if(!empty($r->priority))
                  <span class="rounded-full bg-white px-2 py-1 text-[11px] {{ $priBadge($r->priority) }}">
                    {{ ucfirst(strtolower($r->priority)) }}
                  </span>
                @endif
                @if(!empty($r->category))
                  <span class="rounded-full bg-white px-2 py-1 text-[11px] ring-1 ring-zinc-300 text-zinc-700">
                    {{ $r->category }}
                  </span>
                @endif
              </div>
            </td>

            <td class="p-3">
              <div class="font-medium text-zinc-900">#{{ $r->asset_id }}</div>
              <div class="max-w-full truncate text-xs text-zinc-600">{{ $r->asset->name ?? '—' }}</div>
              @if(!empty($r->asset?->location))
                <div class="mt-0.5 max-w-full truncate text-[11px] text-zinc-500">{{ $r->asset->location }}</div>
              @endif
            </td>

            <td class="p-3">
              <div class="max-w-full truncate text-zinc-900">{{ $r->reporter->name ?? '—' }}</div>
              @php
                $deptLabel = $r->reporter->department_name
                              ?? $r->reporter->department
                              ?? null;
              @endphp
              @if(!empty($deptLabel))
                <div class="max-w-full truncate text-[11px] text-zinc-500">{{ $deptLabel }}</div>
              @endif
            </td>

            <td class="p-3">
              <div class="font-medium text-zinc-800">
                {{ optional($r->request_date)->format('Y-m-d H:i') ?? '—' }}
              </div>
              @if($r->request_date)
                <div class="text-[11px] text-zinc-500">{{ $r->request_date->diffForHumans() }}</div>
              @endif
            </td>

            @if($showActions)
              <td class="p-3 text-center align-middle">
                @can('tech-only')
                  <div class="relative inline-block text-left">
                    <details class="group inline-block queue-actions">
                      <summary class="flex cursor-pointer list-none">
                        <span class="inline-flex items-center gap-1 rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-800 hover:bg-zinc-50">
                          ดำเนินการ ▾
                        </span>
                      </summary>
                      <div class="dropdown-like absolute right-0 z-10 mt-1 w-44 rounded-md border border-zinc-300 bg-white p-2 text-left shadow-sm">
                        <form method="POST" action="{{ route('maintenance.requests.transition', $r) }}" class="block">
                          @csrf <input type="hidden" name="action" value="accept">
                          <button class="w-full rounded-md px-3 py-1.5 text-left text-xs text-zinc-800 hover:bg-zinc-100">รับงาน</button>
                        </form>
                        <form method="POST" action="{{ route('maintenance.requests.transition', $r) }}" class="mt-1 block">
                          @csrf
                          <input type="hidden" name="action" value="assign">
                          <input type="hidden" name="technician_id" value="{{ auth()->id() }}">
                          <button class="w-full rounded-md px-3 py-1.5 text-left text-xs text-zinc-800 hover:bg-zinc-100">มอบหมายให้ฉัน</button>
                        </form>
                        <form method="POST" action="{{ route('maintenance.requests.transition', $r) }}" class="mt-1 block">
                          @csrf <input type="hidden" name="action" value="start">
                          <button class="w-full rounded-md px-3 py-1.5 text-left text-xs text-zinc-800 hover:bg-zinc-100">เริ่มงาน</button>
                        </form>
                      </div>
                    </details>
                  </div>
                @endcan
              </td>
            @endif
          </tr>
        @empty
          <tr>
            <td colspan="{{ $showActions ? 5 : 4 }}" class="p-12 text-center text-zinc-600">ไม่พบรายการที่รอดำเนินการ</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="-mt-3">{{ $list->withQueryString()->links() }}</div>

</div>
@endsection

@push('scripts')
<script>
  // Enhance <details> dropdown so it never gets clipped by parent containers
  (function(){
    function place(menu, anchorRect){
      const vw = window.innerWidth, vh = window.innerHeight;
      menu.style.position = 'fixed';
      menu.style.left = 'auto';
      menu.style.right = (vw - anchorRect.right) + 'px';
      // default show below
      let top = anchorRect.bottom + 4;
      menu.style.top = top + 'px';
      // If overflow bottom, flip to top
      requestAnimationFrame(()=>{
        const mh = menu.getBoundingClientRect().height;
        if (top + mh > vh - 8){
          top = Math.max(8, anchorRect.top - mh - 4);
          menu.style.top = top + 'px';
        }
      });
      menu.style.zIndex = 10050;
    }

    function bindOne(details){
      const summary = details.querySelector('summary');
      const menu = details.querySelector('.dropdown-like');
      if (!summary || !menu) return;

      function close(){ details.removeAttribute('open'); }

      details.addEventListener('toggle', () => {
        if (details.hasAttribute('open')){
          const rect = summary.getBoundingClientRect();
          place(menu, rect);
          setTimeout(()=>{
            // click outside to close
            function onDoc(e){ if (!details.contains(e.target)) { close(); document.removeEventListener('click', onDoc); } }
            document.addEventListener('click', onDoc);
            function onEsc(ev){ if (ev.key === 'Escape'){ close(); document.removeEventListener('keydown', onEsc); } }
            document.addEventListener('keydown', onEsc);
          }, 0);
        }
      });
    }

    document.querySelectorAll('details.queue-actions').forEach(bindOne);

    // Highlight effect fade-out (animate-pulse-once defined inline here)
    // Highlight style (static so it always loads before repaint)
    if (!document.getElementById('queue-highlight-style')) {
      const style = document.createElement('style');
      style.id = 'queue-highlight-style';
      style.textContent = `
        @keyframes queueGlow { 0%{box-shadow:0 0 0 0 rgba(16,185,129,.55);} 60%{box-shadow:0 0 0 14px rgba(16,185,129,0);} 100%{box-shadow:0 0 0 0 rgba(16,185,129,0);} }
        tr.queue-row.is-just { position:relative; background:#ecfdf5; }
        tr.queue-row.is-just::after { content:""; position:absolute; inset:0; border:2px solid #10b981; border-radius:4px; pointer-events:none; animation:queueGlow 2.2s cubic-bezier(.4,0,.2,1); }
      `;
      document.head.appendChild(style);
    }
    // Auto-scroll the highlighted row into view if it's below fold
    const justRow = document.querySelector('tr.queue-row.is-just');
    if (justRow) {
      const rect = justRow.getBoundingClientRect();
      if (rect.bottom > window.innerHeight || rect.top < 0) {
        justRow.scrollIntoView({behavior:'smooth', block:'center'});
      }
    }
  })();
</script>
@endpush
