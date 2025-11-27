{{-- resources/views/assets/print.blade.php --}}
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบข้อมูลครุภัณฑ์ {{ $asset->asset_code }}</title>

    <style>
        /* ====== FONT: SARABUN (จาก public/fonts) ====== */
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
            font-size: 13px;
            margin: 22px 26px;
            color: #000;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }

        .mt-4  { margin-top: 4px;  }
        .mt-8  { margin-top: 8px;  }
        .mt-12 { margin-top: 12px; }
        .mb-4  { margin-bottom: 4px; }
        .mb-8  { margin-bottom: 8px; }

        /* ====== HEADER ====== */
        .header-wrapper {
            width: 100%;
            border-bottom: 2px solid #0f4c81;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-logo {
            width: 15%;
            text-align: left;
        }

        .header-logo img {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
        }

        .header-center {
            width: 60%;
            text-align: center;
        }

        .header-right {
            width: 25%;
            font-size: 11px;
            text-align: right;
        }

        .h-name-th {
            font-size: 12px;
        }

        .h-name-en {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .h-subtitle {
            font-size: 11px;
            color: #555;
        }

        .h-doc-title {
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
            color: #0f4c81;
        }

        /* ====== SECTION TITLE ====== */
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-top: 10px;
            margin-bottom: 3px;
            padding: 4px 6px;
            border-radius: 3px;
            background: #e6f0fb;      /* ฟ้าอ่อน */
            border-left: 3px solid #0f4c81;
        }

        .section-sub {
            font-size: 11px;
            color: #555;
            margin-top: 1px;
            margin-left: 2px;
        }

        /* ====== META TABLE ====== */
        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table.meta td {
            padding: 2px 4px;
            vertical-align: top;
        }

        table.meta td.label {
            width: 22%;
            font-weight: bold;
            white-space: nowrap;
        }

        /* ====== GRID TABLE (SECTION 3) ====== */
        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table.grid th,
        table.grid td {
            border: 1px solid #999;
            padding: 3px 4px;
            font-size: 12px;
        }

        table.grid th {
            background: #eef3fb;
            font-weight: bold;
            text-align: center;
        }

        .footer-note {
            margin-top: 14px;
            font-size: 11px;
        }

        .signature-line {
            margin-top: 26px;
        }

        .sig-col {
            width: 50%;
        }

        .sig-label {
            font-size: 12px;
            margin-bottom: 40px;
        }

        .sig-underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 160px;
        }
    </style>
</head>
<body>
@php
    // helper: ถ้า null/ว่าง → คืนค่าว่าง
    $f  = fn($v) => ($v === null || $v === '') ? '' : $v;
    $fd = fn($v) => $v ? $v->format('d/m/Y') : '';

    $statusMap = [
        'active'    => 'ใช้งานปกติ',
        'in_repair' => 'อยู่ระหว่างซ่อม',
        'disposed'  => 'จำหน่ายออกจากระบบ',
    ];
    $statusLabel = $asset->status ? ($statusMap[$asset->status] ?? '') : '';

    $deptName = $asset->department->display_name
        ?? $asset->department->name_th
        ?? $asset->department->name_en
        ?? '';

    $categoryName = $asset->categoryRef->name ?? '';
    $brandModel   = trim(($asset->brand ?? '').' '.($asset->model ?? ''));

    $hospitalNameTh = 'โรงพยาบาลพระปกเกล้า';
    $hospitalNameEn = 'PHRAPOKKLAO HOSPITAL';
    $subtitle       = 'Asset Repair Management';

    $logoPath = public_path('images/logoppk.png');
@endphp

{{-- ================= HEADER ================= --}}
<div class="header-wrapper">
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo">
                @else
                    {{-- ถ้าไม่มีไฟล์โลโก้ ปล่อยว่างไว้ --}}
                @endif
            </td>
            <td class="header-center">
                <div class="h-name-th">{{ $hospitalNameTh }}</div>
                <div class="h-name-en">{{ $hospitalNameEn }}</div>
                <div class="h-subtitle">{{ $subtitle }}</div>
                <div class="h-doc-title">แบบฟอร์มข้อมูลครุภัณฑ์ / ทรัพย์สิน</div>
            </td>
            <td class="header-right">
                <div>เลขที่เอกสาร: {{-- ใส่ทีหลังบนกระดาษ --}}</div>
                <div>วันที่พิมพ์: {{ now()->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ================= GENERAL INFO ================= --}}
<table class="meta">
    <tr>
        <td class="label">วันที่พิมพ์ฟอร์ม</td>
        <td>{{ now()->format('d/m/Y') }}</td>
        <td class="label">ผู้พิมพ์</td>
        <td>{{ '' }}</td>
    </tr>
</table>

