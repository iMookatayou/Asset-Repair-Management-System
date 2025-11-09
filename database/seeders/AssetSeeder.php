<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $now  = Carbon::now();

        // map departments by code (จาก DepartmentSeeder ของคุณ)
        $dept = DB::table('departments')->pluck('id','code'); // ['OPD'=>1, 'ER'=>2, ...]
        // map categories by slug
        $cat  = DB::table('asset_categories')->pluck('id','slug');

        if ($dept->isEmpty() || $cat->isEmpty()) {
            $this->command->warn('⚠️ กรุณา seed departments และ asset_categories ให้เรียบร้อยก่อน (legacy string category column no longer used)');
            return;
        }

        $rows = [
            // ===== เครื่องมือแพทย์ =====
            ['asset_code'=>'MD-0001','name'=>'เครื่องวัดความดันอัตโนมัติ','type'=>'Medical','brand'=>'Omron','model'=>'HEM-7156','serial_number'=>'MD-BP-0001','location'=>'OPD ห้องตรวจ 1','department_code'=>'MED','category_slug'=>'medical-equipment'],
            ['asset_code'=>'MD-0002','name'=>'เครื่องดูดเสมหะ','type'=>'Medical','brand'=>'Yuwell','model'=>'7E-A','serial_number'=>'MD-SU-0002','location'=>'ER ฉุกเฉิน','department_code'=>'EM','category_slug'=>'medical-equipment'],
            ['asset_code'=>'MD-0003','name'=>'เตียงผู้ป่วยไฟฟ้า','type'=>'Medical','brand'=>'Paramount Bed','model'=>'KA-600','serial_number'=>'MD-BED-0003','location'=>'IPD หอผู้ป่วยใน','department_code'=>'SURG','category_slug'=>'medical-equipment'],
            ['asset_code'=>'MD-0004','name'=>'เครื่องวัดออกซิเจนปลายนิ้ว','type'=>'Medical','brand'=>'Beurer','model'=>'PO30','serial_number'=>'MD-POX-0004','location'=>'ER จุดคัดกรอง','department_code'=>'EM','category_slug'=>'medical-equipment'],

            // ===== IT / Computers =====
            ['asset_code'=>'IT-0001','name'=>'คอมพิวเตอร์ตั้งโต๊ะ','type'=>'Computer','brand'=>'HP','model'=>'EliteDesk 800','serial_number'=>'IT-PC-0001','location'=>'เวชระเบียน','department_code'=>'HIM','category_slug'=>'computers'],
            ['asset_code'=>'IT-0002','name'=>'โน้ตบุ๊กงานแพทย์','type'=>'Notebook','brand'=>'Dell','model'=>'Latitude 5440','serial_number'=>'IT-NB-0002','location'=>'ห้องแพทย์เวร','department_code'=>'MED','category_slug'=>'computers'],
            ['asset_code'=>'IT-0003','name'=>'เครื่องพิมพ์เลเซอร์','type'=>'Printer','brand'=>'Brother','model'=>'HL-L2320D','serial_number'=>'IT-PRN-0003','location'=>'พัสดุ','department_code'=>'PROC','category_slug'=>'computers'],
            ['asset_code'=>'IT-0004','name'=>'จอมอนิเตอร์ 24"','type'=>'Monitor','brand'=>'Dell','model'=>'P2422H','serial_number'=>'IT-MON-0004','location'=>'การเงิน','department_code'=>'FIN','category_slug'=>'computers'],

            // ===== Network =====
            ['asset_code'=>'NET-0001','name'=>'สวิตช์ 24 พอร์ต','type'=>'Network Switch','brand'=>'Cisco','model'=>'CBS250-24T','serial_number'=>'NET-SW-0001','location'=>'ตู้สื่อสาร ชั้น 1','department_code'=>'IT','category_slug'=>'network'],
            ['asset_code'=>'NET-0002','name'=>'เราเตอร์หลัก','type'=>'Router','brand'=>'MikroTik','model'=>'RB4011','serial_number'=>'NET-RT-0002','location'=>'ห้องเซิร์ฟเวอร์','department_code'=>'IT','category_slug'=>'network'],
            ['asset_code'=>'NET-0003','name'=>'Access Point ฝ้าเพดาน','type'=>'Access Point','brand'=>'Ubiquiti','model'=>'U6-Lite','serial_number'=>'NET-AP-0003','location'=>'OPD โถงกลาง','department_code'=>'IT','category_slug'=>'network'],

            // ===== เครื่องใช้ไฟฟ้า =====
            ['asset_code'=>'EL-0001','name'=>'ตู้เย็นเก็บยา','type'=>'Electrical','brand'=>'Samsung','model'=>'RT20HAR1','serial_number'=>'EL-FR-0001','location'=>'ห้องยา','department_code'=>'PHARM','category_slug'=>'electrical'],
            ['asset_code'=>'EL-0002','name'=>'ไมโครเวฟ','type'=>'Electrical','brand'=>'Sharp','model'=>'R-220','serial_number'=>'EL-MW-0002','location'=>'ห้องพักเจ้าหน้าที่','department_code'=>'ADM','category_slug'=>'electrical'],

            // ===== Air-conditioning =====
            ['asset_code'=>'AC-0001','name'=>'เครื่องปรับอากาศ 18000 BTU','type'=>'Air Conditioner','brand'=>'Daikin','model'=>'FTKM18','serial_number'=>'AC-18K-0001','location'=>'ห้องประชุม','department_code'=>'ADM','category_slug'=>'air-conditioning'],
            ['asset_code'=>'AC-0002','name'=>'เครื่องปรับอากาศ 9000 BTU','type'=>'Air Conditioner','brand'=>'Mitsubishi','model'=>'MSY-JP09','serial_number'=>'AC-09K-0002','location'=>'เวรเปล','department_code'=>'IPD','category_slug'=>'air-conditioning'],

            // ===== Furniture =====
            ['asset_code'=>'FN-0001','name'=>'โต๊ะทำงานเหล็ก 120 ซม.','type'=>'Furniture','brand'=>'Lucky','model'=>'L-120','serial_number'=>'FN-DESK-0001','location'=>'สำนักงานกลาง','department_code'=>'ADM','category_slug'=>'furniture'],
            ['asset_code'=>'FN-0002','name'=>'เก้าอี้สำนักงานปรับระดับ','type'=>'Furniture','brand'=>'Modernform','model'=>'M1','serial_number'=>'FN-CHAIR-0002','location'=>'ธุรการ','department_code'=>'ADM','category_slug'=>'furniture'],

            // ===== CCTV =====
            ['asset_code'=>'CCTV-0001','name'=>'กล้องวงจรปิด 4MP','type'=>'CCTV','brand'=>'Hikvision','model'=>'DS-2CD2043','serial_number'=>'CC-0001','location'=>'หน้าทางเข้าอาคาร','department_code'=>'FAC','category_slug'=>'cctv'],
            ['asset_code'=>'CCTV-0002','name'=>'เครื่องบันทึก NVR 8CH','type'=>'CCTV','brand'=>'Dahua','model'=>'NVR2108','serial_number'=>'CC-NVR-0002','location'=>'ห้องควบคุม','department_code'=>'FAC','category_slug'=>'cctv'],

            // ===== Lab =====
            ['asset_code'=>'LAB-0001','name'=>'เครื่องเหวี่ยงตกตะกอน (Centrifuge)','type'=>'Lab','brand'=>'Eppendorf','model'=>'5702','serial_number'=>'LAB-CF-0001','location'=>'LAB','department_code'=>'LAB','category_slug'=>'lab-equipment'],
            ['asset_code'=>'LAB-0002','name'=>'เครื่องวิเคราะห์ทางเคมี (Analyzer)','type'=>'Lab','brand'=>'Mindray','model'=>'BS-200E','serial_number'=>'LAB-AN-0002','location'=>'LAB','department_code'=>'LAB','category_slug'=>'lab-equipment'],

            // ===== Imaging (มีไว้กรณีใช้งาน) =====
            ['asset_code'=>'IMG-0001','name'=>'อัลตราซาวด์พกพา','type'=>'Imaging','brand'=>'GE','model'=>'Vscan','serial_number'=>'IMG-US-0001','location'=>'ER ห้องตรวจฉุกเฉิน','department_code'=>'EM','category_slug'=>'imaging'],
        ];

        // เติมฟิลด์ร่วม + map code/slug -> id
        $insert = [];
        foreach ($rows as $r) {
            $r['department_id']   = $dept[$r['department_code']] ?? null;
            $r['category_id']     = $cat[$r['category_slug']] ?? null;
            $r['purchase_date']   = $now->copy()->subYears(1)->toDateString();
            $r['warranty_expire'] = $now->copy()->addYear()->toDateString();
            $roll = mt_rand(1, 100);
            $r['status'] = $roll <= 80 ? 'active' : ($roll <= 95 ? 'in_repair' : 'disposed');
            $r['created_at']      = $now;
            $r['updated_at']      = $now;

            unset($r['department_code'], $r['category_slug']); // remove helper mapping keys
            $insert[] = $r;
        }

        // ป้องกันรันซ้ำด้วย upsert ตาม asset_code (unique)
        DB::table('assets')->upsert(
            $insert,
            ['asset_code'],
            ['name','type','brand','model','serial_number','location','department_id','category_id','purchase_date','warranty_expire','status','updated_at']
        );
    }
}
