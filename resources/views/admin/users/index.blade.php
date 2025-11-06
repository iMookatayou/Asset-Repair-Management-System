{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')
@section('title','Manage Users')

@php
  $roles   = $roles   ?? ['admin','technician','staff'];
  $filters = $filters ?? ['s'=>'','role'=>'','department'=>''];

  $CTL = 'h-9 text-sm rounded-lg border border-zinc-300 px-3 focus:border-emerald-500 focus:ring-emerald-500';
  $SEL = $CTL . ' pr-9';
  $BTN = 'h-9 text-xs md:text-sm inline-flex items-center gap-2 rounded-lg px-2.5 md:px-3 font-medium focus:outline-none focus:ring-2 whitespace-nowrap';
@endphp

@section('content')
  {{-- ===== Header (เรียบหรู ไม่มีเส้นสี) ===== --}}
  <div class="mb-5 rounded-2xl border border-zinc-200 bg-white shadow-sm">
    <div class="flex items-start justify-between gap-3 px-5 py-4">
      <div class="flex items-start gap-3">
        {{-- Icon --}}
        <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 ring-1 ring-inset ring-indigo-200">
          <svg class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="4" y="3" width="16" height="18" rx="2"/>
            <path d="M8 7h8M8 11h8M8 15h5"/>
          </svg>
        </div>
        <div>
          <h1 class="text-lg font-semibold text-slate-800">Users</h1>
          <p class="text-sm text-slate-500">Browse, filter and manage system users</p>
        </div>
      </div>

      <div class="flex shrink-0 items-center">
        <a href="{{ route('admin.users.create') }}"
           class="{{ $BTN }} bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
          </svg>
          <span class="hidden sm:inline">New User</span>
          <span class="sm:hidden">New</span>
        </a>
      </div>
    </div>
  </div>

  {{-- Flash & Errors --}}
  @if (session('status'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700 text-sm">
      {{ session('status') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700 text-sm">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ===== Filters ===== --}}
  <form method="GET" class="mb-4 grid grid-cols-1 gap-2 md:grid-cols-5">
    <input name="s" value="{{ $filters['s'] }}" placeholder="Search name/email/department"
           class="w-full {{ $CTL }}" />

    <select name="role" class="w-full {{ $SEL }}">
      <option value="">All roles</option>
      @foreach ($roles as $r)
        <option value="{{ $r }}" @selected($filters['role']===$r)>{{ ucfirst($r) }}</option>
      @endforeach
    </select>

    <input name="department" value="{{ $filters['department'] }}" placeholder="Department"
           class="w-full {{ $CTL }}" />

    <div class="col-span-1 flex items-center gap-2">
      <button class="{{ $BTN }} bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500" title="Filter">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 5h18M6 12h12M10 19h4"/>
        </svg>
        <span class="hidden md:inline">Filter</span>
        <span class="md:hidden">Go</span>
      </button>

      <a href="{{ route('admin.users.index') }}"
         class="{{ $BTN }} border border-zinc-300 text-zinc-700 hover:bg-zinc-50 focus:ring-emerald-500"
         title="Reset">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 12a9 9 0 1 0 3-6.7M3 5v5h5"/>
        </svg>
        <span class="hidden md:inline">Reset</span>
        <span class="md:hidden">Rst</span>
      </a>
    </div>
  </form>

  {{-- ===== Bulk Actions ===== --}}
  <form method="POST" action="{{ route('admin.users.bulk') }}">
    @csrf
    <div class="mb-2 flex flex-wrap items-center gap-2">
      <select name="action" class="{{ $SEL }} py-2">
        <option value="" selected>Action</option>
        <option value="change_role">Role</option>
        <option value="delete">Delete</option>
      </select>

      <select name="role" class="{{ $SEL }} py-2">
        <option value="">-- role --</option>
        @foreach ($roles as $r)
          <option value="{{ $r }}">{{ ucfirst($r) }}</option>
        @endforeach
      </select>

      <button type="submit"
        class="{{ $BTN }} bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500"
        onclick="return confirm('Confirm bulk action?');">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        <span class="hidden md:inline">Apply</span>
        <span class="md:hidden">Go</span>
      </button>
    </div>

    {{-- ===== Table ===== --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white">
      <table class="min-w-full divide-y divide-zinc-200">
        <thead class="bg-zinc-50 text-left text-xs font-medium text-zinc-700">
          <tr>
            <th class="px-3 py-2"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"></th>
            <th class="px-3 py-2">Name</th>
            <th class="px-3 py-2">Email</th>
            <th class="px-3 py-2 hidden lg:table-cell">Department</th>
            <th class="px-3 py-2 hidden md:table-cell">Role</th>
            <th class="px-3 py-2 hidden xl:table-cell">Created</th>
            <th class="px-3 py-2 text-right">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 text-sm">
          @forelse ($users as $u)
            <tr>
              <td class="px-3 py-2">
                @if($u->id !== auth()->id())
                  <input type="checkbox" class="row-check" name="ids[]" value="{{ $u->id }}">
                @endif
              </td>
              <td class="px-3 py-2">
                <div class="flex items-center gap-2">
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-xs font-semibold text-white">
                    {{ strtoupper(mb_substr($u->name,0,1)) }}
                  </div>
                  <div>
                    <div class="truncate max-w-[180px] font-medium">{{ $u->name }}</div>
                    <div class="text-xs text-zinc-500">#{{ $u->id }}</div>
                  </div>
                </div>
              </td>
              <td class="px-3 py-2 truncate max-w-[240px]">{{ $u->email }}</td>
              <td class="px-3 py-2 hidden lg:table-cell truncate max-w-[160px]">{{ $u->department ?: '-' }}</td>
              <td class="px-3 py-2 hidden md:table-cell">
                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] leading-5 {{ $u->role==='admin' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-zinc-50 text-zinc-700 border-zinc-300' }}">
                  {{ ucfirst($u->role) }}
                </span>
              </td>
              <td class="px-3 py-2 hidden xl:table-cell text-zinc-700 whitespace-nowrap">
                {{ $u->created_at?->format('Y-m-d H:i') }}
              </td>
              <td class="px-3 py-2">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.users.edit', $u) }}"
                     class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 px-2 md:px-2.5 py-1.5 text-[11px] md:text-xs font-medium text-emerald-700 hover:bg-emerald-50 whitespace-nowrap">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                    </svg>
                    <span class="hidden sm:inline">Edit</span>
                  </a>
                  @if($u->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Delete this user?');">
                      @csrf @method('DELETE')
                      <button
                        class="inline-flex items-center gap-1.5 rounded-md border border-rose-300 px-2 md:px-2.5 py-1.5 text-[11px] md:text-xs font-medium text-rose-600 hover:bg-rose-50 whitespace-nowrap"
                        type="submit">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/>
                        </svg>
                        <span class="hidden sm:inline">Delete</span>
                        <span class="sm:hidden">Del</span>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-3 py-6 text-center text-zinc-500">No users found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </form>

  <div class="mt-4">{{ $users->links() }}</div>
@endsection
