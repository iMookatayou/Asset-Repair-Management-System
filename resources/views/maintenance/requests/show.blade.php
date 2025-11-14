@extends('layouts.app')

@section('title', 'รายละเอียดงานซ่อม #'.$req->id)

@section('page-header')
  @php
    $status = strtolower((string) $req->status);
    $statusLabel = [
      'pending'     => 'รอคิว',
      'accepted'    => 'รับงานแล้ว',
      'in_progress' => 'ระหว่างดำเนินการ',
      'on_hold'     => 'พักไว้',
      'resolved'    => 'แก้ไขแล้ว',
      'closed'      => 'ปิดงาน',
      'cancelled'   => 'ยกเลิก',
    ][$status] ?? $status;

    $statusTone = match ($status) {
      'pending'     => 'bg-sky-50 text-sky-700 border-sky-200',
      'accepted'    => 'bg-indigo-50 text-indigo-700 border-indigo-200',
      'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
      'on_hold'     => 'bg-amber-50 text-amber-700 border-amber-200',
      'resolved'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
      'closed'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
      'cancelled'   => 'bg-rose-50 text-rose-700 border-rose-200',
      default       => 'bg-slate-50 text-slate-700 border-slate-200',
    };

    $prio = strtolower((string) $req->priority);
    // ← เปลี่ยน normal -> medium
    $prioLabel = [
      'low'    => 'ต่ำ',
      'medium' => 'ปานกลาง',
      'high'   => 'สูง',
      'urgent' => 'เร่งด่วน',
    ][$prio] ?? ($req->priority ?? '—');

    $prioTone = match ($prio) {
      'low'    => 'bg-white text-zinc-700 border-zinc-300',
      'medium' => 'bg-white text-sky-800 border-sky-300',
      'high'   => 'bg-white text-amber-800 border-amber-300',
      'urgent' => 'bg-white text-rose-800 border-rose-300',
      default  => 'bg-white text-zinc-700 border-zinc-300',
    };
  @endphp

  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div class="flex flex-col gap-2">
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M21 2l-4.2 4.2a4 4 0 01-5.6 5.6L7 16l-3 1 1-3 4.2-4.2a4 4 0 015.6-5.6L21 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Repair work details <span id="rid">#{{ $req->id }}</span>
            @if($req->request_no)
              <span class="ml-2 inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-medium text-slate-700" title="Reference number">
                {{ $req->request_no }}
              </span>
            @endif
          </h1>

          <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium bg-slate-50 text-slate-700 border-slate-200">
              {{ $req->asset->name ?? $req->asset_id ?? '—' }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusTone }}" id="currentStatusBadge">
              {{ $statusLabel }}
            </span>
            @if($req->technician?->name)
              <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium bg-indigo-50 text-indigo-700 border-indigo-200" id="currentTechnicianBadge">
                ช่าง: {{ $req->technician->name }}
              </span>
            @endif
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {{ $prioTone }}">
              {{ $prioLabel }}
            </span>
          </div>
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-2">
          <button id="copyIdBtn" type="button"
                  class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">
            Copy Number
          </button>
          <button type="button" onclick="window.print()"
                  class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">
            Print
          </button>
          <a href="{{ route('maintenance.requests.index') }}"
             class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back
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
  @php $requestedAt = optional($req->request_date ?? $req->created_at); @endphp

  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- ===== ข้อมูลพื้นฐาน ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 text-sm">
          <div>
            <div class="text-slate-500">หมายเลข</div>
            <div class="font-semibold text-slate-900">#{{ $req->id }}</div>
            @if($req->request_no)
              <div class="text-slate-600 text-sm mt-0.5">{{ $req->request_no }}</div>
            @endif
          </div>
          <div>
            <div class="text-slate-500">ผู้แจ้ง</div>
            <div class="font-semibold text-slate-900">{{ $req->reporter->name ?? $req->reporter_name ?? '-' }}</div>
            @php $contactEmail = $req->reporter->email ?? $req->reporter_email; @endphp
            @if($contactEmail)
              <div class="text-slate-600 text-sm mt-0.5">{{ $contactEmail }}</div>
            @endif
          </div>
          <div>
            <div class="text-slate-500">ทรัพย์สิน</div>
            <div class="font-semibold text-slate-900">{{ $req->asset->name ?? $req->asset_id ?? '—' }}</div>
          </div>
          <div>
            <div class="text-slate-500">สถานที่</div>
            <div class="font-semibold text-slate-900">{{ $req->location_text ?? '—' }}</div> {{-- ← ใช้ location_text --}}
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="lg:col-span-2">
            <div class="mb-1 text-slate-500">หัวข้อ</div>
            <div class="text-base font-semibold text-slate-900">{{ $req->title }}</div>

            <div class="my-4 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

            <div class="mb-1 text-slate-500">รายละเอียด</div>
            <div class="prose max-w-none text-slate-800">{{ $req->description ?: '—' }}</div>
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

    {{-- ===== ดำเนินการ (Transition) ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-6">
        <h3 class="text-base font-semibold text-slate-900">ดำเนินการ</h3>

        <form method="post"
              action="{{ route('maintenance.requests.transition', $req) }}"
              class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2" novalidate>
          @csrf

          <div>
            <label for="status" class="mb-1 block text-sm text-slate-700">เปลี่ยนสถานะ</label>
            <select id="status" name="status" required
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:ring-emerald-600">
              @foreach ([
                'pending'     => 'รอคิว',
                'accepted'    => 'รับงานแล้ว',
                'in_progress' => 'ระหว่างดำเนินการ',
                'on_hold'     => 'พักไว้',
                'resolved'    => 'แก้ไขแล้ว',
                'closed'      => 'ปิดงาน',
                'cancelled'   => 'ยกเลิก',
              ] as $k => $label)
                <option value="{{ $k }}" @selected(old('status', $req->status) === $k)>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div id="techWrap">
            <label for="technician_id" class="mb-1 block text-sm text-slate-700">ช่างผู้รับผิดชอบ (ถ้ามี)</label>
            <x-searchable-select
              id="technician_id"
              name="technician_id"
              placeholder="— ไม่ระบุ —"
              label-field="name"
              value-field="id"
              :items="$techUsers ?? collect()"
              :value="old('technician_id', $req->technician_id)"
            />
          </div>

          <div class="md:col-span-2">
            <label for="note" class="mb-1 block text-sm text-slate-700">บันทึกเพิ่มเติม</label>
            <input id="note" type="text" name="note"
                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:ring-emerald-600"
                   placeholder="(ไม่บังคับ)" value="{{ old('note') }}">
          </div>

          <div class="md:col-span-2 flex flex-wrap gap-2">
            <button type="submit"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
              บันทึกการดำเนินการ
            </button>
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
            <label for="caption" class="mb-1 block text-sm text-slate-700">คำอธิบาย/ชื่อไฟล์ (optional)</label>
            <input id="caption" type="text" name="caption"
                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-600 focus:ring-emerald-600"
                   value="{{ old('caption') }}" placeholder="เช่น รูปก่อนซ่อม / ใบเสนอราคา">
          </div>
          <div>
            <label for="file" class="mb-1 block text-sm text-slate-700">ไฟล์</label>
            <input id="file" type="file" name="file" required
                   accept="image/*,application/pdf"
                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            <p class="mt-1 text-xs text-slate-500">รองรับรูปภาพ และ PDF • สูงสุดไฟล์ละ 10MB</p>
          </div>
          <div>
            <label for="alt_text" class="mb-1 block text-sm text-slate-700">Alt text (optional)</label>
            <input id="alt_text" type="text" name="alt_text"
                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"
                   value="{{ old('alt_text') }}" placeholder="ข้อความอธิบายรูปเพื่อการเข้าถึง">
            <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
              <input type="checkbox" name="is_private" value="1" class="rounded border-slate-300">
              เก็บเป็นไฟล์ส่วนตัว (ไม่แสดงทางเว็บสาธารณะ)
            </label>
          </div>
          <div class="md:col-span-3">
            <button type="submit"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">
              อัปโหลดไฟล์
            </button>
          </div>
        </form>

        @php $atts = ($req->attachments ?? collect()); @endphp
        @if($atts->count())
          <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
            @foreach($atts as $att)
              @php
                // ใช้ข้อมูลจาก relation file
                $file = $att->file;       // อาจถูก eager load มาจาก Controller
                $mime = (string) ($file->mime ?? '');
                $name = $att->filename;   // original_name หรือ basename(path)
                $isPrivate = (bool) $att->is_private;

                // URL: public จะได้ URL โดยตรง, private = ใช้ route เพื่อ authorize/ดาวน์โหลด
                $openUrl = $att->url ?? route('attachments.show', $att);
                $isImg = $att->is_image;  // อิง mime ในตาราง files
              @endphp

              <figure class="overflow-hidden rounded-lg border border-slate-200">
                @if($isImg && !$isPrivate && $att->url)
                  {{-- แสดง thumbnail ได้ก็ต่อเมื่อ public --}}
                  <a href="{{ $openUrl }}" target="_blank" rel="noopener">
                    <img src="{{ $att->url }}" alt="{{ $att->alt_text ?? $name }}" class="h-36 w-full object-cover">
                  </a>
                @else
                  <div class="grid h-36 w-full place-items-center text-slate-500">
                    {{ strtoupper(pathinfo($name, PATHINFO_EXTENSION) ?: 'FILE') }}
                  </div>
                @endif

                <figcaption class="flex items-center justify-between gap-2 px-3 py-2 text-xs">
                  <span class="inline-flex items-center rounded-full border {{ $isPrivate ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-200 bg-slate-50 text-slate-700' }} px-2 py-0.5 font-medium">
                    {{ $isPrivate ? 'private' : 'public' }}
                  </span>
                  <span class="truncate text-slate-600" title="{{ $name }}">{{ $name }}</span>
                  <a href="{{ $openUrl }}" {{ $isPrivate ? '' : 'target=_blank rel=noopener' }}
                     class="inline-flex items-center rounded-md border border-sky-300 bg-sky-50 px-2 py-1 font-medium text-sky-800 hover:bg-sky-100">
                    เปิด
                  </a>
                </figcaption>
              </figure>
            @endforeach
          </div>
        @else
          <p class="mt-3 text-slate-500">ไม่มีไฟล์แนบ</p>
        @endif
      </div>
    </section>

    {{-- ===== ไทม์ไทม์ ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="p-6">
        <h3 class="text-base font-semibold text-slate-900">ประวัติการดำเนินการ</h3>

        <div class="mt-4 space-y-3">
          @forelse(($req->logs ?? collect()) as $log)
            @php
              $to = $log->to_status;
              $tone = 'bg-slate-400';
              if (in_array($to, ['resolved','closed'], true)) $tone = 'bg-emerald-600';
              if ($to === 'cancelled') $tone = 'bg-rose-600';
              if (in_array($to, ['in_progress','accepted','on_hold'], true)) $tone = 'bg-amber-600';
              $fromLabel = $log->from_status_label ?? ($log->from_status ?? '—');
              $toLabel   = $log->to_status_label ?? ($to ?? '—');
            @endphp
            <article class="relative border-l-2 border-slate-200 pl-6">
              <span class="absolute -left-1.5 top-2 inline-block h-3 w-3 rounded-full {{ $tone }}"></span>
              <header class="flex flex-wrap items-center gap-2 text-sm">
                <strong>#{{ $log->id }}</strong>
                @if($log->action === \App\Models\MaintenanceLog::ACTION_TRANSITION)
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                    {{ $fromLabel }} → {{ $toLabel }}
                  </span>
                @else
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                    {{ $log->action }}
                  </span>
                @endif
                @if($log->created_at)
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                    <time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('Y-m-d H:i') }}</time>
                  </span>
                @endif
                @if($log->user?->name ?? $log->user_id)
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                    โดย {{ $log->user->name ?? ('#'.$log->user_id) }}
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
