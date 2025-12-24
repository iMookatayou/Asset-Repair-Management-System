<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\MaintenanceRequest as MR;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection()->disableQueryLog();

        // ===== Config =====
        $assetCount   = (int) env('DEMO_ASSET_COUNT', 120);
        $techCount    = (int) env('DEMO_TECH_COUNT', 6);
        $staffCount   = (int) env('DEMO_MEMBER_COUNT', 18);
        $requestCount = (int) env('DEMO_SEED_COUNT', 300);
        $chunkSize    = (int) env('DEMO_CHUNK', 500);

        // ===== Departments (codes) =====
        $deptCodes     = ['IT','ER','OPD','WARD','ADMIN','LAB'];
        $departmentIds = [];

        if (Schema::hasTable('departments')) {
            $hasCode   = Schema::hasColumn('departments', 'code');
            $hasNameTh = Schema::hasColumn('departments', 'name_th');
            $hasNameEn = Schema::hasColumn('departments', 'name_en');

            if ($hasCode && $hasNameTh && !DB::table('departments')->exists()) {
                $now = now();
                DB::table('departments')->insert([
                    ['code'=>'IT','name_th'=>'ฝ่าย IT & Support','name_en'=>'IT & Support','created_at'=>$now,'updated_at'=>$now],
                    ['code'=>'ER','name_th'=>'ห้องฉุกเฉิน','name_en'=>'Emergency Room','created_at'=>$now,'updated_at'=>$now],
                    ['code'=>'OPD','name_th'=>'ผู้ป่วยนอก','name_en'=>'OPD','created_at'=>$now,'updated_at'=>$now],
                    ['code'=>'WARD','name_th'=>'วอร์ดผู้ป่วยใน','name_en'=>'Ward','created_at'=>$now,'updated_at'=>$now],
                    ['code'=>'ADMIN','name_th'=>'ฝ่ายธุรการ','name_en'=>'Administration','created_at'=>$now,'updated_at'=>$now],
                    ['code'=>'LAB','name_th'=>'ห้องปฏิบัติการ','name_en'=>'Laboratory','created_at'=>$now,'updated_at'=>$now],
                ]);
            }

            if ($hasCode) {
                $codes = DB::table('departments')->pluck('code')->filter()->values()->all();
                if ($codes) $deptCodes = $codes;
            }

            if (Schema::hasColumn('departments', 'id')) {
                $departmentIds = DB::table('departments')->pluck('id')->all();
            }
        }

        // ===== Admin =====
        $adminCitizenId = env('DEMO_ADMIN_CITIZEN_ID', '1000000000001');
        $adminEmail     = 'admin@example.com';

        User::firstOrCreate(
            ['citizen_id' => $adminCitizenId],
            [
                'name'              => 'System Admin',
                'citizen_id'        => $adminCitizenId,
                'email'             => $adminEmail,
                'password'          => bcrypt('Admin123!'),
                'role'              => 'admin',
                'department'        => in_array('IT', $deptCodes, true) ? 'IT' : ($deptCodes[0] ?? null),
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );

        // ===== Users: Technicians / Members =====
        $techDefault = in_array('IT', $deptCodes, true) ? 'IT' : ($deptCodes[0] ?? null);

        $technicians = User::factory()
            ->count($techCount)
            ->state(fn () => [
                'role'       => 'technician',
                'department' => $techDefault,
            ])
            ->create();

        $staffs = User::factory()
            ->count($staffCount)
            ->state(fn () => [
                'role'       => 'member',
                'department' => fake()->randomElement($deptCodes),
            ])
            ->create();

        $techIds  = $technicians->pluck('id')->all();
        $staffIds = $staffs->pluck('id')->all();

        // ===== asset_categories (optional) =====
        $categoryIds = [];
        if (Schema::hasTable('asset_categories')) {
            $hasSlug = Schema::hasColumn('asset_categories', 'slug');

            if (!DB::table('asset_categories')->exists()) {
                $catNames = ['คอมพิวเตอร์','เครื่องพิมพ์','เครื่องปรับอากาศ','โต๊ะทำงาน','หลอดไฟ','เตียงคนไข้'];
                $rows     = [];
                $now      = now();

                $existingSlugs = $hasSlug ? DB::table('asset_categories')->pluck('slug')->filter()->all() : [];
                $slugSet = array_fill_keys($existingSlugs, true);

                $makeSlug = function (string $name) use (&$slugSet) {
                    $base = Str::slug($name, '-');
                    if ($base === '' || $base === null) {
                        $base = trim(preg_replace('/[^a-z0-9]+/i', '-', mb_strtolower($name)), '-');
                    }
                    if ($base === '' || $base === null) {
                        $base = 'cat-'.substr(md5($name.microtime(true)), 0, 6);
                    }
                    $slug = $base;
                    $i    = 2;
                    while (isset($slugSet[$slug])) {
                        $slug = $base.'-'.$i;
                        $i++;
                    }
                    $slugSet[$slug] = true;
                    return $slug;
                };

                foreach ($catNames as $name) {
                    $row = ['name'=>$name,'created_at'=>$now,'updated_at'=>$now];
                    if ($hasSlug) $row['slug'] = $makeSlug($name);
                    $rows[] = $row;
                }

                DB::table('asset_categories')->insert($rows);
            }

            $categoryIds = DB::table('asset_categories')->pluck('id')->all();
        }

        // ===== Assets (seed เฉพาะตอนว่าง) =====
        if (Schema::hasTable('assets') && !DB::table('assets')->exists()) {
            $types     = ['เครื่องใช้ไฟฟ้า','อุปกรณ์สำนักงาน','คอมพิวเตอร์','เครื่องมือแพทย์'];
            $brands    = ['HP','Dell','Acer','Lenovo','Brother','Mitsubishi','Daikin'];
            $locations = ['ER','OPD','Ward','Admin','IT Room','Lab'];

            $hasType         = Schema::hasColumn('assets', 'type');
            $hasBrand        = Schema::hasColumn('assets', 'brand');
            $hasModel        = Schema::hasColumn('assets', 'model');
            $hasSerial       = Schema::hasColumn('assets', 'serial_number');
            $hasLocation     = Schema::hasColumn('assets', 'location');
            $hasDeptId       = Schema::hasColumn('assets', 'department_id');
            $hasCategoryId   = Schema::hasColumn('assets', 'category_id');
            $hasPurchaseDate = Schema::hasColumn('assets', 'purchase_date');
            $hasWarranty     = Schema::hasColumn('assets', 'warranty_expire');
            $hasStatus       = Schema::hasColumn('assets', 'status');
            $hasAssetCode    = Schema::hasColumn('assets', 'asset_code');
            $hasName         = Schema::hasColumn('assets', 'name');

            $assetRows = [];
            $nowTs     = now();
            $usedCodes = [];
            $usedSNs   = [];

            for ($i = 1; $i <= $assetCount; $i++) {
                do { $code = 'ASSET-'.random_int(10000, 99999); } while (isset($usedCodes[$code]));
                $usedCodes[$code] = true;

                do { $sn = 'SN'.random_int(10000000, 99999999); } while (isset($usedSNs[$sn]));
                $usedSNs[$sn] = true;

                $purchaseAt = Carbon::now()->subMonths(random_int(6, 48))->startOfDay();
                $warrantyAt = (clone $purchaseAt)->addMonths(random_int(12, 48));

                $row = ['created_at'=>$nowTs,'updated_at'=>$nowTs];

                if ($hasAssetCode)    $row['asset_code']      = $code;
                if ($hasName)         $row['name']            = fake()->words(2, true);
                if ($hasType)         $row['type']            = $types[array_rand($types)];
                if ($hasBrand)        $row['brand']           = $brands[array_rand($brands)];
                if ($hasModel)        $row['model']           = strtoupper(fake()->bothify('??-###'));
                if ($hasSerial)       $row['serial_number']   = $sn;
                if ($hasLocation)     $row['location']        = $locations[array_rand($locations)];
                if ($hasDeptId && $departmentIds)   $row['department_id'] = $departmentIds[array_rand($departmentIds)];
                if ($hasCategoryId && $categoryIds) $row['category_id']   = $categoryIds[array_rand($categoryIds)];
                if ($hasPurchaseDate) $row['purchase_date']   = $purchaseAt;
                if ($hasWarranty)     $row['warranty_expire'] = $warrantyAt;

                if ($hasStatus) {
                    $roll          = mt_rand(1, 100);
                    $row['status'] = $roll <= 75 ? 'active' : ($roll <= 95 ? 'in_repair' : 'disposed');
                }

                $assetRows[] = $row;

                if (count($assetRows) >= $chunkSize) {
                    DB::table('assets')->insert($assetRows);
                    $assetRows = [];
                }
            }

            if ($assetRows) DB::table('assets')->insert($assetRows);
        }

        $assetIds = Schema::hasTable('assets') ? DB::table('assets')->pluck('id')->all() : [];

        // ===== Maintenance Requests flags =====
        $mrTable = 'maintenance_requests';

        $hasAssetId           = Schema::hasColumn($mrTable, 'asset_id');
        $hasReporterId        = Schema::hasColumn($mrTable, 'reporter_id');
        $hasTechnicianId      = Schema::hasColumn($mrTable, 'technician_id');

        $hasRequestNo         = Schema::hasColumn($mrTable, 'request_no');
        $hasDeptMR            = Schema::hasColumn($mrTable, 'department_id');

        $hasTitle             = Schema::hasColumn($mrTable, 'title');
        $hasDescription       = Schema::hasColumn($mrTable, 'description');
        $hasPriority          = Schema::hasColumn($mrTable, 'priority');
        $hasStatusCol         = Schema::hasColumn($mrTable, 'status');

        $hasReporterName      = Schema::hasColumn($mrTable, 'reporter_name');
        $hasReporterPhone     = Schema::hasColumn($mrTable, 'reporter_phone');
        $hasReporterEmail     = Schema::hasColumn($mrTable, 'reporter_email');
        $hasReporterPosition  = Schema::hasColumn($mrTable, 'reporter_position');
        $hasReporterIp        = Schema::hasColumn($mrTable, 'reporter_ip');
        $hasReporterPort      = Schema::hasColumn($mrTable, 'reporter_port');
        $hasReporterApp       = Schema::hasColumn($mrTable, 'reporter_app');
        $hasLegacyPayload     = Schema::hasColumn($mrTable, 'legacy_payload');

        $hasLocationText      = Schema::hasColumn($mrTable, 'location_text');

        $hasRequestDate       = Schema::hasColumn($mrTable, 'request_date');
        $hasAssignedDate      = Schema::hasColumn($mrTable, 'assigned_date');
        $hasCompletedDate     = Schema::hasColumn($mrTable, 'completed_date');
        $hasAcceptedAt        = Schema::hasColumn($mrTable, 'accepted_at');
        $hasStartedAt         = Schema::hasColumn($mrTable, 'started_at');
        $hasOnHoldAt          = Schema::hasColumn($mrTable, 'on_hold_at');
        $hasResolvedAt        = Schema::hasColumn($mrTable, 'resolved_at');
        $hasClosedAt          = Schema::hasColumn($mrTable, 'closed_at');

        $hasRemark            = Schema::hasColumn($mrTable, 'remark');
        $hasResolutionNote    = Schema::hasColumn($mrTable, 'resolution_note');
        $hasCost              = Schema::hasColumn($mrTable, 'cost');
        $hasSource            = Schema::hasColumn($mrTable, 'source');
        $hasExtra             = Schema::hasColumn($mrTable, 'extra');

        $hasCreatedAt         = Schema::hasColumn($mrTable, 'created_at');
        $hasUpdatedAt         = Schema::hasColumn($mrTable, 'updated_at');

        $statuses = [
            MR::STATUS_PENDING,
            MR::STATUS_ACCEPTED,
            MR::STATUS_IN_PROGRESS,
            MR::STATUS_ON_HOLD,
            MR::STATUS_RESOLVED,
            MR::STATUS_CLOSED,
            MR::STATUS_CANCELLED,
        ];

        $priorities = [
            MR::PRIORITY_LOW,
            MR::PRIORITY_MEDIUM,
            MR::PRIORITY_HIGH,
            MR::PRIORITY_URGENT,
        ];

        $now = Carbon::now();

        // timeline (ง่าย + ตรงกับ controller)
        $makeTimeline = function (string $status, Carbon $base) {
            $assigned = $accepted = $started = $onHold = $resolved = $closed = $completedDate = null;

            if (in_array($status, ['accepted','in_progress','on_hold','resolved','closed'], true)) {
                $assigned = (clone $base)->addDays(random_int(0, 3));
                $accepted = (clone $assigned)->addHours(random_int(0, 36));
            }

            if (in_array($status, ['in_progress','on_hold','resolved','closed'], true)) {
                $started = (clone ($accepted ?? $base))->addHours(random_int(1, 24));
            }

            if ($status === 'on_hold') {
                $onHold = (clone ($started ?? $accepted ?? $base))->addHours(random_int(2, 48));
            }

            if (in_array($status, ['resolved','closed'], true)) {
                $resolved = (clone ($onHold ?? $started ?? $accepted ?? $base))->addHours(random_int(2, 72));
            }

            if ($status === 'closed') {
                $closed = (clone ($resolved ?? $base))->addHours(random_int(1, 24));
                $completedDate = $closed; // ✅ ให้ completed_date = closed_at
            }

            return [$assigned, $accepted, $started, $onHold, $resolved, $closed, $completedDate];
        };

        // ===== request_no generator (LEGACY: YY + TYPE(2) + RUN(5)) =====
        $existingSet  = [];
        $yearCounters = [];

        if (Schema::hasTable($mrTable) && $hasRequestNo) {
            $existing = DB::table($mrTable)->pluck('request_no')->filter()->all();
            foreach ($existing as $no) {
                $existingSet[$no] = true;
                if (preg_match('/^(\d{2})(\d{2})(\d{5})$/', (string) $no, $m)) {
                    $yy   = $m[1];
                    $type = $m[2];
                    $seq  = (int) $m[3];
                    $key  = $yy.$type;
                    $yearCounters[$key] = max($yearCounters[$key] ?? 0, $seq);
                }
            }
        }

        $usedInRun = [];

        $makeRequestNo = function (Carbon $date) use (&$yearCounters, &$existingSet, &$usedInRun) {
            $beYear = $date->year + 543;
            $yy     = substr((string) $beYear, -2);
            $type   = '10';

            $key     = $yy.$type;
            $lastSeq = $yearCounters[$key] ?? 0;

            do {
                $lastSeq++;
                $candidate = $yy.$type.sprintf('%05d', $lastSeq);
            } while (isset($existingSet[$candidate]) || isset($usedInRun[$candidate]));

            $yearCounters[$key] = $lastSeq;
            $existingSet[$candidate] = true;
            $usedInRun[$candidate]   = true;

            return $candidate;
        };

        // ===== Seed maintenance_requests =====
        DB::transaction(function () use (
            $requestCount, $assetIds, $staffIds, $techIds, $priorities, $statuses, $now, $chunkSize, $departmentIds,
            $makeTimeline, $makeRequestNo,
            $hasAssetId, $hasReporterId, $hasTechnicianId,
            $hasRequestNo, $hasDeptMR,
            $hasTitle, $hasDescription, $hasPriority, $hasStatusCol,
            $hasReporterName, $hasReporterPhone, $hasReporterEmail, $hasReporterPosition, $hasReporterIp, $hasReporterPort, $hasReporterApp, $hasLegacyPayload,
            $hasLocationText,
            $hasRequestDate, $hasAssignedDate, $hasCompletedDate, $hasAcceptedAt, $hasStartedAt, $hasOnHoldAt, $hasResolvedAt, $hasClosedAt,
            $hasRemark, $hasResolutionNote, $hasCost, $hasSource, $hasExtra,
            $hasCreatedAt, $hasUpdatedAt
        ) {
            $insertCols = [];

            if ($hasAcceptedAt)      $insertCols[] = 'accepted_at';
            if ($hasAssetId)         $insertCols[] = 'asset_id';
            if ($hasAssignedDate)    $insertCols[] = 'assigned_date';
            if ($hasClosedAt)        $insertCols[] = 'closed_at';
            if ($hasCompletedDate)   $insertCols[] = 'completed_date';
            if ($hasCost)            $insertCols[] = 'cost';
            if ($hasCreatedAt)       $insertCols[] = 'created_at';
            if ($hasDeptMR)          $insertCols[] = 'department_id';
            if ($hasDescription)     $insertCols[] = 'description';
            if ($hasExtra)           $insertCols[] = 'extra';
            if ($hasLegacyPayload)   $insertCols[] = 'legacy_payload';
            if ($hasLocationText)    $insertCols[] = 'location_text';
            if ($hasOnHoldAt)        $insertCols[] = 'on_hold_at';
            if ($hasPriority)        $insertCols[] = 'priority';
            if ($hasRemark)          $insertCols[] = 'remark';
            if ($hasReporterApp)     $insertCols[] = 'reporter_app';
            if ($hasReporterEmail)   $insertCols[] = 'reporter_email';
            if ($hasReporterId)      $insertCols[] = 'reporter_id';
            if ($hasReporterIp)      $insertCols[] = 'reporter_ip';
            if ($hasReporterName)    $insertCols[] = 'reporter_name';
            if ($hasReporterPhone)   $insertCols[] = 'reporter_phone';
            if ($hasReporterPort)    $insertCols[] = 'reporter_port';
            if ($hasReporterPosition)$insertCols[] = 'reporter_position';
            if ($hasRequestDate)     $insertCols[] = 'request_date';
            if ($hasRequestNo)       $insertCols[] = 'request_no';
            if ($hasResolutionNote)  $insertCols[] = 'resolution_note';
            if ($hasResolvedAt)      $insertCols[] = 'resolved_at';
            if ($hasSource)          $insertCols[] = 'source';
            if ($hasStartedAt)       $insertCols[] = 'started_at';
            if ($hasStatusCol)       $insertCols[] = 'status';
            if ($hasTechnicianId)    $insertCols[] = 'technician_id';
            if ($hasTitle)           $insertCols[] = 'title';
            if ($hasUpdatedAt)       $insertCols[] = 'updated_at';

            $rows = [];

            for ($i = 1; $i <= $requestCount; $i++) {
                $createdAt = (clone $now)
                    ->subMonths(random_int(0, 11))
                    ->subDays(random_int(0, 28))
                    ->setTime(random_int(8, 17), random_int(0, 59));

                $status   = $statuses[array_rand($statuses)];
                $priority = $priorities[array_rand($priorities)];
                $assetId  = $assetIds ? $assetIds[array_rand($assetIds)] : null;
                $reporter = $staffIds ? $staffIds[array_rand($staffIds)] : null;

                // ผู้แจ้งภายนอก 10%
                $isExternal = random_int(1, 100) <= 10;
                if ($isExternal) $reporter = null;

                // ✅ pending/cancelled ต้องว่าง เพื่อให้รับงานได้จริง
                $techId = null;
                if (!in_array($status, ['pending','cancelled'], true)) {
                    $techId = $techIds ? $techIds[array_rand($techIds)] : null;
                }

                [$assigned,$accepted,$started,$onHold,$resolved,$closed,$completedDate] = $makeTimeline($status, $createdAt);

                $row = array_fill_keys($insertCols, null);

                if ($hasAssetId)      $row['asset_id'] = $assetId;
                if ($hasReporterId)   $row['reporter_id'] = $reporter;
                if ($hasTechnicianId) $row['technician_id'] = $techId;

                if ($hasRequestNo) $row['request_no'] = $makeRequestNo($createdAt);

                if ($hasTitle)       $row['title']       = 'แจ้งซ่อม #'.$i;
                if ($hasDescription) $row['description'] = 'รายละเอียดปัญหาเบื้องต้น';

                if ($hasPriority)  $row['priority'] = $priority;
                if ($hasStatusCol) $row['status']   = $status;

                if ($hasDeptMR && $departmentIds) {
                    $row['department_id'] = $departmentIds[array_rand($departmentIds)];
                }

                if ($hasLocationText) {
                    $row['location_text'] = fake()->randomElement([
                        'ตึก A ชั้น 2',
                        'ตึก B ห้อง IT',
                        'หน้า ER',
                        'Ward 3',
                        'OPD 5',
                    ]);
                }

                if ($isExternal) {
                    if ($hasReporterName)     $row['reporter_name']  = fake()->name();
                    if ($hasReporterPhone)    $row['reporter_phone'] = fake()->numerify('08########');
                    if ($hasReporterEmail)    $row['reporter_email'] = fake()->safeEmail();
                    if ($hasReporterPosition) $row['reporter_position'] = fake()->jobTitle();
                }

                if ($hasReporterIp) {
                    $row['reporter_ip'] = fake()->randomElement([
                        '172.16.'.random_int(1, 254).'.'.random_int(1, 254),
                        '10.'.random_int(0, 255).'.'.random_int(0, 255).'.'.random_int(1, 254),
                    ]);
                }

                if ($hasReporterPort) {
                    $p = (string) fake()->randomElement(['', '80', '443', '5405']);
                    $row['reporter_port'] = $p === '' ? null : $p;
                }

                if ($hasReporterApp) {
                    $row['reporter_app'] = fake()->randomElement(['web','HIS','EXCEL','WORD','Chrome','Edge']);
                }

                if ($hasLegacyPayload) {
                    $row['legacy_payload'] = json_encode([
                        'ua' => fake()->randomElement(['Chrome','Edge','Firefox']),
                        'tz' => 'Asia/Bangkok',
                        'seed' => true,
                        'external' => $isExternal,
                    ]);
                }

                if ($hasRequestDate)   $row['request_date']   = $createdAt;
                if ($hasAssignedDate)  $row['assigned_date']  = $assigned;
                if ($hasAcceptedAt)    $row['accepted_at']    = $accepted;
                if ($hasStartedAt)     $row['started_at']     = $started;
                if ($hasOnHoldAt)      $row['on_hold_at']     = $onHold;
                if ($hasResolvedAt)    $row['resolved_at']    = $resolved;
                if ($hasClosedAt)      $row['closed_at']      = $closed;
                if ($hasCompletedDate) $row['completed_date'] = $completedDate;

                if ($hasRemark) {
                    $row['remark'] = match ($status) {
                        'pending'      => null,
                        'accepted'     => 'รับเข้าคิวแล้ว',
                        'in_progress'  => 'กำลังดำเนินการ',
                        'on_hold'      => 'รอชิ้นส่วน/ช่างเฉพาะทาง',
                        'resolved'     => 'แก้เสร็จ รอปิดงาน',
                        'closed'       => 'ปิดงานเรียบร้อย',
                        'cancelled'    => 'ผู้แจ้งยกเลิก',
                        default        => null,
                    };
                }

                if ($hasResolutionNote && in_array($status, ['resolved','closed'], true)) {
                    $row['resolution_note'] = fake()->sentence(8);
                }

                if ($hasCost && in_array($status, ['resolved','closed'], true)) {
                    $row['cost'] = fake()->randomFloat(2, 200, 8000);
                }

                if ($hasSource) $row['source'] = 'web';
                if ($hasExtra)  $row['extra']  = null;

                $updatedAt = $closed ?? $resolved ?? $onHold ?? $started ?? $accepted ?? $assigned ?? $createdAt;
                if ($hasCreatedAt) $row['created_at'] = $createdAt;
                if ($hasUpdatedAt) $row['updated_at'] = $updatedAt;

                $rows[] = $row;

                if (count($rows) >= $chunkSize) {
                    DB::table('maintenance_requests')->insert($rows);
                    $rows = [];
                }
            }

            if ($rows) DB::table('maintenance_requests')->insert($rows);
        });

        // ===== Maintenance Assignments =====
        if (Schema::hasTable('maintenance_assignments')) {
            $reqs = DB::table('maintenance_requests')
                ->select('id','technician_id','status','assigned_date','accepted_at','request_date','created_at')
                ->whereNotNull('technician_id')
                ->get();

            $nowTs = now();
            $asgRows = [];

            foreach ($reqs as $r) {
                $assignedAt = $r->assigned_date ?? $r->accepted_at ?? $r->request_date ?? $r->created_at ?? $nowTs;

                $asgRows[] = [
                    'maintenance_request_id' => $r->id,
                    'user_id'                => $r->technician_id,
                    'role'                   => 'technician',
                    'is_lead'                => true,
                    'assigned_at'            => $assignedAt,
                    'status'                 => match ($r->status) {
                        'resolved','closed' => 'done',
                        'cancelled'         => 'cancelled',
                        default             => 'in_progress',
                    },
                    'created_at'             => $nowTs,
                    'updated_at'             => $nowTs,
                ];

                if (count($asgRows) >= 1000) {
                    DB::table('maintenance_assignments')->upsert(
                        $asgRows,
                        ['maintenance_request_id','user_id'],
                        ['role','is_lead','assigned_at','status','updated_at']
                    );
                    $asgRows = [];
                }
            }

            if ($asgRows) {
                DB::table('maintenance_assignments')->upsert(
                    $asgRows,
                    ['maintenance_request_id','user_id'],
                    ['role','is_lead','assigned_at','status','updated_at']
                );
            }
        }

        // ===== Maintenance Operation Logs (upsert + join assets for property_code) =====
        if (Schema::hasTable('maintenance_operation_logs')) {
            $hasPropertyCode = Schema::hasColumn('maintenance_operation_logs', 'property_code');

            $target = DB::table('maintenance_requests as mr')
                ->leftJoin('assets as a', 'a.id', '=', 'mr.asset_id')
                ->select(
                    'mr.id',
                    'mr.technician_id',
                    'mr.resolution_note',
                    'mr.resolved_at',
                    'mr.closed_at',
                    'mr.started_at',
                    'mr.request_date',
                    'mr.created_at',
                    'a.asset_code as asset_code'
                )
                ->whereIn('mr.status', ['resolved','closed'])
                ->inRandomOrder()
                ->limit((int) floor($requestCount * 0.6))
                ->get();

            $nowTs   = now();
            $methods = ['requisition','service_fee','other'];
            $opRows  = [];

            foreach ($target as $r) {
                $userId = $r->technician_id ?: (!empty($techIds) ? $techIds[array_rand($techIds)] : null);
                if (!$userId) continue;

                $base = $r->resolved_at ?? $r->closed_at ?? $r->started_at ?? $r->request_date ?? $r->created_at ?? $nowTs;
                $operationDate = Carbon::parse($base)->startOfDay();

                $opRows[] = [
                    'maintenance_request_id' => $r->id,
                    'user_id'                => $userId,
                    'operation_date'         => $operationDate,
                    'operation_method'       => $methods[array_rand($methods)],
                    'property_code'          => $hasPropertyCode ? ($r->asset_code ?? null) : null,
                    'require_precheck'       => (bool) random_int(0, 1),
                    'remark'                 => $r->resolution_note ?: fake()->sentence(10),
                    'issue_software'         => (bool) random_int(0, 1),
                    'issue_hardware'         => (bool) random_int(0, 1),
                    'created_at'             => $nowTs,
                    'updated_at'             => $nowTs,
                ];
            }

            if ($opRows) {
                DB::table('maintenance_operation_logs')->upsert(
                    $opRows,
                    ['maintenance_request_id'],
                    ['user_id','operation_date','operation_method','property_code','require_precheck','remark','issue_software','issue_hardware','updated_at']
                );
            }
        }
    }
}
