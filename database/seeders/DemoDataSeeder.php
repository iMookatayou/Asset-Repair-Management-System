<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\MaintenanceRequest as MR;
use App\Models\MaintenanceOperationLog;
use App\Models\MaintenanceAssignment;
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
        $staffCount   = (int) env('DEMO_MEMBER_COUNT', 18); // legacy STAFF renamed to Member
        $requestCount = (int) env('DEMO_SEED_COUNT', 300);
        $chunkSize    = (int) env('DEMO_CHUNK', 500);

        // ===== Departments (codes) =====
        $deptCodes     = ['IT','ER','OPD','WARD','ADMIN','LAB'];
        $departmentIds = [];

        if (Schema::hasTable('departments')) {
            $hasCode   = Schema::hasColumn('departments', 'code');
            $hasNameTh = Schema::hasColumn('departments', 'name_th');
            $hasNameEn = Schema::hasColumn('departments', 'name_en');

            // Bootstrap departments ถ้ายังไม่มีข้อมูล
            if ($hasCode && $hasNameTh && !DB::table('departments')->exists()) {
                $now = now();
                DB::table('departments')->insert([
                    [
                        'code'       => 'IT',
                        'name_th'    => 'ฝ่าย IT & Support',
                        'name_en'    => 'IT & Support',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'code'       => 'ER',
                        'name_th'    => 'ห้องฉุกเฉิน',
                        'name_en'    => 'Emergency Room',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'code'       => 'OPD',
                        'name_th'    => 'ผู้ป่วยนอก',
                        'name_en'    => 'OPD',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'code'       => 'WARD',
                        'name_th'    => 'วอร์ดผู้ป่วยใน',
                        'name_en'    => 'Ward',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'code'       => 'ADMIN',
                        'name_th'    => 'ฝ่ายธุรการ',
                        'name_en'    => 'Administration',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'code'       => 'LAB',
                        'name_th'    => 'ห้องปฏิบัติการ',
                        'name_en'    => 'Laboratory',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ]);
            }

            // ใช้ codes จาก DB ถ้ามี
            if ($hasCode) {
                $codes = DB::table('departments')->pluck('code')->filter()->values()->all();
                if ($codes) {
                    $deptCodes = $codes;
                }
            }

            if (Schema::hasColumn('departments', 'id')) {
                $departmentIds = DB::table('departments')->pluck('id')->all();
            }
        }

        // ===== Admin (ถ้ายังไม่มี) =====
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'System Admin',
                'password'          => bcrypt('Admin123!'),
                'role'              => 'admin',
                'department'        => in_array('IT', $deptCodes, true) ? 'IT' : ($deptCodes[0] ?? null),
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );

        // ===== Users: Technicians / Members (legacy staff) =====
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
                // legacy 'staff' replaced by 'member'
                'role'       => 'member',
                'department' => fake()->randomElement($deptCodes),
            ])
            ->create();

        $techIds  = $technicians->pluck('id')->all();
        $staffIds = $staffs->pluck('id')->all(); // member ids

        // ===== asset_categories (ถ้ามี) =====
        $categoryIds = [];

        if (Schema::hasTable('asset_categories')) {
            $hasSlug = Schema::hasColumn('asset_categories', 'slug');

            if (!DB::table('asset_categories')->exists()) {
                $catNames = ['คอมพิวเตอร์','เครื่องพิมพ์','เครื่องปรับอากาศ','โต๊ะทำงาน','หลอดไฟ','เตียงคนไข้'];
                $rows     = [];
                $now      = now();

                $existingSlugs = $hasSlug
                    ? DB::table('asset_categories')->pluck('slug')->filter()->all()
                    : [];

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
                    $row = [
                        'name'       => $name,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($hasSlug) {
                        $row['slug'] = $makeSlug($name);
                    }

                    $rows[] = $row;
                }

                DB::table('asset_categories')->insert($rows);
            }

            $categoryIds = DB::table('asset_categories')->pluck('id')->all();
        }

        // ===== Assets =====
        $types     = ['เครื่องใช้ไฟฟ้า','อุปกรณ์สำนักงาน','คอมพิวเตอร์','เครื่องมือแพทย์']; // mapped to assets.type
        $brands    = ['HP','Dell','Acer','Lenovo','Brother','Mitsubishi','Daikin'];
        $locations = ['ER','OPD','Ward','Admin','IT Room','Lab'];

        $hasType         = Schema::hasColumn('assets', 'type');
        $hasBrand        = Schema::hasColumn('assets', 'brand');
        $hasModel        = Schema::hasColumn('assets', 'model');
        $hasSerial       = Schema::hasColumn('assets', 'serial_number');
        $hasLocation     = Schema::hasColumn('assets', 'location');
        $hasDeptId       = Schema::hasColumn('assets', 'department_id');
        $hasCategoryId   = Schema::hasColumn('assets', 'category_id'); // legacy string 'category' ignored
        $hasPurchaseDate = Schema::hasColumn('assets', 'purchase_date');
        $hasWarranty     = Schema::hasColumn('assets', 'warranty_expire');
        $hasStatus       = Schema::hasColumn('assets', 'status');
        $hasAssetCode    = Schema::hasColumn('assets', 'asset_code');
        $hasName         = Schema::hasColumn('assets', 'name');

        $assetRows = [];
        $now       = now();

        $usedCodes = [];
        $usedSNs   = [];

        for ($i = 1; $i <= $assetCount; $i++) {
            do {
                $code = 'ASSET-'.random_int(10000, 99999);
            } while (isset($usedCodes[$code]));
            $usedCodes[$code] = true;

            do {
                $sn = 'SN'.random_int(10000000, 99999999);
            } while (isset($usedSNs[$sn]));
            $usedSNs[$sn] = true;

            $purchaseAt = Carbon::now()->subMonths(random_int(6, 48))->startOfDay();
            $warrantyAt = (clone $purchaseAt)->addMonths(random_int(12, 48));

            $row = [
                'created_at' => $now,
                'updated_at' => $now,
            ];

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
                $row['status'] = $roll <= 75
                    ? 'active'
                    : ($roll <= 95 ? 'in_repair' : 'disposed');
            }

            $assetRows[] = $row;

            if (count($assetRows) >= $chunkSize) {
                DB::table('assets')->insert($assetRows);
                $assetRows = [];
            }
        }

        if ($assetRows) {
            DB::table('assets')->insert($assetRows);
        }

        $assetIds = DB::table('assets')->pluck('id')->all();

        // ===== Maintenance Requests =====
        $hasRequestNo     = Schema::hasColumn('maintenance_requests', 'request_no');
        $hasReporterName  = Schema::hasColumn('maintenance_requests', 'reporter_name');
        $hasReporterPhone = Schema::hasColumn('maintenance_requests', 'reporter_phone');
        $hasReporterEmail = Schema::hasColumn('maintenance_requests', 'reporter_email');
        $hasDeptMR        = Schema::hasColumn('maintenance_requests', 'department_id');
        $hasLocationText  = Schema::hasColumn('maintenance_requests', 'location_text');
        $hasPriority      = Schema::hasColumn('maintenance_requests', 'priority');
        $hasStatusCol     = Schema::hasColumn('maintenance_requests', 'status');
        $hasTechnicianId  = Schema::hasColumn('maintenance_requests', 'technician_id');
        $hasRequestDate   = Schema::hasColumn('maintenance_requests', 'request_date');
        $hasAssignedDate  = Schema::hasColumn('maintenance_requests', 'assigned_date');
        $hasCompletedDate = Schema::hasColumn('maintenance_requests', 'completed_date');
        $hasAcceptedAt    = Schema::hasColumn('maintenance_requests', 'accepted_at');
        $hasStartedAt     = Schema::hasColumn('maintenance_requests', 'started_at');
        $hasOnHoldAt      = Schema::hasColumn('maintenance_requests', 'on_hold_at');
        $hasResolvedAt    = Schema::hasColumn('maintenance_requests', 'resolved_at');
        $hasClosedAt      = Schema::hasColumn('maintenance_requests', 'closed_at');
        $hasRemark        = Schema::hasColumn('maintenance_requests', 'remark');
        $hasResolutionNote= Schema::hasColumn('maintenance_requests', 'resolution_note');
        $hasCost          = Schema::hasColumn('maintenance_requests', 'cost');
        $hasSource        = Schema::hasColumn('maintenance_requests', 'source');
        $hasExtra         = Schema::hasColumn('maintenance_requests', 'extra');

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

        $makeTimeline = function (string $status, Carbon $base) {
            $assigned        = null;
            $accepted        = null;
            $started         = null;
            $onHold          = null;
            $resolved        = null;
            $closed          = null;
            $completedLegacy = null;

            $assignedChance = random_int(0, 100) < 75;

            if (in_array($status, ['accepted','in_progress','on_hold','resolved','closed'], true)) {
                $assigned = (clone $base)->addDays(random_int(0, 3));
                $accepted = (clone $assigned)->addHours(random_int(0, 36));
            } elseif ($assignedChance && $status === 'pending') {
                if (random_int(0,100) < 20) {
                    $assigned = (clone $base)->addDays(random_int(0, 2));
                    $accepted = (clone $assigned)->addHours(random_int(0, 24));
                }
            }

            if (in_array($status, ['in_progress','on_hold','resolved','closed'], true)) {
                $started = ($accepted ?: (clone $base)->addDays(random_int(0, 5)))
                    ->addHours(random_int(1, 24));
            }

            if (in_array($status, ['on_hold','resolved','closed'], true)) {
                $onHold = ($started ?: (clone $base)->addDays(2))
                    ->addHours(random_int(2, 48));
            }

            if (in_array($status, ['resolved','closed'], true)) {
                $resolved        = ($onHold ?: $started ?: (clone $base)->addDays(3))
                    ->addHours(random_int(2, 48));
                $completedLegacy = $resolved;
            }

            if ($status === 'closed') {
                $closed = ($resolved ?: (clone $base)->addDays(5))
                    ->addHours(random_int(1, 24));
            }

            return [$assigned, $accepted, $started, $onHold, $resolved, $closed, $completedLegacy];
        };

        // Prepare idempotent, unique request number generator per period (MR-yyMM-####)
        $existingSet     = [];
        $prefixCounters  = [];

        if (Schema::hasTable('maintenance_requests') && $hasRequestNo) {
            $existing = DB::table('maintenance_requests')
                ->pluck('request_no')
                ->filter()
                ->all();

            foreach ($existing as $no) {
                $existingSet[$no] = true;

                if (preg_match('/^MR-(\d{4})-(\d{4})$/', $no, $m)) {
                    $prefix = $m[1];
                    $suffix = (int) $m[2];

                    $prefixCounters[$prefix] = max($prefixCounters[$prefix] ?? 1000, $suffix);
                }
            }
        }

        $usedInRun = [];

        $makeRequestNo = function (Carbon $date) use (&$prefixCounters, &$existingSet, &$usedInRun) {
            $prefix = $date->format('ym');
            $last   = $prefixCounters[$prefix] ?? 1000;

            do {
                $last++;
                $candidate = sprintf('MR-%s-%04d', $prefix, $last);
            } while (isset($existingSet[$candidate]) || isset($usedInRun[$candidate]));

            $prefixCounters[$prefix] = $last;
            $existingSet[$candidate] = true;
            $usedInRun[$candidate]   = true;

            return $candidate;
        };

        // ===== Seed maintenance_requests (bulk insert) =====
        DB::transaction(function () use (
            $requestCount,
            $assetIds,
            $staffIds,
            $techIds,
            $priorities,
            $statuses,
            $now,
            $chunkSize,
            $departmentIds,
            $hasRequestNo,
            $hasReporterName,
            $hasReporterPhone,
            $hasReporterEmail,
            $hasDeptMR,
            $hasLocationText,
            $hasPriority,
            $hasStatusCol,
            $hasTechnicianId,
            $hasRequestDate,
            $hasAssignedDate,
            $hasCompletedDate,
            $hasAcceptedAt,
            $hasStartedAt,
            $hasOnHoldAt,
            $hasResolvedAt,
            $hasClosedAt,
            $hasRemark,
            $hasResolutionNote,
            $hasCost,
            $hasSource,
            $hasExtra,
            $makeTimeline,
            $makeRequestNo
        ) {
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
                if ($isExternal) {
                    $reporter = null;
                }

                // technician: บาง pending อาจว่าง
                $techId = null;
                if ($status !== 'pending' || random_int(0, 100) < 40) {
                    $techId = $techIds ? $techIds[array_rand($techIds)] : null;
                }

                [
                    $assigned,
                    $accepted,
                    $started,
                    $onHold,
                    $resolved,
                    $closed,
                    $completedLegacy,
                ] = $makeTimeline($status, $createdAt);

                $row = array_fill_keys([
                    'asset_id',
                    'reporter_id',
                    'technician_id',
                    'reporter_name',
                    'reporter_phone',
                    'reporter_email',
                    'department_id',
                    'location_text',
                    'request_no',
                    'title',
                    'description',
                    'priority',
                    'status',
                    'request_date',
                    'assigned_date',
                    'completed_date',
                    'accepted_at',
                    'started_at',
                    'on_hold_at',
                    'resolved_at',
                    'closed_at',
                    'remark',
                    'resolution_note',
                    'cost',
                    'source',
                    'extra',
                    'created_at',
                    'updated_at',
                ], null);

                $row['asset_id'] = $assetId;

                if (Schema::hasColumn('maintenance_requests', 'reporter_id')) {
                    $row['reporter_id'] = $reporter;
                }

                if ($hasTechnicianId) {
                    $row['technician_id'] = $techId;
                }

                if ($isExternal) {
                    if ($hasReporterName) {
                        $row['reporter_name'] = fake()->name();
                    }
                    if ($hasReporterPhone) {
                        $row['reporter_phone'] = fake()->numerify('08########');
                    }
                    if ($hasReporterEmail) {
                        $row['reporter_email'] = fake()->safeEmail();
                    }
                }

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

                if ($hasRequestNo) {
                    $row['request_no'] = $makeRequestNo($createdAt);
                }

                $row['title']       = 'แจ้งซ่อม #'.$i;
                $row['description'] = 'รายละเอียดปัญหาเบื้องต้น';

                if ($hasPriority) {
                    $row['priority'] = $priority;
                }

                if ($hasStatusCol) {
                    $row['status'] = $status;
                }

                if ($hasRequestDate) {
                    $row['request_date'] = $createdAt;
                }

                if ($hasAssignedDate) {
                    $row['assigned_date'] = $assigned;
                }

                if ($hasCompletedDate) {
                    $row['completed_date'] = $completedLegacy;
                }

                if ($hasAcceptedAt) {
                    $row['accepted_at'] = $accepted;
                }

                if ($hasStartedAt) {
                    $row['started_at'] = $started;
                }

                if ($hasOnHoldAt) {
                    $row['on_hold_at'] = $onHold;
                }

                if ($hasResolvedAt) {
                    $row['resolved_at'] = $resolved;
                }

                if ($hasClosedAt) {
                    $row['closed_at'] = $closed;
                }

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

                if ($hasSource) {
                    $row['source'] = 'web';
                }

                if ($hasExtra) {
                    $row['extra'] = null;
                }

                $row['created_at'] = $createdAt;
                $row['updated_at'] = $closed
                    ?? $resolved
                    ?? $onHold
                    ?? $started
                    ?? $accepted
                    ?? $assigned
                    ?? $createdAt;

                $rows[] = $row;

                if (count($rows) >= $chunkSize) {
                    DB::table('maintenance_requests')->insert($rows);
                    $rows = [];
                }
            }

            if ($rows) {
                DB::table('maintenance_requests')->insert($rows);
            }
        });

        // ===== Maintenance Assignments (team workers demo) =====
        if (Schema::hasTable('maintenance_assignments') && !empty($techIds)) {
            $assignRows = [];
            $nowAssign  = now();

            // เอางานที่ไม่ใช่ cancelled/pending บ้าง + บางส่วนของ pending
            $targetRequests = MR::query()
                ->whereIn('status', [
                    MR::STATUS_ACCEPTED,
                    MR::STATUS_IN_PROGRESS,
                    MR::STATUS_ON_HOLD,
                    MR::STATUS_RESOLVED,
                    MR::STATUS_CLOSED,
                ])
                ->inRandomOrder()
                ->limit((int) floor($requestCount * 0.7))
                ->get();

            foreach ($targetRequests as $req) {
                if (!$techIds) {
                    continue;
                }

                // สุ่มให้มีทีม 1–3 คน
                $teamSize = random_int(1, min(3, count($techIds)));
                $picked   = (array) array_rand($techIds, $teamSize);

                foreach ($picked as $index) {
                    $userId = $techIds[$index];

                    // กำหนดสถานะ assignment ตามสถานะใบงานแบบประมาณ ๆ
                    $status = MaintenanceAssignment::STATUS_PENDING;
                    switch ($req->status) {
                        case MR::STATUS_IN_PROGRESS:
                        case MR::STATUS_ACCEPTED:
                            $status = MaintenanceAssignment::STATUS_IN_PROGRESS;
                            break;
                        case MR::STATUS_RESOLVED:
                        case MR::STATUS_CLOSED:
                            $status = MaintenanceAssignment::STATUS_DONE;
                            break;
                        case MR::STATUS_CANCELLED:
                            $status = MaintenanceAssignment::STATUS_CANCELLED;
                            break;
                        case MR::STATUS_ON_HOLD:
                            // สุ่ม pending / in_progress
                            $status = random_int(0, 1)
                                ? MaintenanceAssignment::STATUS_IN_PROGRESS
                                : MaintenanceAssignment::STATUS_PENDING;
                            break;
                        default:
                            $status = MaintenanceAssignment::STATUS_PENDING;
                    }

                    $assignRows[] = [
                        'maintenance_request_id' => $req->id,
                        'user_id'                => $userId,
                        'status'                 => $status,
                        'created_at'             => $nowAssign,
                        'updated_at'             => $nowAssign,
                    ];
                }
            }

            if ($assignRows) {
                DB::table('maintenance_assignments')->insert($assignRows);
            }
        }

        // ===== Maintenance Operation Logs (demo) =====
        if (Schema::hasTable('maintenance_operation_logs')) {
            // เลือกเฉพาะงานที่ resolved/closed มาใส่รายงานการปฏิบัติงาน
            $targetRequests = MR::query()
                ->whereIn('status', ['resolved', 'closed'])
                ->inRandomOrder()
                ->limit((int) floor($requestCount * 0.6)) // ซัก 60% ของใบงาน
                ->get();

            $opRows = [];
            $now    = now();

            foreach ($targetRequests as $req) {
                // กันซ้ำ ถ้ามีอยู่แล้วให้ข้าม
                if ($req->operationLog) {
                    continue;
                }

                // เลือกช่างคนใดคนหนึ่งในทีม หรือสุ่มจาก techIds
                $userId = null;
                if ($req->workers && $req->workers->count()) {
                    $userId = $req->workers->random()->id;
                } elseif (!empty($techIds)) {
                    $userId = $techIds[array_rand($techIds)];
                }

                $baseDate = $req->resolved_at
                    ?? $req->closed_at
                    ?? $req->started_at
                    ?? $req->request_date
                    ?? $req->created_at
                    ?? $now;

                $operationDate = (clone $baseDate)->startOfDay();

                $methods = ['requisition', 'service_fee', 'other'];
                $method  = $methods[array_rand($methods)];

                // เดารพ./หน่วยงานจาก department ถ้ามี
                $hospitalName = 'โรงพยาบาลพระปกเกล้า';
                if ($req->department?->name_th) {
                    $hospitalName .= ' - '.$req->department->name_th;
                } elseif ($req->department?->name_en) {
                    $hospitalName .= ' - '.$req->department->name_en;
                }

                $opRows[] = [
                    'maintenance_request_id' => $req->id,
                    'user_id'                => $userId,
                    'operation_date'         => $operationDate,
                    'operation_method'       => $method,
                    'hospital_name'          => $hospitalName,
                    'require_precheck'       => (bool) random_int(0, 1),
                    'remark'                 => $req->resolution_note
                        ?: fake()->sentence(10),
                    'issue_software'         => (bool) random_int(0, 1),
                    'issue_hardware'         => (bool) random_int(0, 1),
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ];
            }

            if ($opRows) {
                DB::table('maintenance_operation_logs')->insert($opRows);
            }
        }

        // ปรับ updated_at ให้ recent
        if (Schema::hasTable('maintenance_requests')) {
            DB::table('maintenance_requests')
                ->inRandomOrder()
                ->limit(15)
                ->update(['updated_at' => now()]);
        }
    }
}
