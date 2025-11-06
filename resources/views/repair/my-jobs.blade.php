{{-- resources/views/repairs/my_jobs.blade.php --}}
@extends('layouts.app')
@section('title','My Jobs')

@section('content')
@php
  use Illuminate\Support\Str;

  $q        = request('q');
  $status   = request('status'); // null|pending|accepted|in_progress|on_hold|resolved|closed|cancelled
  $priority = request('priority'); // low|medium|high|urgent (optional)
  $sort     = request('sort','updated_desc'); // updated_desc|updated_asc|created_desc|created_asc
  $perPage  = (int) request('per_page', 20);

  // tokens
  $btnPrimary = 'inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2';
  $btnGhost   = 'inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2';
  $chip       = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1';

  $statusClasses = [
    'pending'     => 'text-amber-700 bg-amber-50 ring-amber-200',
    'accepted'    => 'text-sky-700 bg-sky-50 ring-sky-200',
    'in_progress' => 'text-indigo-700 bg-indigo-50 ring-indigo-200',
    'on_hold'     => 'text-slate-700 bg-slate-50 ring-slate-200',
    'resolved'    => 'text-emerald-700 bg-emerald-50 ring-emerald-200',
    'closed'      => 'text-slate-800 bg-slate-200 ring-slate-200',
    'cancelled'   => 'text-slate-600 bg-white ring-slate-300',
  ];
  $priorityClasses = [
    'low'     => 'text-emerald-700 bg-emerald-50 ring-emerald-200',
    'medium'  => 'text-sky-700 bg-sky-50 ring-sky-200',
    'high'    => 'text-amber-700 bg-amber-50 ring-amber-200',
    'urgent'  => 'text-rose-700 bg-rose-50 ring-rose-200',
  ];

  $human = fn($s) => Str::of($s)->replace('_',' ')->title();
@endphp

