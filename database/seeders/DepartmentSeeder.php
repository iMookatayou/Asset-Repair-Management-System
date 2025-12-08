<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'ADM',   'th' => 'ฝ่ายบริหารทั่วไป',               'en' => 'General Administration'],
            ['code' => 'FIN',   'th' => 'ฝ่ายการเงินและบัญชี',            'en' => 'Finance & Accounting'],
            ['code' => 'HR',    'th' => 'ฝ่ายทรัพยากรบุคคล',              'en' => 'Human Resources'],
            ['code' => 'IT',    'th' => 'ฝ่ายเทคโนโลยีสารสนเทศ',          'en' => 'Information Technology'],
            ['code' => 'PROC',  'th' => 'ฝ่ายพัสดุและจัดซื้อ',             'en' => 'Procurement & Supplies'],
            ['code' => 'FAC',   'th' => 'อาคารสถานที่/ซ่อมบำรุง',          'en' => 'Facilities & Maintenance'],
            ['code' => 'BME',   'th' => 'วิศวกรรมชีวการแพทย์',            'en' => 'Biomedical Engineering'],
            ['code' => 'HIM',   'th' => 'เวชระเบียน/สารสนเทศสุขภาพ',      'en' => 'Health Information Management'],
            ['code' => 'QA',    'th' => 'คุณภาพและความเสี่ยง',            'en' => 'Quality & Risk Management'],
            ['code' => 'IC',    'th' => 'ควบคุมการติดเชื้อ',               'en' => 'Infection Control'],
            ['code' => 'EDU',   'th' => 'การศึกษาและฝึกอบรม',             'en' => 'Education & Training'],
            ['code' => 'PR',    'th' => 'ประชาสัมพันธ์',                   'en' => 'Public Relations'],
            ['code' => 'SEC',   'th' => 'รักษาความปลอดภัย',               'en' => 'Security'],
            ['code' => 'HK',    'th' => 'แม่บ้าน/ทำความสะอาด',            'en' => 'Housekeeping'],
            ['code' => 'LDY',   'th' => 'ซักฟอก',                           'en' => 'Laundry'],
            ['code' => 'NUT',   'th' => 'โภชนาการและอาหาร',               'en' => 'Nutrition & Dietetics'],
            ['code' => 'LOG',   'th' => 'ขนส่ง/คลังกลาง',                  'en' => 'Logistics & Central Supply'],
            ['code' => 'MED',   'th' => 'อายุรกรรม',                       'en' => 'Internal Medicine'],
            ['code' => 'IPD',   'th' => 'หอผู้ป่วยใน',                       'en' => 'In-Patient Department'],
            ['code' => 'SURG',  'th' => 'ศัลยกรรม',                        'en' => 'Surgery'],
            ['code' => 'PED',   'th' => 'กุมารเวชกรรม',                    'en' => 'Pediatrics'],
            ['code' => 'OBG',   'th' => 'สูติ-นรีเวชกรรม',                 'en' => 'Obstetrics & Gynecology'],
            ['code' => 'ORTH',  'th' => 'ออร์โธปิดิกส์',                   'en' => 'Orthopedics'],
            ['code' => 'ENT',   'th' => 'โสต ศอ นาสิก',                    'en' => 'Otolaryngology (ENT)'],
            ['code' => 'OPH',   'th' => 'จักษุวิทยา',                      'en' => 'Ophthalmology'],
            ['code' => 'ANES',  'th' => 'วิสัญญีวิทยา',                    'en' => 'Anesthesiology'],
            ['code' => 'EM',    'th' => 'เวชศาสตร์ฉุกเฉิน',                'en' => 'Emergency Medicine'],
            ['code' => 'RAD',   'th' => 'รังสีวิทยา',                       'en' => 'Radiology'],
            ['code' => 'LAB',   'th' => 'ห้องปฏิบัติการ/พยาธิวิทยา',      'en' => 'Laboratory & Pathology'],
            ['code' => 'PHARM', 'th' => 'เภสัชกรรม',                        'en' => 'Pharmacy'],
            ['code' => 'REHAB', 'th' => 'เวชกรรมฟื้นฟู',                    'en' => 'Rehabilitation Medicine'],
            ['code' => 'DENT',  'th' => 'ทันตกรรม',                         'en' => 'Dentistry'],
            ['code' => 'PSY',   'th' => 'จิตเวช',                           'en' => 'Psychiatry'],
            ['code' => 'COMM',  'th' => 'เวชกรรมสังคม/เวชป้องกัน',         'en' => 'Community & Preventive Medicine'],
            ['code' => 'SW',    'th' => 'สังคมสงเคราะห์',                   'en' => 'Social Work'],
        ];

        $hasNameTh = Schema::hasColumn('departments', 'name_th');
        $hasNameEn = Schema::hasColumn('departments', 'name_en');
        $hasName   = Schema::hasColumn('departments', 'name');

        foreach ($rows as $r) {
            if ($hasNameTh && $hasNameEn) {
                Department::updateOrCreate(
                    ['code' => $r['code']],
                    ['name_th' => $r['th'], 'name_en' => $r['en']]
                );
            } elseif ($hasName) {
                Department::updateOrCreate(
                    ['code' => $r['code']],
                    ['name' => $r['th']]
                );
            }
        }
    }
}
