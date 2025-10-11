<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\{Asset, MaintenanceRequest};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1) สร้างผู้ใช้กลุ่มผู้แจ้ง + ช่าง
        $reporters = User::factory()->count(15)->create([
            'role' => 'staff',
        ]);

        $technicians = User::factory()->count(6)->create([
            'role' => 'technician',
        ]);

        // 2) สร้างทรัพย์สิน
        $types = ['เครื่องใช้ไฟฟ้า','อุปกรณ์สำนักงาน','คอมพิวเตอร์','เครื่องมือแพทย์'];
        $categories = ['คอมพิวเตอร์','เครื่องพิมพ์','เครื่องปรับอากาศ','โต๊ะทำงาน','หลอดไฟ','เตียงคนไข้'];
        $locations = ['ER','OPD','Ward','Admin','IT Room','Lab'];

        $assets = Asset::factory()
            ->count(rand(80,120))
            ->create()
            ->each(function($a) use ($types,$categories,$locations) {
                // ถ้า schema มีคอลัมน์เหล่านี้ จะอัปเดตให้สมจริง
                $dirty = false;
                if (Schema::hasColumn('assets','type')) { $a->type = collect($types)->random(); $dirty=true; }
                if (Schema::hasColumn('assets','category')) { $a->category = collect($categories)->random(); $dirty=true; }
                if (Schema::hasColumn('assets','location')) { $a->location = collect($locations)->random(); $dirty=true; }
                if ($dirty) $a->save();
            });

        // 3) ใบงาน ~300 รายการ กระจาย 12 เดือน และ "มี reporter_id/technician_id เสมอ"
        $statuses = [
            MaintenanceRequest::STATUS_PENDING,
            MaintenanceRequest::STATUS_IN_PROGRESS,
            MaintenanceRequest::STATUS_COMPLETED,
        ];
        $priorities = [
            MaintenanceRequest::PRIORITY_LOW,
            MaintenanceRequest::PRIORITY_MEDIUM,
            MaintenanceRequest::PRIORITY_HIGH,
            MaintenanceRequest::PRIORITY_URGENT,
        ];

        foreach (range(1,300) as $i) {
            $createdAt = Carbon::now()->subMonths(rand(0,11))->subDays(rand(0,28));
            $status = collect($statuses)->random();

            $asset = $assets->random();
            $reporter = $reporters->random();     // << สำคัญ: ต้องมีค่า
            $technician = $technicians->random(); // << สำคัญ: ต้องมีค่า

            MaintenanceRequest::create([
                'asset_id'       => $asset->id,
                'reporter_id'    => $reporter->id,    // << ห้าม null
                'title'          => 'แจ้งซ่อม '.$asset->name,
                'description'    => 'รายละเอียดปัญหาของ '.($asset->category ?? 'อุปกรณ์'),
                'priority'       => collect($priorities)->random(),
                'status'         => $status,
                'technician_id'  => $technician->id,  // << ห้าม null ถ้า schema บังคับ
                'request_date'   => $createdAt,
                'assigned_date'  => $createdAt->copy()->addDays(rand(0,5)),
                'completed_date' => $status === MaintenanceRequest::STATUS_COMPLETED
                    ? $createdAt->copy()->addDays(rand(3,15))
                    : null,
                'remark'         => $status === MaintenanceRequest::STATUS_COMPLETED ? 'ดำเนินการแล้วเสร็จ' : null,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ]);
        }
    }
}
