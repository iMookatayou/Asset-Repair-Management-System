{{-- resources/views/maintenance/show.blade.php --}}
@extends('layouts.app')

@section('title', 'รายละเอียดงานซ่อม #'.$req->id)

@section('page-header')
  @php
    $statusText  = str_replace('_',' ',$req->status);
    $statusTone  = match($req->status){
      'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
      'canceled'  => 'bg-rose-50 text-rose-700 border-rose-200',
      'assigned','in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
      default     => 'bg-sky-50 text-sky-700 border-sky-200',
    };
    $prio = strtolower((string)$req->priority);
    $prioTone = match($prio){
      'high'   => 'bg-rose-50 text-rose-700 border-rose-200',
      'medium' => 'bg-amber-50 text-amber-700 border-amber-200',
      'low'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
      default  => 'bg-slate-50 text-slate-700 border-slate-200',
    };
  @endphp

  {{-- Header โทนอ่อน + ไอคอน + ปุ่ม Back (มาตรฐานเดียวกับหน้า Create) --}}
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div class="flex flex-col gap-2">
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            {{-- ไอคอนประแจ (inline SVG) --}}
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M21 2l-4.2 4.2a4 4 0 01-5.6 5.6L7 16l-3 1 1-3 4.2-4.2a4 4 0 015.6-5.6L21 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            รายละเอียดงานซ่อม <span id="rid">#{{ $req->id }}</span>
          </h1>

          <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium bg-slate-50 text-slate-700 border-slate-200">
              {{ $req->asset->name ?? $req->asset_id }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusTone }}">
              {{ $statusText }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {{ $prioTone }}">
              {{ $req->priority ?? 'unknown' }}
            </span>
          </div>
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-2">
          <button id="copyIdBtn" type="button"
                  class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">
            คัดลอกเลขที่
          </button>
          <button type="button" onclick="window.print()"
                  class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">
            พิมพ์
          </button>
          <a href="{{ route('maintenance.requests.index') }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
            {{-- ลูกศรย้อนกลับ --}}
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            กลับ
          </a>
        </div>
      </div>
      <p class="mt-1 text-sm text-slate-600">
        ดูรายละเอียดคำขอซ่อม ติดตามสถานะ และจัดการการดำเนินการที่เกี่ยวข้อง
      </p>
    </div>
  </div>
@endsection

@section('content')
  @php
    $requestedAt = optional($req->request_date ?? $req->created_at);
  @endphp

  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- ===== ข้อมูลพื้นฐาน ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 text-sm">
          <div>
            <div class="text-slate-500">หมายเลข</div>
            <div class="font-semibold text-slate-900">#{{ $req->id }}</div>
          </div>
          <div>
            <div class="text-slate-500">ผู้แจ้ง</div>
            <div class="font-semibold text-slate-900">{{ $req->reporter_name ?? ($req->reporter->name ?? '-') }}</div>
          </div>
          <div>
            <div class="text-slate-500">ทรัพย์สิน</div>
            <div class="font-semibold text-slate-900">{{ $req->asset->name ?? $req->asset_id }}</div>
          </div>
          <div>
            <div class="text-slate-500">สถานที่</div>
            <div class="font-semibold text-slate-900">{{ $req->location ?? '-' }}</div>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="lg:col-span-2">
            <div class="mb-1 text-slate-500">หัวข้อ</div>
            <div class="text-base font-semibold text-slate-900">{{ $req->title }}</div>

            <div class="my-4 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

            <div class="mb-1 text-slate-500">รายละเอียด</div>
            <div class="prose max-w-none text-slate-800">{{ $req->description ?: '-' }}</div>
          </div>

          <div>
            <div class="mb-2 text-slate-500">เวลา</div>
            <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
              <div class="inline-flex items-center gap-2 rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-800">
                รับคำขอ:
                @if($requestedAt)
                  <time datetime="{{ $requestedAt->toIso8601String() }}">{{ $requestedAt->format('Y-m-d H:i') }}</time>
                @else - @endif
              </div>
              @if($req->assigned_date)
                <div class="inline-flex items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800">
                  มอบหมาย:
                  <time datetime="{{ $req->assigned_date->toIso8601String() }}">{{ $req->assigned_date->format('Y-m-d H:i') }}</time>
                </div>
              @endif
              @if($req->completed_date)
                <div class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800">
                  เสร็จสิ้น:
                  <time datetime="{{ $req->completed_date->toIso8601String() }}">{{ $req->completed_date->format('Y-m-d H:i') }}</time>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== ดำเนินการ ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-6">
        <h3 class="text-base font-semibold text-slate-900">ดำเนินการ</h3>

        <form method="post"
              action="{{ route('maintenance.requests.transition', $req) }}"
              class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2" novalidate>
          @csrf

          <div>
            <label for="action" class="mb-1 block text-sm text-slate-700">การดำเนินการ</label>
            <select id="action" name="action" required
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:ring-emerald-600">
              <option value="" disabled {{ old('action') ? '' : 'selected' }}>— เลือกการดำเนินการ —</option>
              <option value="assign"   @selected(old('action')==='assign')>assign</option>
              <option value="start"    @selected(old('action')==='start')>start</option>
              <option value="complete" @selected(old('action')==='complete')>complete</option>
              <option value="cancel"   @selected(old('action')==='cancel')>cancel</option>
            </select>
          </div>

          <div id="techWrap" class="hidden">
            <label for="technician_id" class="mb-1 block text-sm text-slate-700">รหัสช่างผู้รับผิดชอบ</label>
            <input id="technician_id" type="number" inputmode="numeric" name="technician_id"
                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:ring-emerald-600"
                   value="{{ old('technician_id') }}" placeholder="เช่น 5">
          </div>

          <div class="md:col-span-2">
            <label for="remark" class="mb-1 block text-sm text-slate-700">บันทึกเพิ่มเติม</label>
            <input id="remark" type="text" name="remark"
                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:ring-emerald-600"
                   placeholder="(ไม่บังคับ)" value="{{ old('remark') }}">
          </div>

          <div class="md:col-span-2 flex flex-wrap gap-2">
            <button type="submit"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
              บันทึกการดำเนินการ
            </button>
            <a href="{{ route('maintenance.requests.index') }}"
               class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">
              กลับรายการ
            </a>
          </div>
        </form>
      </div>
    </section>

    {{-- ===== ไฟล์แนบ ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-6">
        <h3 class="text-base font-semibold text-slate-900">ไฟล์แนบ</h3>

        <form method="post" enctype="multipart/form-data"
              action="{{ route('maintenance.requests.attachments', $req) }}"
              class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3" novalidate>
          @csrf
          <div>
            <label for="att_type" class="mb-1 block text-sm text-slate-700">ประเภท</label>
            <select id="att_type" name="type"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:ring-emerald-600">
              <option value="before" @selected(old('type')==='before')>before</option>
              <option value="after"  @selected(old('type')==='after')>after</option>
              <option value="other"  @selected(old('type','other')==='other')>other</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label for="file" class="mb-1 block text-sm text-slate-700">ไฟล์</label>
            <input id="file" type="file" name="file" required
                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm hover:file:bg-slate-200 focus:border-emerald-600 focus:ring-emerald-600">
          </div>
          <div class="md:col-span-3">
            <button type="submit"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">
              อัปโหลดไฟล์
            </button>
          </div>
        </form>

        @if($req->attachments->count())
          <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
            @foreach($req->attachments as $att)
              @php
                $name = $att->original_name ?? basename($att->file_path ?? $att->path ?? '');
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $isImg = in_array($ext,['jpg','jpeg','png','gif','webp','bmp']);
                $url = isset($att->file_path) ? asset('storage/'.$att->file_path) : (isset($att->path) ? asset('storage/'.$att->path) : '#');
                $tag = $att->file_type ?? $att->type ?? 'other';
              @endphp
              <figure class="overflow-hidden rounded-lg border border-slate-200">
                @if($isImg && $url !== '#')
                  <a href="{{ $url }}" target="_blank" rel="noopener">
                    <img src="{{ $url }}" alt="{{ $name }}" class="h-36 w-full object-cover">
                  </a>
                @else
                  <div class="grid h-36 w-full place-items-center text-slate-500">
                    {{ strtoupper($ext ?: 'FILE') }}
                  </div>
                @endif
                <figcaption class="flex items-center justify-between gap-2 px-3 py-2 text-xs">
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 font-medium text-slate-700">
                    {{ $tag }}
                  </span>
                  <span class="truncate text-slate-600">{{ $name }}</span>
                  @if($url !== '#')
                    <a href="{{ $url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center rounded-md border border-sky-300 bg-sky-50 px-2 py-1 font-medium text-sky-800 hover:bg-sky-100">
                      เปิด
                    </a>
                  @endif
                </figcaption>
              </figure>
            @endforeach
          </div>
        @else
          <p class="mt-3 text-slate-500">ไม่มีไฟล์แนบ</p>
        @endif
      </div>
    </section>

    {{-- ===== ไทม์ไลน์ ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-6">
        <h3 class="text-base font-semibold text-slate-900">ประวัติการดำเนินการ</h3>

        <div class="mt-4 space-y-3">
          @forelse($req->logs as $log)
            @php
              $tone = match($log->action) {
                'complete_request' => 'bg-emerald-600',
                'cancel_request'   => 'bg-rose-600',
                'assign_technician','start_request' => 'bg-amber-600',
                default => 'bg-slate-400'
              };
            @endphp
            <article class="relative border-l-2 border-slate-200 pl-6">
              <span class="absolute -left-1.5 top-2 inline-block h-3 w-3 rounded-full {{ $tone }}"></span>
              <header class="flex flex-wrap items-center gap-2 text-sm">
                <strong>#{{ $log->id }}</strong>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                  {{ $log->action }}
                </span>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                  <time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('Y-m-d H:i') }}</time>
                </span>
                @if($log->user_id)
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                    โดย {{ $log->user_id }}
                  </span>
                @endif
              </header>
              @if($log->note)
                <p class="mt-1 text-slate-700">{{ $log->note }}</p>
              @endif
            </article>
          @empty
            <p class="text-slate-500">ยังไม่มีบันทึก</p>
          @endforelse
        </div>
      </div>
    </section>

  </div>
@endsection

@push('scripts')
<script>
  // toggle technician input visibility/required by action
  (function(){
    const sel = document.getElementById('action');
    const wrap = document.getElementById('techWrap');
    const input = document.getElementById('technician_id');
    function sync(){
      const show = sel && sel.value === 'assign';
      if(!wrap) return;
      wrap.classList.toggle('hidden', !show);
      if(input){
        input.required = !!show;
        if(!show) input.value = '';
      }
    }
    sel && sel.addEventListener('change', sync);
    sync();
  })();

  // copy id
  (function(){
    const btn = document.getElementById('copyIdBtn');
    btn?.addEventListener('click', async () => {
      const idText = (document.getElementById('rid')?.textContent || '{{ $req->id }}').replace('#','');
      try{
        await navigator.clipboard.writeText(idText);
        const old = btn.textContent;
        btn.classList.add('bg-slate-900','text-white','border-slate-900');
        btn.textContent = 'คัดลอกแล้ว';
        setTimeout(()=> {
          btn.classList.remove('bg-slate-900','text-white','border-slate-900');
          btn.textContent = old;
        }, 1200);
      }catch(e){}
    });
  })();
</script>
@endpush
