{{-- ส่วนหัว/สไตล์ใช้ของเดิมได้ --}}

{{-- SEARCH BAR --}}
<form method="GET" class="card">
  <div class="px-4 py-3 border-b border-zinc-700 font-semibold">ค้นหา / ตัวกรอง</div>
  <div class="p-4 grid grid-cols-1 md:grid-cols-5 gap-3">
    <div class="md:col-span-2">
      <label class="block text-xs text-zinc-400 mb-1">คำค้น</label>
      <input type="text" name="q" value="{{ request('q') }}" placeholder="เช่น รหัสทรัพย์สิน, ชื่อทรัพย์สิน, ผู้แจ้ง, ช่าง, สถานะ"
             class="w-full rounded-md bg-zinc-900 border-zinc-700 text-zinc-100">
    </div>
    <div>
      <label class="block text-xs text-zinc-400 mb-1">สถานะ</label>
      <select name="status" class="w-full rounded-md bg-zinc-900 border-zinc-700 text-zinc-100">
        <option value="">ทั้งหมด</option>
        @foreach(['pending'=>'Pending','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $val=>$lbl)
          <option value="{{ $val }}" @selected(request('status')===$val)>{{ $lbl }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs text-zinc-400 mb-1">จากวันที่</label>
      <input type="date" name="from" value="{{ request('from') }}"
             class="w-full rounded-md bg-zinc-900 border-zinc-700 text-zinc-100">
    </div>
    <div>
      <label class="block text-xs text-zinc-400 mb-1">ถึงวันที่</label>
      <input type="date" name="to" value="{{ request('to') }}"
             class="w-full rounded-md bg-zinc-900 border-zinc-700 text-zinc-100">
    </div>
    <div class="md:col-span-5 flex gap-2">
      <button class="px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white">ค้นหา</button>
      {{-- ปุ่มนี้ทำให้โหลดผลแม้ไม่ใส่คีย์เวิร์ด --}}
      @if(request()->missing('q') && request()->missing('status') && request()->missing('from') && request()->missing('to'))
        <a href="{{ request()->fullUrlWithQuery(['show'=>1]) }}"
           class="px-4 py-2 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-100">แสดงผล</a>
      @endif
    </div>
  </div>
</form>

{{-- KPIs (คงไว้) --}}

@if($deferred ?? false)
  {{-- ยังไม่ค้นหา: โชว์เฉพาะ KPI + ข้อความแนะนำ --}}
  <div class="card p-4 text-zinc-300">
    ใส่คำค้นหรือเลือกตัวกรอง แล้วกด “ค้นหา” เพื่อแสดงรายการและสรุปเพิ่มเติม
  </div>
@else
  {{-- มีการค้นหาแล้ว: แสดงสรุป + ตาราง (ใช้ของเดิมที่เบา) --}}
  {{-- บล็อก trend/type/dept และตาราง recent ของคุณวางต่อจากนี้ได้เลย --}}
@endif
