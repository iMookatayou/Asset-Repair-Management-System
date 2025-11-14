<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    protected function abilitiesFor(User $user): array
    {
        if ($user->isSupervisor()) {
            return [
                'manage-users', 'assets.read', 'assets.write', 'maintenance.request', 'maintenance.manage', 'stats.view'
            ];
        }

        if ($user->isWorker()) {
            return [
                'assets.read', 'maintenance.work', 'stats.view'
            ];
        }

        return [
            'assets.read', 'maintenance.request', 'stats.view'
        ];
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required','string','email'],
            'password'    => ['required','string','min:6'],
            'device_name' => ['nullable','string','max:120'],
        ]);

        $key = sprintf('login:%s|%s', Str::lower($data['email']), $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'พยายามเข้าสู่ระบบมากเกินไป กรุณาลองใหม่ใน '.$seconds.' วินาที',
                'code'    => 'too_many_attempts',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], (string) $user->password)) {
            RateLimiter::hit($key, 60);
            return response()->json([
                'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
                'code'    => 'invalid_credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        RateLimiter::clear($key);

        $device    = $data['device_name'] ?? ('api-'.Str::random(6));
        $abilities = $this->abilitiesFor($user);
        $token     = $user->createToken($device, $abilities);

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user'       => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role ?? null,
                'abilities' => $abilities,
            ],
        ], Response::HTTP_CREATED);
    }

    public function tokens(Request $request)
    {
        $user = $request->user();
        $list = $user->tokens()->latest('id')->get()->map(fn($t) => [
            'id'         => $t->id,
            'name'       => $t->name,
            'abilities'  => $t->abilities,
            'last_used_at' => $t->last_used_at,
            'created_at' => $t->created_at,
        ]);
        return response()->json(['data' => $list]);
    }

    public function revokeToken(Request $request, string $tokenId)
    {
        $user = $request->user();
        $token = $user->tokens()->where('id', $tokenId)->first();
        if (! $token) {
            return response()->json([
                'message' => 'ไม่พบโทเค็น',
                'code'    => 'token_not_found',
            ], Response::HTTP_NOT_FOUND);
        }
        $token->delete();
        return response()->json(['message' => 'ยกเลิกโทเค็นเรียบร้อยแล้ว']);
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        return response()->json(['message' => 'ออกจากระบบเรียบร้อยแล้ว']);
    }

    public function logoutAll(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
        }
        return response()->json(['message' => 'ยกเลิกโทเค็นทั้งหมดเรียบร้อยแล้ว']);
    }

    public function me(Request $request)
    {
        $u = $request->user();
        return response()->json([
            'id'        => $u->id,
            'name'      => $u->name,
            'email'     => $u->email,
            'role'      => $u->role ?? null,
            'abilities' => $this->abilitiesFor($u),
        ]);
    }
}