<div class="mx-auto max-w-7xl py-6 space-y-6">

  {{-- ===== Section header ===== --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between px-5 py-4">
      <div class="flex items-center gap-3">
        <div class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-700">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h10M4 14h16M4 18h10"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-semibold text-slate-900">My Jobs</h2>
          <p class="text-sm text-slate-500">Browse, filter and manage repair jobs assigned to you</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('maintenance.requests.index') }}" class="{{ $btnGhost }}">All Requests</a>
        <a href="{{ route('maintenance.requests.create') }}" class="hidden sm:inline-flex {{ $btnPrimary }}">+ New Request</a>
      </div>
    </div>

    {{-- ===== Filters row (moved all controls to TOP) ===== --}}
    <div class="border-t border-slate-200 px-5 py-4">
      <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 xl:grid-cols-8">
        {{-- keep other query params --}}
        @foreach(request()->except(['q','status','priority','sort','per_page','page']) as $k => $v)
          <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach

        {{-- Search (wider to avoid text squeezing) --}}
        <div class="lg:col-span-2 xl:col-span-3">
          <label for="q" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
          <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="m21 21-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <input id="q" name="q" value="{{ $q }}" placeholder="Search subject, asset, reporter…"
                   class="w-full rounded-xl border border-slate-300 bg-white px-9 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500"/>
          </div>
        </div>

        {{-- Status --}}
        <div class="lg:col-span-1">
          <label for="status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
          <select id="status" name="status"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            <option value="" @selected(!$status)>All Status</option>
            @foreach(['in_progress','resolved','pending','accepted','on_hold','closed','cancelled'] as $s)
              <option value="{{ $s }}" @selected($status===$s)>{{ $human($s) }}</option>
            @endforeach
          </select>
        </div>

        {{-- Priority --}}
        <div class="lg:col-span-1">
          <label for="priority" class="mb-1 block text-xs font-medium text-slate-600">Priority</label>
          <select id="priority" name="priority"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            <option value="" @selected(!$priority)>All Priority</option>
            @foreach(['low','medium','high','urgent'] as $p)
              <option value="{{ $p }}" @selected($priority===$p)>{{ ucfirst($p) }}</option>
            @endforeach
          </select>
        </div>

        {{-- Sort (moved from bottom) --}}
        <div class="lg:col-span-1">
          <label for="sort" class="mb-1 block text-xs font-medium text-slate-600">Sort</label>
          <select id="sort" name="sort"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            <option value="updated_desc" @selected($sort==='updated_desc')>Updated</option>
            <option value="updated_asc"  @selected($sort==='updated_asc')>Updated</option>
            <option value="created_desc" @selected($sort==='created_desc')>Created</option>
            <option value="created_asc"  @selected($sort==='created_asc')>Created</option>
          </select>
        </div>

        {{-- Per page (moved from bottom) --}}
        <div class="lg:col-span-1">
          <label for="per_page" class="mb-1 block text-xs font-medium text-slate-600">Per page</label>
          <select id="per_page" name="per_page"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
            @foreach([10,20,50,100] as $n)
              <option value="{{ $n }}" @selected($perPage===$n)>{{ $n }}/page</option>
            @endforeach
          </select>
        </div>

        {{-- Actions --}}
        <div class="flex items-end gap-2 lg:col-span-1">
          <button type="submit" class="w-full sm:w-auto {{ $btnPrimary }}">Apply</button>
          @if($q || $status || $priority || $sort!=='updated_desc' || $perPage!==20)
            <a href="{{ route('repairs.my_jobs') }}" class="w-full sm:w-auto {{ $btnGhost }}">Reset</a>
          @endif
        </div>
      </form>
    </div>

    {{-- ===== Table ===== --}}
    <div class="border-t border-slate-200">
      <div class="overflow-x-auto">
        <table class="w-full table-auto text-left">
          <thead class="bg-slate-50">
            <tr class="text-xs uppercase tracking-wide text-slate-500">
              <th class="px-5 py-3 w-[6%]">#</th>
              <th class="px-5 py-3 w-[34%]">Subject</th>
              <th class="px-5 py-3 w-[16%]">Asset</th>
              <th class="px-5 py-3 w-[12%]">Priority</th>
              <th class="px-5 py-3 w-[12%]">Status</th>
              <th class="px-5 py-3 w-[12%]">Updated</th>
              <th class="px-5 py-3 w-[6%]"><span class="sr-only">View</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-sm text-slate-800">
          @forelse($list as $r)
            @php
              $stCls = $statusClasses[$r->status] ?? 'text-slate-700 bg-slate-50 ring-slate-200';
              $prio  = strtolower($r->priority ?? 'medium');
              $prCls = $priorityClasses[$prio] ?? $priorityClasses['medium'];
            @endphp
            <tr class="hover:bg-slate-50 align-top">
              <td class="px-5 py-3 text-slate-500">#{{ $r->id }}</td>

              <td class="px-5 py-3">
                <a href="{{ route('maintenance.requests.show', $r) }}"
                   class="font-medium text-slate-900 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded">
                  <span class="line-clamp-2">{{ $r->title }}</span>
                </a>
                <div class="mt-0.5 text-xs text-slate-500 line-clamp-2">
                  {{ Str::limit($r->description ?? '', 140) }}
                </div>
              </td>

              <td class="px-5 py-3 whitespace-nowrap">
                #{{ $r->asset_id }} — <span class="truncate inline-block max-w-[14rem] align-bottom">{{ $r->asset->name ?? '-' }}</span>
                @if($r->location ?? null)
                  <div class="text-xs text-slate-500">{{ $r->location }}</div>
                @endif
              </td>

              <td class="px-5 py-3">
                <span class="{{ $chip }} {{ $prCls }}">{{ ucfirst($prio) }}</span>
              </td>

              <td class="px-5 py-3">
                <span class="{{ $chip }} {{ $stCls }}">{{ $human($r->status) }}</span>
              </td>

              <td class="px-5 py-3 whitespace-nowrap text-slate-600">
                {{ optional($r->updated_at)->format('Y-m-d H:i') }}
              </td>

              <td class="px-5 py-3 text-right">
                <a href="{{ route('maintenance.requests.show', $r) }}"
                   class="text-emerald-700 hover:text-emerald-800 hover:underline">View</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-10">
                <div class="text-center text-slate-500">
                  <div class="mx-auto mb-3 h-10 w-10 rounded-full border border-dashed border-slate-300"></div>
                  <div class="mb-2 text-sm">No jobs found.</div>
                  <a href="{{ route('repairs.my_jobs') }}" class="{{ $btnGhost }}">Clear filters</a>
                </div>
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination only (controls moved to top already) --}}
      <div class="px-5 py-3">
        <div class="flex justify-center">
          {{ $list->withQueryString()->links() }}
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Mobile cards ===== --}}
  <div class="grid grid-cols-1 gap-3 md:hidden">
    @forelse($list as $r)
      @php
        $stCls = $statusClasses[$r->status] ?? 'text-slate-700 bg-slate-50 ring-slate-200';
        $prio  = strtolower($r->priority ?? 'medium');
        $prCls = $priorityClasses[$prio] ?? $priorityClasses['medium'];
      @endphp
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-xs text-slate-500">#{{ $r->id }}</div>
              <a href="{{ route('maintenance.requests.show', $r) }}"
                 class="block font-semibold text-slate-900 hover:underline">
                <span class="line-clamp-2">{{ $r->title }}</span>
              </a>
              <p class="mt-1 text-sm text-slate-600 line-clamp-3">{{ Str::limit($r->description ?? '', 140) }}</p>
            </div>
            <span class="{{ $chip }} {{ $stCls }}">{{ $human($r->status) }}</span>
          </div>

          <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
            <div class="text-slate-500">Priority</div>
            <div><span class="{{ $chip }} {{ $prCls }}">{{ ucfirst($prio) }}</span></div>
            <div class="text-slate-500">Asset</div>
            <div class="text-slate-800 truncate">#{{ $r->asset_id }} — {{ $r->asset->name ?? '-' }}</div>
            <div class="text-slate-500">Updated</div>
            <div class="text-slate-800">{{ optional($r->updated_at)->format('Y-m-d H:i') }}</div>
          </div>

          <div class="mt-4">
            <a href="{{ route('maintenance.requests.show', $r) }}" class="w-full {{ $btnGhost }} justify-center">View</a>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
        <div class="mx-auto mb-3 h-10 w-10 rounded-full border border-dashed border-slate-300"></div>
        <p class="text-sm text-slate-600 mb-3">No jobs found</p>
        <a href="{{ route('repairs.my_jobs') }}" class="{{ $btnGhost }}">Clear filters</a>
      </div>
    @endforelse

    <div class="flex justify-center">
      {{ $list->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