{{-- ================= SECTION 1 ================= --}}
<div class="section-title">ส่วนที่ 1: ข้อมูลครุภัณฑ์</div>
<div class="section-sub">ข้อมูลพื้นฐานของครุภัณฑ์ เพื่อใช้ในการระบุทรัพย์สินให้ชัดเจน</div>

<table class="meta">
    <tr>
        <td class="label">รหัสครุภัณฑ์</td>
        <td>{{ $f($asset->asset_code) }}</td>
        <td class="label">หมวดหมู่</td>
        <td>{{ $f($categoryName) }}</td>
    </tr>
    <tr>
        <td class="label">ชื่อครุภัณฑ์</td>
        <td>{{ $f($asset->name) }}</td>
        <td class="label">ประเภท (Type)</td>
        <td>{{ $f($asset->type) }}</td>
    </tr>
    <tr>
        <td class="label">ยี่ห้อ / รุ่น</td>
        <td>{{ $f($brandModel) }}</td>
        <td class="label">Serial</td>
        <td>{{ $f($asset->serial_number) }}</td>
    </tr>
    <tr>
        <td class="label">หน่วยงานเจ้าของ</td>
        <td>{{ $f($deptName) }}</td>
        <td class="label">สถานที่ใช้งาน</td>
        <td>{{ $f($asset->location) }}</td>
    </tr>
    <tr>
        <td class="label">สถานะปัจจุบัน</td>
        <td>{{ $f($statusLabel) }}</td>
        <td class="label"></td>
        <td></td>
    </tr>
</table>

{{-- ================= SECTION 2 ================= --}}
<div class="section-title">ส่วนที่ 2: ข้อมูลการจัดซื้อและอายุการใช้งาน</div>
<div class="section-sub">ข้อมูลวันที่จัดซื้อ ระยะเวลารับประกัน และรายละเอียดอื่น ๆ</div>

<table class="meta">
    <tr>
        <td class="label">วันที่จัดซื้อ</td>
        <td>{{ $fd($asset->purchase_date) }}</td>
        <td class="label">วันหมดประกัน</td>
        <td>{{ $fd($asset->warranty_expire) }}</td>
    </tr>
    <tr>
        <td class="label">หมายเหตุเพิ่มเติม</td>
        <td colspan="3">
            {{-- เว้นไว้ให้เขียนเพิ่มเติมบนกระดาษ --}}
        </td>
    </tr>
</table>

{{-- ================= SECTION 3 ================= --}}
<div class="section-title">ส่วนที่ 3: ประวัติงานซ่อม (สำหรับกรอกหรือแนบข้อมูลเพิ่มเติม)</div>
<div class="section-sub">ลำดับ เลขที่ใบงาน วันที่แจ้ง อาการ / รายละเอียดปัญหา สถานะ</div>

<table class="grid">
    <thead>
    <tr>
        <th style="width: 8%;">ลำดับ</th>
        <th style="width: 20%;">เลขที่ใบงาน</th>
        <th style="width: 18%;">วันที่แจ้ง</th>
        <th>อาการ / รายละเอียดปัญหา</th>
        <th style="width: 16%;">สถานะ</th>
    </tr>
    </thead>
    <tbody>
    {{-- ตอนนี้ให้แถวว่างไว้กรอกเอง ถ้าอนาคตอยากดึง log จริงค่อย loop --}}
    @for($i = 1; $i <= 5; $i++)
        <tr>
            <td class="text-center">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    @endfor
    </tbody>
</table>

{{-- ================= SECTION 4 ================= --}}
<div class="section-title">ส่วนที่ 4: หมายเหตุ / การรับรองข้อมูล</div>
<div class="section-sub">สำหรับผู้รับผิดชอบครุภัณฑ์หรือผู้ตรวจสอบข้อมูลลงนามรับรอง</div>

<table class="meta">
    <tr>
        <td class="label">ผู้ตรวจสอบข้อมูล</td>
        <td>{{ '' }}</td>
        <td class="label">วันที่ตรวจสอบ</td>
        <td>{{ '' }}</td>
    </tr>
</table>

<table class="meta signature-line">
    <tr>
        <td class="sig-col text-center">
            <div class="sig-label">ผู้ดูแลครุภัณฑ์</div>
            <div class="sig-underline">&nbsp;</div>
        </td>
        <td class="sig-col text-center">
            <div class="sig-label">ผู้ตรวจสอบ / หัวหน้าหน่วยงาน</div>
            <div class="sig-underline">&nbsp;</div>
        </td>
    </tr>
</table>

<div class="footer-note">
    หมายเหตุ: ฟอร์มนี้จัดทำจากระบบ Asset Repair Management ของโรงพยาบาล เพื่อใช้เป็นข้อมูลประกอบการบริหารจัดการครุภัณฑ์
</div>

</body>
</html>
