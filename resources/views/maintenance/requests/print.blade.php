{{-- resources/views/maintenance/requests/print.blade.php --}}
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Maintenance Work Order #{{ $req->request_no ?? $req->id }}</title>

    <style>
        /* ====== SARABUN FONT ====== */
        @font-face {
            font-family: "Sarabun";
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Sarabun-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: "Sarabun";
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path('fonts/Sarabun-Bold.ttf') }}") format("truetype");
        }

        html, body, * {
            font-family: "Sarabun", DejaVu Sans, sans-serif;
            box-sizing: border-box;
        }

        body {
            font-size: 12px;
            margin: 16px 20px;
            color: #000;
        }

        .text-center { text-align:center; }
        .text-right  { text-align:right; }
        .small       { font-size:10px; }

        /* ===== HEADER (เตี้ยลง / ประหยัดพื้นที่) ===== */
        .header-wrapper {
            width: 100%;
            border-bottom: 1px solid #0f4c81;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-logo {
            width: 14%;
        }

        .header-logo img {
            max-width: 50px;
            max-height: 50px;
        }

        .header-center {
            width: 56%;
            text-align: left;
            padding-left: 4px;
        }

        .header-right {
            width: 30%;
            text-align: right;
            font-size: 10px;
            line-height: 1.4;
        }

        .h-name-th {
            font-size: 10px;
        }

        .h-name-en {
            font-size: 12px;
            font-weight: bold;
        }

        .h-subtitle {
            font-size: 9px;
            color: #555;
        }

        .h-doc-title {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 2px;
            padding: 3px 5px;
            background: #e6f0fb;
            border-left: 3px solid #0f4c81;
            border-radius: 2px;
        }

        .section-sub {
            font-size: 10px;
            color:#555;
            margin-left: 2px;
            margin-bottom: 2px;
        }

        /* ===== TABLES ===== */
        table.meta {
            width:100%;
            border-collapse:collapse;
            margin-top:3px;
        }
        table.meta td {
            padding:2px 3px;
            vertical-align:top;
        }
        table.meta td.label {
            width:22%;
            font-weight:bold;
            white-space:nowrap;
        }

        table.grid {
            width:100%;
            border-collapse:collapse;
            margin-top:4px;
        }
        table.grid th,
        table.grid td {
            border:1px solid #999;
            padding:2px 3px;
            font-size:11px;
        }
        table.grid th {
            background:#eef3fb;
        }

        /* ===== BOXES ===== */
        .box {
            border:1px solid #d1d5db;
            border-radius:3px;
            padding:5px 7px;
            margin-top:3px;
            font-size:11px;
        }
        .box-muted {
            background:#f9fafb;
        }

        /* ===== CHECKBOX LOOK ===== */
        .checkbox-box {
            display:inline-block;
            width:8px;
            height:8px;
            border:1px solid #6b7280;
            margin-right:4px;
        }
        .checkbox-box.checked {
            background:#111827;
        }

        /* ===== SIGNATURE ===== */
        .signature-table {
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
            font-size:11px;
        }
        .signature-table td {
            width:50%;
            padding-top:5px;
        }
        .signature-line {
            border-bottom:1px solid #000;
            width:85%;
            height:16px;
        }

        /* ===== BADGES (ใช้แบบจาง ๆ) ===== */
        .badge {
            display:inline-block;
            padding:1px 6px;
            border-radius:9999px;
            font-size:10px;
            border:1px solid #d1d5db;
        }
        .badge-status {
            background:#eef3fb;
            border-color:#cbd5f5;
        }
        .prio-low    { background:#f3f4f6; }
        .prio-medium { background:#e0f2fe; color:#0369a1; }
        .prio-high   { background:#fef3c7; color:#92400e; }
        .prio-urgent { background:#fee2e2; color:#b91c1c; }
    </style>
</head>

<body>
@php
    // hospital array มาจาก controller: ['name_th' => ..., 'name_en' => ...]
    $hospitalNameTh = $hospital['name_th'] ?? 'โรงพยาบาลพระปกเกล้า';
    $hospitalNameEn = $hospital['name_en'] ?? 'PHRAPOKKLAO HOSPITAL';
    $logoPath       = public_path('images/logoppk.png');

    $opLog = $req->operationLog;

    $status = strtolower($req->status ?? '');
    $statusLabel = [
        'pending'     => 'รอคิว',
        'accepted'    => 'รับงานแล้ว',
        'in_progress' => 'ระหว่างดำเนินการ',
        'on_hold'     => 'พักไว้',
        'resolved'    => 'แก้ไขแล้ว',
        'closed'      => 'ปิดงาน',
        'cancelled'   => 'ยกเลิก',
    ][$status] ?? '';

    $prio = strtolower($req->priority ?? '');
    // ให้เป็นคำไทยปกติ (เหมือนฟอร์มราชการ)
    $prioLabel = [
        'low'    => 'ต่ำ',
        'medium' => 'ปานกลาง',
        'high'   => 'สูง',
        'urgent' => 'เร่งด่วน',
    ][$prio] ?? ($req->priority ?? '');

    $prioClass = match ($prio) {
        'medium' => 'prio-medium',
        'high'   => 'prio-high',
        'urgent' => 'prio-urgent',
        default  => 'prio-low',
    };

    $workers = $req->workers ?? collect();
@endphp

{{-- ================= HEADER ================= --}}
<div class="header-wrapper">
    <table class="header-table">
        <tr>
            {{-- LOGO --}}
            <td class="header-logo">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo">
                @endif
            </td>

            {{-- HOSPITAL NAME: TH + EN (same line, no parentheses) --}}
            <td class="header-center">
                <div style="font-size:11px; font-weight:bold;">
                    {{ $hospitalNameTh }}&nbsp;&nbsp;{{ $hospitalNameEn }}
                </div>

                <div class="h-doc-title" style="margin-top:4px;">
                    แบบฟอร์มใบแจ้งซ่อม / Maintenance Work Order
                </div>
            </td>

            {{-- RIGHT META --}}
            <td class="header-right">
                <div>เลขที่ใบงาน: <strong>{{ $req->request_no ?? $req->id }}</strong></div>
                <div>พิมพ์เมื่อ: {{ now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ================= ส่วนที่ 1: ข้อมูลงานซ่อมและผู้แจ้ง ================= --}}
<div class="section-title">ส่วนที่ 1 : ข้อมูลงานซ่อมและผู้แจ้ง</div>
<div class="section-sub">ข้อมูลผู้แจ้ง ทรัพย์สิน และสถานะใบงาน</div>

<table class="meta">
    <table class="meta">
    <tr>
        <td class="label">หมายเลขงานระบบ</td>
        <td>#{{ $req->id }}</td>
        <td class="label">สถานะปัจจุบัน</td>
        <td>{{ $statusLabel }}</td>
    </tr>
    <tr>
        <td class="label">ระดับความสำคัญ</td>
        <td>{{ $prioLabel }}</td>
        <td class="label">วันที่รับคำขอ</td>
        <td>{{ optional($req->request_date ?? $req->created_at)->format('d/m/Y H:i') }}</td>
    </tr>

    <tr>
        <td class="label">ผู้แจ้งซ่อม</td>
        <td>
            {{ $req->reporter->name ?? $req->reporter_name ?? '-' }}<br>
            @if($req->reporter_phone)
                <span class="small">โทร. {{ $req->reporter_phone }}</span><br>
            @endif
            @if($req->reporter_email)
                <span class="small">{{ $req->reporter_email }}</span>
            @endif
        </td>
        <td class="label">หน่วยงาน / สถานที่</td>
        <td>
            {{ $req->location_text ?? $req->department->name_th ?? '-' }}<br>
            @if($req->department?->code)
                <span class="small">รหัสหน่วยงาน: {{ $req->department->code }}</span>
            @endif
        </td>
    </tr>
    <tr>
        <td class="label">ทรัพย์สินที่เกี่ยวข้อง</td>
        <td>
            {{ $req->asset->name ?? '-' }}<br>
            @if($req->asset?->asset_code)
                <span class="small">รหัสครุภัณฑ์: {{ $req->asset->asset_code }}</span>
            @endif
        </td>
        <td class="label">ช่างหลัก</td>
        <td>{{ $req->technician->name ?? '-' }}</td>
    </tr>
</table>

<table class="grid">
    <tr>
        <th>รับคำขอ</th>
        <th>มอบหมายทีมช่าง</th>
        <th>เสร็จสิ้น / ปิดงาน</th>
    </tr>
    <tr>
        <td class="text-center">
            {{ optional($req->request_date ?? $req->created_at)->format('d/m/Y H:i') ?? '—' }}
        </td>
        <td class="text-center">{{ optional($req->assigned_date)->format('d/m/Y H:i') ?? '—' }}</td>
        <td class="text-center">{{ optional($req->completed_date)->format('d/m/Y H:i') ?? '—' }}</td>
    </tr>
</table>

{{-- ================= ส่วนที่ 2: รายละเอียดปัญหา ================= --}}
<div class="section-title">ส่วนที่ 2 : หัวข้อและรายละเอียดปัญหา</div>

<div class="box box-muted">
    <div class="small" style="color:#555;">หัวข้อใบงาน</div>
    <div style="font-weight:bold; margin-top:1px;">
        {{ $req->title ?? '-' }}
    </div>
</div>

<div class="box" style="min-height:45px;">
    <div class="small" style="color:#555; margin-bottom:2px;">รายละเอียด / อาการเสีย</div>
    {!! nl2br(e($req->description ?: '-')) !!}
</div>

{{-- ================= ส่วนที่ 3: ทีมช่าง ================= --}}
<div class="section-title">ส่วนที่ 3 : ทีมช่างที่เกี่ยวข้อง</div>

@if($workers->isEmpty())
    <div class="box box-muted small">
        ยังไม่ได้มอบหมายทีมช่าง (ช่างหลัก: {{ $req->technician->name ?? '-' }})
    </div>
@else
    <table class="grid">
        <tr>
            <th style="width: 8%;">ลำดับ</th>
            <th style="width: 42%;">ชื่อช่าง / ตำแหน่ง</th>
            <th style="width: 20%;">บทบาท</th>
            <th style="width: 20%;">สถานะ</th>
            <th style="width: 10%;">หมายเหตุ</th>
        </tr>

        @foreach($workers as $i => $w)
            @php
                $assign = $req->assignments->firstWhere('user_id', $w->id);
                $txt = $assign?->status === \App\Models\MaintenanceAssignment::STATUS_IN_PROGRESS ? 'กำลังดำเนินการ'
                     : ($assign?->status === \App\Models\MaintenanceAssignment::STATUS_DONE ? 'เสร็จสิ้น'
                     : ($assign?->status === \App\Models\MaintenanceAssignment::STATUS_CANCELLED ? 'ยกเลิก' : 'ไม่ระบุ'));
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    {{ $w->name }}<br>
                    <span class="small">{{ $w->role_label }}</span>
                </td>
                <td class="small">{{ $assign?->role ?? '-' }}</td>
                <td class="small">{{ $txt }}</td>
                <td></td>
            </tr>
        @endforeach
    </table>
@endif

{{-- ================= ส่วนที่ 4: รายงานการปฏิบัติงาน ================= --}}
<div class="section-title">ส่วนที่ 4 : รายงานการปฏิบัติงาน</div>

<table class="meta">
    <tr>
        <td class="label">วันที่ปฏิบัติงาน</td>
        <td style="width:33%;">
            {{ optional($opLog?->operation_date)->format('d/m/Y') ?? '—' }}
        </td>
        <td class="label">หน่วยงานที่เกี่ยวข้อง</td>
        <td>{{ $opLog->hospital_name ?? $hospitalNameTh }}</td>
    </tr>
</table>

<div class="box box-muted">
    <div class="small" style="color:#555;">วิธีการปฏิบัติ / การคิดค่าใช้จ่าย</div>
    <div>
        <span class="checkbox-box {{ ($opLog->operation_method ?? '') === 'requisition' ? 'checked' : '' }}"></span>
        ตามใบเบิกครุภัณฑ์ / วัสดุ
    </div>
    <div>
        <span class="checkbox-box {{ ($opLog->operation_method ?? '') === 'service_fee' ? 'checked' : '' }}"></span>
        ค่าบริการ / ค่าแรงช่าง
    </div>
    <div>
        <span class="checkbox-box {{ ($opLog->operation_method ?? '') === 'other' ? 'checked' : '' }}"></span>
        อื่น ๆ
    </div>
</div>

<div class="box">
    <div class="small" style="color:#555;">ประเภทงานที่ปฏิบัติ</div>
    <div>
        <span class="checkbox-box {{ ($opLog->issue_software ?? false) ? 'checked' : '' }}"></span>
        Software
    </div>
    <div>
        <span class="checkbox-box {{ ($opLog->issue_hardware ?? false) ? 'checked' : '' }}"></span>
        Hardware
    </div>
</div>

<div class="box" style="min-height:40px;">
    <div class="small" style="color:#555;">หมายเหตุ / รายละเอียดประกอบ</div>
    {!! nl2br(e($opLog->remark ?? '-')) !!}
</div>

{{-- ================= ลายเซ็น ================= --}}
<table class="signature-table">
    <tr>
        <td>
            <div class="small">ผู้แจ้งซ่อม</div>
            <div class="signature-line"></div>
            <div class="small">
                ( {{ $req->reporter->name ?? '........................' }} )
            </div>
            <div class="small">วันที่ ....../....../..........</div>
        </td>
        <td>
            <div class="small">ช่างผู้ปฏิบัติงาน</div>
            <div class="signature-line"></div>
            <div class="small">
                ( {{ $req->technician->name ?? '........................' }} )
            </div>
            <div class="small">วันที่ ....../....../..........</div>
        </td>
    </tr>
</table>

<div class="small" style="color:#555; margin-top:6px;">
    หมายเหตุ: เอกสารนี้จัดทำจากระบบ Maintenance Work Order ของ{{ $hospitalNameTh }} เพื่อใช้ประกอบงานซ่อมบำรุง
</div>

</body>
</html>
