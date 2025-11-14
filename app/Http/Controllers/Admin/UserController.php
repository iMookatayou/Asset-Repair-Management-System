<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ใช้บทบาทตามโมเดล User
    private const ROLES = [];

    public function index(Request $request)
    {
        $q = User::query();

        if ($search = trim((string) $request->get('s', ''))) {
            $needle = mb_strtolower($search);
            $q->where(function ($qq) use ($needle) {
                $qq->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"])
                   ->orWhereRaw('LOWER(email) LIKE ?', ["%{$needle}%"])
                   ->orWhereRaw('LOWER(COALESCE(department, \'\')) LIKE ?', ["%{$needle}%"]);
            });
        }

        if ($role = $request->get('role')) {
            $q->where('role', $role);
        }

        if ($dept = $request->get('department')) {
            $q->where('department', $dept);
        }

        $users = $q->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => User::availableRoles(),
            'filters' => [
                's' => $request->get('s'),
                'role' => $request->get('role'),
                'department' => $request->get('department'),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => User::availableRoles(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', 'min:8'],
            'department' => ['nullable', 'string', 'max:100'],
            'role'       => ['required', Rule::in(User::availableRoles())],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = DB::transaction(fn () => User::create($data));

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'สร้างผู้ใช้เรียบร้อยแล้ว');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user'  => $user,
            'roles' => User::availableRoles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'password'   => ['nullable', 'confirmed', 'min:8'],
            'department' => ['nullable', 'string', 'max:100'],
            'role'       => ['required', Rule::in(User::availableRoles())],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        DB::transaction(fn () => $user->update($data));

        return back()->with('status', 'อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['delete' => 'คุณไม่สามารถลบผู้ใช้ตัวเองได้']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'ลบผู้ใช้เรียบร้อยแล้ว');
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'string'],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer', 'exists:users,id'],
            'role'   => ['nullable', Rule::in(User::availableRoles())],
        ]);

        $ids = collect($validated['ids'])->unique()->values();

        $ids = $ids->reject(fn ($id) => (int)$id === (int) Auth::id());

        if ($ids->isEmpty()) {
            return back()->withErrors(['bulk' => 'ไม่มีเป้าหมายที่ถูกต้อง (ไม่สามารถดำเนินการกับตัวเองได้)']);
        }

        DB::transaction(function () use ($validated, $ids) {
            $query = User::whereIn('id', $ids);

            switch ($validated['action']) {
                case 'change_role':
                    $role = $validated['role'] ?? null;
                    if (!$role) {
                        abort(422, 'ต้องระบุบทบาทสำหรับการเปลี่ยนบทบาท');
                    }
                    $query->update(['role' => $role]);
                    break;

                case 'delete':
                    $query->delete();
                    break;

                default:
                    abort(422, 'ไม่รู้จักคำสั่งนี้');
            }
        });

        return back()->with('status', 'ดำเนินการเรียบร้อยแล้ว');
    }
}
