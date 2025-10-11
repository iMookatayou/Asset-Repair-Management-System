<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Asset;
use App\Models\MaintenanceRequest;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ============= Tuning =============
        DB::connection()->disableQueryLog();          // กันเมมบวม
        $countRequests = (int) env('DEMO_SEED_COUNT', 300);
        $assetCount    = (int) env('DEMO_ASSET_COUNT', rand(80,120));
        $chunkSize     = 500;                         // ปรับได้: 200–1000
        // ==================================

        // 1) Users
        $reporters    = User::factory(15)->create(['role' => 'staff']);
        $technicians  = User::factory(6)->create(['role' => 'technician']);

        // เก็บ id ไว้ใน array เพื่อลด memory จาก Eloquent Collection หนา ๆ
        $reporterIds   = $reporters->pluck('id')->all();
        $technicianIds = $technicians->pluck('id')->all();

        // 2) Assets
        $assets = Asset::factory($assetCount)->create();

        $assetIds = $assets->pluck('id')->all();
        $types       = ['เครื่องใช้ไฟฟ้า','อุปกรณ์สำนักงาน','คอมพิวเตอร์','เครื่องมือแพทย์'];
        $categories  = ['คอมพิวเตอร์','เครื่องพิมพ์','เครื่องปรับอากาศ','โต๊ะทำงาน','หลอดไฟ','เตียงคนไข้'];
        $locations   = ['ER','OPD','Ward','Admin','IT Room','Lab'];

        // เติมฟิลด์เสริมให้ assets เท่าที่ schema มี (แบบเบา ๆ)
        $hasType      = Schema::hasColumn('assets','type');
        $hasCategory  = Schema::hasColumn('assets','category');
        $hasLocation  = Schema::hasColumn('assets','location');

        foreach ($assets as $a) {
            $dirty = false;
            if ($hasType)     { $a->type = $types[array_rand($types)]; $dirty = true; }
            if ($hasCategory) { $a->category = $categories[array_rand($categories)]; $dirty = true; }
            if ($hasLocation) { $a->location = $locations[array_rand($locations)]; $dirty = true; }
            if ($dirty) $a->save();
        }

        // 3) Maintenance Requests — ใช้ bulk insert
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

        $now = Carbon::now();
        $rows = [];
        $hasRemark        = Schema::hasColumn('maintenance_requests', 'remark');
        $hasAssignedDate  = Schema::hasColumn('maintenance_requests', 'assigned_date');
        $hasCompletedDate = Schema::hasColumn('maintenance_requests', 'completed_date');

        DB::transaction(function () use (
            $countRequests, $assetIds, $reporterIds, $technicianIds,
            $statuses, $priorities, $now, $hasRemark, $hasAssignedDate, $hasCompletedDate, &$rows, $chunkSize
        ) {
            for ($i = 0; $i < $countRequests; $i++) {
                $createdAt = (clone $now)->subMonths(random_int(0, 11))->subDays(random_int(0, 28));
                $status    = $statuses[array_rand($statuses)];
                $assetId   = $assetIds[array_rand($assetIds)];
                $reporter  = $reporterIds[array_rand($reporterIds)];
                $tech      = $technicianIds[array_rand($technicianIds)];

                $row = [
                    'asset_id'      => $assetId,
                    'reporter_id'   => $reporter,
                    'technician_id' => $tech,
                    'title'         => 'แจ้งซ่อม #'.($i+1),
                    'description'   => 'รายละเอียดปัญหาเบื้องต้น',
                    'priority'      => $priorities[array_rand($priorities)],
                    'status'        => $status,
                    'request_date'  => $createdAt,
                    'created_at'    => $createdAt,
                    'updated_at'    => $createdAt,
                ];

                if ($hasAssignedDate)  $row['assigned_date']  = (clone $createdAt)->addDays(random_int(0,5));
                if ($hasCompletedDate) $row['completed_date'] = ($status === MaintenanceRequest::STATUS_COMPLETED)
                    ? (clone $createdAt)->addDays(random_int(3,15))
                    : null;
                if ($hasRemark)        $row['remark'] = $status === MaintenanceRequest::STATUS_COMPLETED ? 'ดำเนินการแล้วเสร็จ' : null;

                $rows[] = $row;

                if (count($rows) >= $chunkSize) {
                    DB::table('maintenance_requests')->insert($rows);
                    $rows = [];
                }
            }

            if (!empty($rows)) {
                DB::table('maintenance_requests')->insert($rows);
            }
        });
    }
}
