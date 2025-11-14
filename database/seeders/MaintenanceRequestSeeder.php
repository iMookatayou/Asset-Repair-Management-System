<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Models\Asset;
use App\Models\Department;

class MaintenanceRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ===== USERS =====
            $reporter = User::firstWhere('email', 'nurse.ppk@example.com');
            if (!$reporter) {
                $reporter = User::create([
                    'name'     => 'นางสาวอัญชัน ศรีสมบัติ',
                    'email'    => 'nurse.ppk@example.com',
                    'password' => Hash::make('password1234'),
                    'role'     => User::ROLE_COMPUTER_OFFICER,
                    'department' => 'HIM',
                ]);
            }

            $technician = User::firstWhere('email', 'technician.ppk@example.com');
            if (!$technician) {
                $technician = User::create([
                    'name'     => 'นายสมชาย ช่างอาคาร',
                    'email'    => 'technician.ppk@example.com',
                    'password' => Hash::make('password1234'),
                    'role'     => User::ROLE_IT_SUPPORT,
                    'department' => 'FAC',
                ]);
            }

            // ===== DEPARTMENT (ใช้แผนกจริง: Facilities & Maintenance) =====
            $dept = $this->upsertDepartment('FAC', 'อาคารสถานที่/ซ่อมบำรุง', 'Facilities & Maintenance');

            // ===== ASSET =====
            $asset = Asset::firstOrCreate(
                ['asset_code' => 'AC-PPK-302'],
                [
                    'name'          => 'เครื่องปรับอากาศ Panasonic CS-PN9WKT',
                    'type'          => 'เครื่องปรับอากาศ',
                    'brand'         => 'Panasonic',
                    'model'         => 'CS-PN9WKT',
                    'serial_number' => 'PN9WKT-2301-302',
                    'location'      => 'ห้องผู้ป่วย 302',
                    'department_id' => $dept->id,
                    'status'        => 'active',
                ]
            );

            // ===== MAINTENANCE REQUESTS =====
            $requests = [
                [
                    'request_no'    => 'REQ-' . now()->format('ymd') . '-001',
                    'asset_id'      => $asset->id,
                    'reporter_id'   => $reporter->id,
                    'department_id' => $dept->id,
                    'location_text' => 'ห้องผู้ป่วย 302 (เตียง 3)',
                    'title'         => 'แอร์มีเสียงดังและไม่เย็น',
                    'description'   => 'เปิดแล้วมีเสียงดังคล้ายพัดลมเสียดใบพัด ลมไม่เย็น และมีน้ำหยดจากตัวเครื่อง',
                    'priority'      => 'high',
                    'status'        => 'in_progress',
                    'technician_id' => $technician->id,
                    'request_date'  => now()->subDays(3),
                    'assigned_date' => now()->subDays(2),
                    'started_at'    => now()->subDays(2)->addHours(3),
                    'remark'        => 'ตรวจสอบแล้วต้องเปลี่ยน bearing ของพัดลม',
                    'source'        => 'web',
                    'extra'         => json_encode(['contact' => 'ภายใน 302 กด 1423']),
                ],
                [
                    'request_no'    => 'REQ-' . now()->format('ymd') . '-002',
                    'asset_id'      => null,
                    'reporter_id'   => $reporter->id,
                    'department_id' => $dept->id,
                    'location_text' => 'โถงชั้น 2 หน้าห้อง ER',
                    'title'         => 'ไฟฟ้าดับบางจุด',
                    'description'   => 'โซนหน้าห้องฉุกเฉินมีไฟดับ 2 จุด คาดว่าสายไฟชำรุด',
                    'priority'      => 'urgent',
                    'status'        => 'pending',
                    'technician_id' => null,
                    'request_date'  => now()->subDay(),
                    'source'        => 'mobile',
                ],
                [
                    'request_no'       => 'REQ-' . now()->format('ymd') . '-003',
                    'asset_id'         => null,
                    'reporter_id'      => $reporter->id,
                    'department_id'    => $dept->id,
                    'location_text'    => 'ห้องประชุมใหญ่ ชั้น 5',
                    'title'            => 'โปรเจกเตอร์ไม่ติด',
                    'description'      => 'เสียบสายแล้วไม่แสดงภาพ คาดว่า adapter เสีย',
                    'priority'         => 'medium',
                    'status'           => 'resolved',
                    'technician_id'    => $technician->id,
                    'request_date'     => now()->subDays(5),
                    'started_at'       => now()->subDays(5)->addHour(),
                    'resolved_at'      => now()->subDays(4),
                    'resolution_note'  => 'เปลี่ยนสาย HDMI และ adapter ใหม่ ใช้งานได้แล้ว',
                    'cost'             => 450.00,
                    'source'           => 'web',
                ],
            ];

            foreach ($requests as $data) {
                MaintenanceRequest::updateOrCreate(
                    ['request_no' => $data['request_no']],
                    $data
                );
            }
        });
    }

    /**
     * Upsert แผนกแบบรองรับทั้ง schema ใหม่/เก่า
     */
    private function upsertDepartment(string $code, string $nameTh, ?string $nameEn = null): Department
    {
        $hasNameTh = Schema::hasColumn('departments', 'name_th');
        $hasName   = Schema::hasColumn('departments', 'name'); // สคีม่าเก่า

        if ($hasNameTh) {
            return Department::updateOrCreate(
                ['code' => $code],
                ['name_th' => $nameTh, 'name_en' => $nameEn]
            );
        } elseif ($hasName) {
            return Department::updateOrCreate(
                ['code' => $code],
                ['name' => $nameTh]
            );
        }

        // กรณี schema ผิดปกติ
        return Department::firstOrCreate(['code' => $code]);
    }
}
