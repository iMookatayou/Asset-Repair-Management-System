@extends('layouts.app')
@section('title','Manage Users')

@php
  $roles   = $roles   ?? \App\Models\User::availableRoles();
  $filters = $filters ?? ['s'=>'','role'=>'','department'=>''];

  $CTL = 'h-10 text-sm rounded-lg border border-zinc-300 px-3 focus:border-emerald-500 focus:ring-emerald-500';
  $SEL = $CTL . ' pr-9';
  $BTN = 'h-10 text-xs md:text-sm inline-flex items-center gap-2 rounded-lg px-3 md:px-3.5 font-medium leading-5
          focus:outline-none focus:ring-2 whitespace-nowrap';
@endphp

@section('content')
  <div class="pt-3 md:pt-4"></div>

  {{-- Sticky header + filter --}}
  <div class="sticky top-[6rem] z-20 bg-slate-50/90 backdrop-blur mb-4">
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
      <div class="px-4 py-3">
        <div class="flex flex-col gap-3">
          {{-- Top row: Icon + Title + Button --}}
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 ring-1 ring-inset ring-indigo-200">
                <svg class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="4" y="3" width="16" height="18" rx="2"/>
                  <path d="M8 7h8M8 11h8M8 15h5"/>
                </svg>
              </div>
              <div>
                <h1 class="text-lg font-semibold text-slate-800">Manage User</h1>
                <p class="text-sm text-slate-500">เรียกดู กรอง และจัดการผู้ใช้</p>
              </div>
            </div>

            <div class="flex shrink-0 items-center">
              <a href="{{ route('admin.users.create') }}"
                 class="{{ $BTN }} min-w-[108px] justify-center bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14"/>
                </svg>
                <span class="hidden sm:inline">สร้างผู้ใช้ใหม่</span>
                <span class="sm:hidden">สร้าง</span>
              </a>
            </div>
          </div>

          {{-- Search/Filter Form --}}
          <div class="pt-3 border-t border-zinc-200">
            <form method="GET" class="grid grid-cols-1 gap-2 md:grid-cols-5">
              <input name="s" value="{{ $filters['s'] }}" placeholder="ค้นหาชื่อ/อีเมล/หน่วยงาน"
                     class="w-full {{ $CTL }}" />

              <select name="role" class="w-full {{ $SEL }}">
                <option value="">บทบาททั้งหมด</option>
                @foreach ($roles as $r)
                  <option value="{{ $r }}" @selected($filters['role']===$r)>{{ \App\Models\User::roleLabels()[$r] ?? ucfirst($r) }}</option>
                @endforeach
              </select>

              <input name="department" value="{{ $filters['department'] }}" placeholder="หน่วยงาน"
                     class="w-full {{ $CTL }}" />

              <div class="col-span-1 flex items-center gap-2">
                <button class="{{ $BTN }} min-w-[96px] justify-center bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500" title="Filter">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 5h18M6 12h12M10 19h4"/>
                  </svg>
                  <span class="hidden md:inline">กรอง</span>
                  <span class="md:hidden">ค้นหา</span>
                </button>

                <a href="{{ route('admin.users.index') }}"
                   class="{{ $BTN }} min-w-[88px] justify-center border border-zinc-300 text-zinc-700 hover:bg-zinc-50 focus:ring-emerald-500"
                   title="Reset">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 3-6.7M3 5v5h5"/>
                  </svg>
                  <span class="hidden md:inline">ล้างค่า</span>
                  <span class="md:hidden">ล้าง</span>
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('status'))
    @php session()->flash('toast', \App\Support\Toast::success(session('status'))); @endphp
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

  <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white">
      <table class="min-w-full divide-y divide-zinc-200">
        <thead class="bg-zinc-50 text-left text-xs font-medium text-zinc-700">
          <tr>
            <th class="px-3 py-2">ชื่อ</th>
            <th class="px-3 py-2">อีเมล</th>
            <th class="px-3 py-2 hidden lg:table-cell">หน่วยงาน</th>
            <th class="px-3 py-2 hidden md:table-cell">บทบาท</th>
            <th class="px-3 py-2 hidden xl:table-cell">สร้างเมื่อ</th>
            <th class="px-3 py-2 text-center min-w-[180px]">การดำเนินการ</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 text-sm">
          @forelse ($users as $u)
            <tr>
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
                @php $isSup = method_exists($u,'isSupervisor') ? $u->isSupervisor() : false; @endphp
                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] leading-5 {{ $isSup ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-zinc-50 text-zinc-700 border-zinc-300' }}">
                  {{ $u->role_label ?? ucfirst($u->role) }}
                </span>
              </td>
              <td class="px-3 py-2 hidden xl:table-cell text-zinc-700 whitespace-nowrap">
                {{ $u->created_at?->format('Y-m-d H:i') }}
              </td>
              <td class="px-3 py-2 text-center align-middle whitespace-nowrap">
                <div class="h-full flex items-center justify-center gap-1.5">
                  <a href="{{ route('admin.users.edit', $u) }}"
                     class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 px-2.5 md:px-3 py-1.5 text-[11px] md:text-xs font-medium text-emerald-700 hover:bg-emerald-50 whitespace-nowrap min-w-[74px] justify-center">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                    </svg>
                    <span class="hidden sm:inline">แก้ไข</span>
                    <span class="sm:hidden">แก้ไข</span>
                  </a>
                  @if($u->id !== auth()->id())
                    <button type="button"
                      class="inline-flex items-center gap-1.5 rounded-md border border-rose-300 px-2.5 md:px-3 py-1.5 text-[11px] md:text-xs font-medium text-rose-600 hover:bg-rose-50 whitespace-nowrap min-w-[74px] justify-center"
                      onclick="return window.confirmDeleteUser('{{ route('admin.users.destroy', $u) }}');">
                      <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/>
                      </svg>
                      <span class="hidden sm:inline">ลบ</span>
                      <span class="sm:hidden">ลบ</span>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="px-3 py-6 text-center text-zinc-500">ไม่พบผู้ใช้</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

  {{-- Hidden global delete form & script (outside bulk form to avoid nested forms) --}}
  <form id="delete-user-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
  </form>
  <script>
    window.confirmDeleteUser = function(url){
      if(!confirm('ยืนยันการลบผู้ใช้นี้?')) return false;
      const f = document.getElementById('delete-user-form');
      if(!f) return true;
      f.action = url;
      f.submit();
      return false; // prevent default link/button behavior
    };
  </script>

  <div class="mt-3">{{ $users->links() }}</div>
@endsection
