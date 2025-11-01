@extends('layouts.app')

@section('title', 'Maintenance Requests')

@section('page-header')
  <div class="flex items-center justify-between">
    <a href="{{ route('maintenance.requests.create') }}"
       class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
      + New Request
    </a>
  </div>
@endsection

@section('content')
  {{-- Filters --}}
  <form method="GET" action="{{ route('maintenance.requests.index') }}"
        class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4" role="search" aria-label="Filter maintenance requests">
    <div>
      <label for="q" class="block text-sm text-zinc-600">Search</label>
      <input id="q" type="text" name="q" value="{{ request('q') }}"
             placeholder="Search title/description…"
             class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500" />
    </div>

    <div>
      <label for="status" class="block text-sm text-zinc-600">Status</label>
      <select id="status" name="status"
              class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
        <option value="">All Status</option>
        @foreach (['pending'=>'Pending','in_progress'=>'In progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v)
          <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label for="priority" class="block text-sm text-zinc-600">Priority</label>
      <select id="priority" name="priority"
              class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
        <option value="">All Priority</option>
        @foreach (['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $k=>$v)
          <option value="{{ $k }}" @selected(request('priority') === $k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div class="flex items-end">
      <button type="submit"
              class="w-full rounded-lg border border-zinc-300 px-3 py-2 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        Apply
      </button>
    </div>
  </form>

  {{-- Table --}}
  <div class="overflow-x-auto rounded-2xl border border-zinc-200">
    <table class="min-w-full text-sm">
      <thead class="bg-zinc-50">
        <tr class="text-left">
          <th scope="col" class="p-2 font-semibold text-zinc-700">#</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Title</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Priority</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Status</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Reporter</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Technician</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700">Requested</th>
          <th scope="col" class="p-2 font-semibold text-zinc-700"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($list as $row)
          <tr class="border-t">
            <td class="p-2 align-top">{{ $row->id }}</td>

            <td class="p-2 align-top">
              <div class="font-medium text-zinc-900">{{ $row->title }}</div>
              @if($row->description)
                <div class="text-zinc-500 line-clamp-2">{{ $row->description }}</div>
              @endif
            </td>

            <td class="p-2 align-top">
              <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize
                           @class([
                             'border-emerald-200 bg-emerald-50 text-emerald-700' => $row->priority === 'low',
                             'border-sky-200 bg-sky-50 text-sky-700'             => $row->priority === 'medium',
                             'border-amber-200 bg-amber-50 text-amber-700'       => $row->priority === 'high',
                             'border-rose-200 bg-rose-50 text-rose-700'          => $row->priority === 'urgent',
                           ])">
                {{ str_replace('_',' ', $row->priority) }}
              </span>
            </td>

            <td class="p-2 align-top">
              <span class="inline-flex items-center rounded-full border px-2 py-0.5 capitalize
                           @class([
                             'border-zinc-300 bg-white text-zinc-700'            => $row->status === 'pending',
                             'border-blue-200 bg-blue-50 text-blue-700'          => $row->status === 'in_progress',
                             'border-emerald-200 bg-emerald-50 text-emerald-700' => $row->status === 'completed',
                             'border-zinc-300 bg-zinc-50 text-zinc-700'          => $row->status === 'cancelled',
                           ])">
                {{ str_replace('_',' ', $row->status) }}
              </span>
            </td>

            <td class="p-2 align-top">{{ $row->reporter->name ?? '-' }}</td>
            <td class="p-2 align-top">{{ $row->technician->name ?? '-' }}</td>

            <td class="p-2 align-top">
              @php $when = optional($row->request_date ?? $row->created_at); @endphp
              @if($when)
                <time datetime="{{ $when->toIso8601String() }}">{{ $when->format('Y-m-d H:i') }}</time>
              @else
                -
              @endif
            </td>

            <td class="p-2 align-top">
              <a href="{{ route('maintenance.requests.show', $row) }}"
                 class="text-emerald-700 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded">
                View
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="p-6 text-center text-zinc-500">No data</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $list->withQueryString()->links() }}
  </div>
@endsection
