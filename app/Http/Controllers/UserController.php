<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $role = $request->string('role')->toString();

        $users = User::query()
            ->when($q, fn($qq)=>$qq->where(function($w) use ($q){
                $w->where('name','like',"%$q%")->orWhere('email','like',"%$q%");
            }))
            ->when($role, fn($qq)=>$qq->where('role',$role))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page',20));

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => ['sometimes','string','max:255'],
            'email'      => ['sometimes','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'department' => ['nullable','string','max:100'],
            'role'       => ['sometimes', Rule::in(['admin','technician','staff'])],
            'password'   => ['nullable','string','min:8'],
        ]);

        if (!empty($data['password'])) {
        }

        $user->update($data);
        return response()->json(['message'=>'updated','data'=>$user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message'=>'deleted']);
    }
}
