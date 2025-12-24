<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\Toast;

class AssetController extends Controller
{
    /**
     * JSON options (unicode + slashes + pretty)
     */
    private function jsonOptions(Request $request): int
    {
        return JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | ($request->boolean('pretty') ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * API: GET /assets (json)
     */
    public function index(Request $request)
    {
        $q          = trim($request->string('q')->toString());
        $status     = $request->string('status')->toString();
        $type       = $request->string('type')->toString();
        $categoryId = $request->integer('category_id');
        $deptId     = $request->integer('department_id');
        $location   = $request->string('location')->toString();

        $perPageInput = (int) $request->integer('per_page', 20);
        $perPage      = max(1, min($perPageInput, 100));

        // map คีย์ที่ frontend ใช้ -> คอลัมน์จริงใน DB
        $sortMap = [
            'id'              => 'id',
            'asset_code'      => 'asset_code',
            'name'            => 'name',
            'purchase_date'   => 'purchase_date',
            'warranty_expire' => 'warranty_expire',
            'status'          => 'status',
            'created_at'      => 'created_at',
        ];

        // ใช้ helper จำ sort ต่อ user (ใช้ key ฝั่ง UI เช่น id, asset_code, name)
        [$sortKey, $sortDir] = $this->resolveAssetSort($request, array_keys($sortMap));
        $sortBy = $sortMap[$sortKey] ?? 'id';

        $baseQuery = Asset::query()
            ->with(['categoryRef', 'department'])
            ->search($q) // <<< ต้นเหตุหลักมักอยู่ใน scopeSearch
            ->status($status)
            ->when($type !== '', fn($s) => $s->where('type', $type))
            ->when($request->filled('category_id'), fn($s) => $s->where('category_id', $categoryId))
            ->departmentId($deptId)
            ->when($location !== '', fn($s) => $s->where('location', $location));

        /**
         * ✅ OPTIONAL: ถ้ายังไม่แก้ Model::search
         * จัดอันดับให้ asset_code ที่ "ตรง/ขึ้นต้น" มาก่อน (เฉพาะตอนมี q)
         */
        if ($q !== '') {
            $qEsc = str_replace("'", "''", $q);
            $baseQuery->orderByRaw("
                CASE
                    WHEN assets.asset_code = '{$qEsc}' THEN 0
                    WHEN assets.asset_code LIKE '{$qEsc}%' THEN 1
                    WHEN assets.asset_code LIKE '%{$qEsc}%' THEN 2
                    WHEN assets.serial_number LIKE '%{$qEsc}%' THEN 3
                    WHEN assets.name LIKE '%{$qEsc}%' THEN 4
                    ELSE 9
                END
            ");
        }

        $filteredTotal = (clone $baseQuery)->toBase()->count();

        $assets = (clone $baseQuery)
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'data' => $assets->items(),
            'meta' => [
                'current_page' => $assets->currentPage(),
                'per_page'     => $assets->perPage(),
                'total'        => $assets->total() ?: $filteredTotal,
                'last_page'    => $assets->lastPage(),
            ],
            'sort' => [
                'by'  => $sortKey,
                'dir' => $sortDir,
            ],
            'toast' => Toast::info('โหลดรายการทรัพย์สินแล้ว', 1200),
        ];

        return response()->json($payload, 200, [], $this->jsonOptions($request));
    }

    /**
     * API: POST /assets (json)
     */
    public function store(Request $request)
    {
        $rules = [
            'asset_code'      => ['required', 'string', 'max:100', 'unique:assets,asset_code'],
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['nullable', 'string', 'max:100'],
            'category_id'     => ['nullable', 'integer', 'exists:asset_categories,id'],
            'brand'           => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'serial_number'   => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'],
            'location'        => ['nullable', 'string', 'max:255'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'purchase_date'   => ['nullable', 'date'],
            'warranty_expire' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'status'          => ['nullable', Rule::in(['active', 'in_repair', 'disposed'])],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $fieldsHuman = [
                'asset_code' => 'รหัสครุภัณฑ์',
                'name' => 'ชื่อครุภัณฑ์',
                'serial_number' => 'Serial',
                'category_id' => 'หมวดหมู่',
                'department_id' => 'หน่วยงาน',
                'warranty_expire' => 'หมดประกัน',
            ];

            $bad = collect(array_keys($errors->toArray()))
                ->map(fn($f) => $fieldsHuman[$f] ?? $f)
                ->implode(', ');

            $msg = $bad ? ('ข้อมูลไม่ถูกต้อง: ' . $bad) : 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';

            if (!$request->expectsJson()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('toast', Toast::warning($msg, 2200));
            }

            return response()->json([
                'errors' => $errors,
                'toast'  => Toast::warning($msg, 2200),
            ], Response::HTTP_UNPROCESSABLE_ENTITY, [], $this->jsonOptions($request));
        }

        $data = $validator->validated();
        $asset = Asset::create($data)->load(['categoryRef', 'department']);

        return response()->json([
            'message' => 'created',
            'toast'   => Toast::success('สร้างทรัพย์สินเรียบร้อย', 1600),
            'data'    => $asset,
        ], Response::HTTP_CREATED, [], $this->jsonOptions($request));
    }

    /**
     * API: GET /assets/{asset} (json)
     */
    public function show(Asset $asset)
    {
        $asset->load(['categoryRef', 'department']);

        return response()->json([
            'data'  => $asset,
            'toast' => Toast::info('โหลดข้อมูลทรัพย์สินแล้ว', 1000),
        ], 200, [], $this->jsonOptions(request()));
    }

    /**
     * API: PUT/PATCH /assets/{asset} (json)
     */
    public function update(Request $request, Asset $asset)
    {
        $rules = [
            'asset_code'      => ['sometimes', 'string', 'max:100', 'unique:assets,asset_code,' . $asset->id],
            'name'            => ['sometimes', 'string', 'max:255'],
            'type'            => ['nullable', 'string', 'max:100'],
            'category_id'     => ['nullable', 'integer', 'exists:asset_categories,id'],
            'brand'           => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'serial_number'   => ['nullable', 'string', 'max:100', 'unique:assets,serial_number,' . $asset->id],
            'location'        => ['nullable', 'string', 'max:255'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'purchase_date'   => ['nullable', 'date'],
            'warranty_expire' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'status'          => ['nullable', Rule::in(['active', 'in_repair', 'disposed'])],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $fieldsHuman = [
                'asset_code' => 'รหัสครุภัณฑ์',
                'name' => 'ชื่อครุภัณฑ์',
                'serial_number' => 'Serial',
                'category_id' => 'หมวดหมู่',
                'department_id' => 'หน่วยงาน',
                'warranty_expire' => 'หมดประกัน',
            ];

            $bad = collect(array_keys($errors->toArray()))
                ->map(fn($f) => $fieldsHuman[$f] ?? $f)
                ->implode(', ');

            $msg = $bad ? ('ข้อมูลไม่ถูกต้อง: ' . $bad) : 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';

            if (!$request->expectsJson()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('toast', Toast::warning($msg, 2200));
            }

            return response()->json([
                'errors' => $errors,
                'toast'  => Toast::warning($msg, 2200),
            ], Response::HTTP_UNPROCESSABLE_ENTITY, [], $this->jsonOptions($request));
        }

        $data = $validator->validated();
        $asset->update($data);

        return response()->json([
            'message' => 'updated',
            'toast'   => Toast::success('อัปเดตทรัพย์สินเรียบร้อย', 1600),
            'data'    => $asset->load(['categoryRef', 'department']),
        ], Response::HTTP_OK, [], $this->jsonOptions($request));
    }

    /**
     * API: DELETE /assets/{asset} (json)
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();

        return response()->json([
            'message' => 'deleted',
            'toast'   => Toast::success('ลบทรัพย์สินแล้ว', 1600),
        ], Response::HTTP_OK, [], $this->jsonOptions(request()));
    }

    /**
     * WEB: GET /assets (blade)
     */
    public function indexPage(Request $request)
    {
        $q          = trim($request->string('q')->toString());
        $status     = $request->string('status')->toString();
        $categoryId = $request->integer('category_id');
        $deptId     = $request->integer('department_id');
        $type       = $request->string('type')->toString();
        $location   = $request->string('location')->toString();

        // map ชื่อ sort ที่ใช้ใน UI -> คอลัมน์จริง
        $sortMap = [
            'id'         => 'id',
            'asset_code' => 'asset_code',
            'name'       => 'name',
            'status'     => 'status',
            'category'   => 'category', // พิเศษ ใช้ orderByRaw
        ];

        // ใช้ helper จำ sort ต่อ user
        [$sortBy, $sortDir] = $this->resolveAssetSort($request, array_keys($sortMap));
        $sortCol = $sortMap[$sortBy] ?? 'id';

        $assetsQ = Asset::query()
            ->with(['categoryRef', 'department'])
            ->search($q)  // <<< ตัวหลัก
            ->status($status)
            ->when($request->filled('category_id'), fn($s) => $s->where('category_id', $categoryId))
            ->departmentId($deptId)
            ->when($type !== '', fn($s) => $s->where('type', $type))
            ->when($location !== '', fn($s) => $s->where('location', $location));

        /**
         * ✅ OPTIONAL: ถ้ายังไม่แก้ Model::search
         * จัดอันดับให้ asset_code ที่ "ตรง/ขึ้นต้น" มาก่อน (เฉพาะตอนมี q)
         */
        if ($q !== '') {
            $qEsc = str_replace("'", "''", $q);
            $assetsQ->orderByRaw("
                CASE
                    WHEN assets.asset_code = '{$qEsc}' THEN 0
                    WHEN assets.asset_code LIKE '{$qEsc}%' THEN 1
                    WHEN assets.asset_code LIKE '%{$qEsc}%' THEN 2
                    WHEN assets.serial_number LIKE '%{$qEsc}%' THEN 3
                    WHEN assets.name LIKE '%{$qEsc}%' THEN 4
                    ELSE 9
                END
            ");
        }

        if ($sortCol === 'category') {
            $assetsQ->orderByRaw(
                "(select name from asset_categories where asset_categories.id = assets.category_id) {$sortDir}"
            );
        } else {
            $assetsQ->orderBy($sortCol, $sortDir);
        }

        $assets = $assetsQ->paginate(20)->withQueryString();

        $categories  = \App\Models\AssetCategory::orderBy('name')->get(['id', 'name']);

        $departments = \App\Models\Department::query()
            ->select(['id', 'code', 'name_th', 'name_en'])
            ->orderByRaw('COALESCE(name_th, name_en, code) asc')
            ->get()
            ->map(fn($d) => [
                'id'           => $d->id,
                'display_name' => $d->display_name,
            ]);

        return view('assets.index', compact(
            'assets',
            'categories',
            'departments',
            'sortBy',
            'sortDir',
            'q',
            'status',
            'categoryId',
            'deptId',
            'type',
            'location',
        ));
    }

    public function createPage()
    {
        $departments = \App\Models\Department::query()
            ->select(['id', 'code', 'name_th', 'name_en'])
            ->orderByRaw('COALESCE(name_th, name_en, code) asc')
            ->get();

        $categories  = \App\Models\AssetCategory::orderBy('name')->get(['id', 'name']);

        if ($departments->isEmpty()) {
            session()->flash('toast', Toast::info('ยังไม่มีข้อมูลหน่วยงาน กรุณา seed หรือเพิ่มใหม่ก่อน', 3200));
        }
        if ($categories->isEmpty()) {
            session()->flash('toast', Toast::info('ยังไม่มีหมวดหมู่ทรัพย์สิน กรุณา seed หรือเพิ่มใหม่ก่อน', 3200));
        }

        return view('assets.create', compact('departments', 'categories'));
    }

    public function storePage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_code'      => ['required', 'string', 'max:100', 'unique:assets,asset_code'],
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['nullable', 'string', 'max:100'],
            'category_id'     => ['nullable', 'integer', 'exists:asset_categories,id'],
            'brand'           => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'serial_number'   => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'],
            'location'        => ['nullable', 'string', 'max:255'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'purchase_date'   => ['nullable', 'date'],
            'warranty_expire' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'status'          => ['nullable', Rule::in(['active', 'in_repair', 'disposed'])],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $fieldsHuman = [
                'asset_code' => 'รหัสครุภัณฑ์',
                'name' => 'ชื่อครุภัณฑ์',
                'serial_number' => 'Serial',
                'category_id' => 'หมวดหมู่',
                'department_id' => 'หน่วยงาน',
                'warranty_expire' => 'หมดประกัน',
            ];
            $bad = collect(array_keys($errors->toArray()))
                ->map(fn($f) => $fieldsHuman[$f] ?? $f)
                ->implode(', ');
            $msg = $bad ? ('ข้อมูลไม่ถูกต้อง: ' . $bad) : 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('toast', Toast::error($msg, 2600));
        }

        $data = $validator->validated();
        $asset = Asset::create($data);

        return redirect()
            ->route('assets.show', $asset)
            ->with('toast', Toast::success('สร้างทรัพย์สินเรียบร้อยแล้ว'));
    }

    public function showPage(Asset $asset)
    {
        $asset->load(['categoryRef', 'department'])
            ->loadCount([
                'maintenanceRequests as maintenance_requests_count',
                'requestAttachments as attachments_count',
            ]);

        $logs = $asset->requestLogs()
            ->select('maintenance_logs.*')
            ->orderBy(
                Schema::hasColumn('maintenance_logs', 'created_at') ? 'maintenance_logs.created_at' : 'maintenance_logs.id',
                'desc'
            )
            ->limit(10)
            ->get();

        $attQuery = $asset->requestAttachments()->select('attachments.*');

        $attQuery->orderBy(
            Schema::hasColumn('attachments', 'created_at') ? 'attachments.created_at' : 'attachments.id',
            'desc'
        );

        $attachments = $attQuery->get();

        if (session('status') && !session()->has('toast')) {
            session()->flash('toast', Toast::success(session('status')));
        }

        return view('assets.show', compact('asset', 'logs', 'attachments'));
    }

    public function editPage(Asset $asset)
    {
        $asset->load(['categoryRef', 'department']);

        $departments = \App\Models\Department::query()
            ->select(['id', 'code', 'name_th', 'name_en'])
            ->orderByRaw('COALESCE(name_th, name_en, code) asc')
            ->get();

        $categories  = \App\Models\AssetCategory::orderBy('name')->get(['id', 'name']);

        if ($departments->isEmpty()) {
            session()->flash('toast', Toast::info('ยังไม่มีข้อมูลหน่วยงาน กรุณา seed หรือเพิ่มใหม่ก่อน', 3200));
        }
        if ($categories->isEmpty()) {
            session()->flash('toast', Toast::info('ยังไม่มีหมวดหมู่ทรัพย์สิน กรุณา seed หรือเพิ่มใหม่ก่อน', 3200));
        }

        return view('assets.edit', compact('asset', 'departments', 'categories'));
    }

    public function updatePage(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'asset_code'      => ['sometimes', 'string', 'max:100', 'unique:assets,asset_code,' . $asset->id],
            'name'            => ['sometimes', 'string', 'max:255'],
            'type'            => ['nullable', 'string', 'max:100'],
            'category_id'     => ['nullable', 'integer', 'exists:asset_categories,id'],
            'brand'           => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'serial_number'   => ['nullable', 'string', 'max:100', 'unique:assets,serial_number,' . $asset->id],
            'location'        => ['nullable', 'string', 'max:255'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'purchase_date'   => ['nullable', 'date'],
            'warranty_expire' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'status'          => ['nullable', Rule::in(['active', 'in_repair', 'disposed'])],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $fieldsHuman = [
                'asset_code' => 'รหัสครุภัณฑ์',
                'name' => 'ชื่อครุภัณฑ์',
                'serial_number' => 'Serial',
                'category_id' => 'หมวดหมู่',
                'department_id' => 'หน่วยงาน',
                'warranty_expire' => 'หมดประกัน',
            ];
            $bad = collect(array_keys($errors->toArray()))
                ->map(fn($f) => $fieldsHuman[$f] ?? $f)
                ->implode(', ');
            $msg = $bad ? ('ข้อมูลไม่ถูกต้อง: ' . $bad) : 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('toast', Toast::error($msg, 2600));
        }

        $data = $validator->validated();
        $asset->update($data);

        return redirect()
            ->route('assets.show', $asset)
            ->with('toast', Toast::success('อัปเดตรายการทรัพย์สินแล้ว'));
    }

    public function destroyPage(Asset $asset)
    {
        $asset->delete();

        return redirect()
            ->route('assets.index')
            ->with('toast', Toast::success('ลบทรัพย์สินเรียบร้อยแล้ว'));
    }

    public function printPage(Request $request, Asset $asset)
    {
        $asset->load(['categoryRef', 'department'])
            ->loadCount([
                'maintenanceRequests as maintenance_requests_count',
                'requestAttachments as attachments_count',
            ]);

        $hospital = [
            'name_th'  => 'โรงพยาบาลพระปกเกล้า',
            'name_en'  => 'PHRAPOKKLAO HOSPITAL',
            'subtitle' => 'Asset Repair Management',
            'logo'     => asset('images/logoppk1.png'),
        ];

        $pdf = Pdf::loadView('assets.print', [
            'asset'    => $asset,
            'hospital' => $hospital,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('asset-' . $asset->asset_code . '.pdf');
    }

    /**
     * จำค่า sort_by / sort_dir ของหน้า Asset ต่อ user ด้วย session
     */
    protected function resolveAssetSort(Request $request, array $allowedKeys): array
    {
        $user   = $request->user();
        $userId = $user?->id;

        $sessionSortByKey  = $userId ? "asset_sort_by_user_{$userId}"  : 'asset_sort_by_guest';
        $sessionSortDirKey = $userId ? "asset_sort_dir_user_{$userId}" : 'asset_sort_dir_guest';

        $sortByReq  = $request->query('sort_by');
        $sortDirReq = strtolower((string) $request->query('sort_dir'));

        if (in_array($sortByReq, $allowedKeys, true)) {
            $sortBy = $sortByReq;
            session([$sessionSortByKey => $sortBy]);
        } else {
            $sortBy = session($sessionSortByKey, 'id');
        }

        if (in_array($sortDirReq, ['asc', 'desc'], true)) {
            $sortDir = $sortDirReq;
            session([$sessionSortDirKey => $sortDir]);
        } else {
            $sortDir = session($sessionSortDirKey, 'desc');
        }

        return [$sortBy, $sortDir];
    }
}
