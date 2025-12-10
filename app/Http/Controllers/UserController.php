<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q       = (string) $request->string('q');
        $role    = (string) $request->string('role');
        $dept    = (string) $request->string('department');
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $users = User::query()
            ->select([
                'id',
                'name',
                'citizen_id',           // ✅ เพิ่มเลขบัตร
                'email',
                'department',
                'role',
                'profile_photo_thumb',
                'created_at',
                'updated_at',
            ])
            ->with([
                'departmentRef' => function ($qq) {
                    $qq->select([
                        'id',
                        'code',
                        'name_th',
                        'name_en',
                    ]);
                },
                'roleRef' => function ($qq) {
                    $qq->select([
                        'id',
                        'code',
                        'name_th',
                        'name_en',
                    ]);
                },
            ])
            ->withAvg('technicianRatings as rating_average', 'score')
            ->withCount('technicianRatings as rating_count')
            ->when($q, fn ($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('citizen_id', 'like', "%{$q}%");   // ✅ search ด้วย citizen_id ได้
            }))
            ->when($role, fn ($qq) => $qq->where('role', $role))
            ->when($dept, fn ($qq) => $qq->where('department', $dept))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($users);
    }

    public function show(User $user)
    {
        $user->load([
                'departmentRef' => function ($qq) {
                    $qq->select([
                        'id',
                        'code',
                        'name_th',
                        'name_en',
                    ]);
                },
                'roleRef' => function ($qq) {
                    $qq->select([
                        'id',
                        'code',
                        'name_th',
                        'name_en',
                    ]);
                },
            ])
            ->loadAvg('technicianRatings as rating_average', 'score')
            ->loadCount('technicianRatings as rating_count');

        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],

            // ✅ citizen_id optional แต่ถ้ามีต้อง 13 หลัก และไม่ซ้ำคนอื่น
            'citizen_id'  => [
                'sometimes',
                'digits:13',
                Rule::unique('users', 'citizen_id')->ignore($user->id),
            ],

            // ✅ email ให้ nullable (เคลียร์เป็น null ได้)
            'email'       => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'department'  => ['nullable', 'string', 'max:100'],
            'role'        => ['sometimes', Rule::in(User::availableRoles())],
            'password'    => ['nullable', 'string', 'min:8'],
        ]);

        // ถ้าไม่มี password หรือว่าง string → ไม่อัปเดต
        if (array_key_exists('password', $data)) {
            if (!$data['password']) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }

        $user->update($data);

        $user->load([
                'departmentRef' => function ($qq) {
                    $qq->select([
                        'id',
                        'code',
                        'name_th',
                        'name_en',
                    ]);
                },
            ])
            ->loadAvg('technicianRatings as rating_average', 'score')
            ->loadCount('technicianRatings as rating_count');

        return response()->json([
            'message' => 'อัปเดตเรียบร้อยแล้ว',
            'data'    => $user,
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        // กันไม่ให้ลบตัวเองผ่าน API
        if ($request->user() && $request->user()->id === $user->id) {
            return response()->json([
                'message' => 'ไม่สามารถลบบัญชีผู้ใช้ของตัวเองผ่าน API นี้ได้',
            ], 422);
        }

        $user->delete();

        return response()->json(['message' => 'ลบเรียบร้อยแล้ว']);
    }
}
