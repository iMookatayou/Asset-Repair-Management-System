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
                'id', 'name', 'email', 'department', 'role',
                'profile_photo_thumb', 'created_at', 'updated_at'
            ])
            ->with([
                'departmentRef:id,code,name',
            ])
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            }))
            ->when($role, fn($qq) => $qq->where('role', $role))
            ->when($dept, fn($qq) => $qq->where('department', $dept))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($users);
    }

    public function show(User $user)
    {
        $user->load('departmentRef:id,code,name');
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => ['sometimes','string','max:255'],
            'email'      => ['sometimes','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'department' => ['nullable','string','max:100'],
            'role'       => ['sometimes', Rule::in(['admin','technician','computer_officer'])],
            'password'   => ['nullable','string','min:8'],
        ]);

        if (array_key_exists('password', $data)) {
            if (!$data['password']) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }

        $user->update($data);
        $user->load('departmentRef:id,code,name');

        return response()->json([
            'message' => 'อัปเดตเรียบร้อยแล้ว',
            'data'    => $user,
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'ลบเรียบร้อยแล้ว']);
    }
}
