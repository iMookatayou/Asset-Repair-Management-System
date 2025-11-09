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
    /**
     * Map role -> default abilities/scopes for API tokens.
     */
    protected function abilitiesFor(User $user): array
    {
        return match ($user->role) {
            User::ROLE_ADMIN => [
                'manage-users','assets.read','assets.write','maintenance.request','maintenance.manage','stats.view'
            ],
            User::ROLE_TECHNICIAN => [
                'assets.read','maintenance.work','stats.view'
            ],
            default => [ // staff
                'assets.read','maintenance.request','stats.view'
            ],
        };
    }

    // POST /api/auth/login
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
                'message' => 'Too many attempts. Try again in '.$seconds.' seconds.',
                'code'    => 'too_many_attempts',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], (string) $user->password)) {
            RateLimiter::hit($key, 60);
            return response()->json([
                'message' => 'Invalid credentials',
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

    // GET /api/auth/tokens (list own tokens) (auth:sanctum)
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

    // DELETE /api/auth/tokens/{token} (revoke one) (auth:sanctum)
    public function revokeToken(Request $request, string $tokenId)
    {
        $user = $request->user();
        $token = $user->tokens()->where('id', $tokenId)->first();
        if (! $token) {
            return response()->json([
                'message' => 'Token not found',
                'code'    => 'token_not_found',
            ], Response::HTTP_NOT_FOUND);
        }
        $token->delete();
        return response()->json(['message' => 'Token revoked']);
    }

    // POST /api/auth/logout (auth:sanctum)
    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        return response()->json(['message' => 'Logged out']);
    }

    // POST /api/auth/logout-all (auth:sanctum)
    public function logoutAll(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
        }
        return response()->json(['message' => 'All tokens revoked']);
    }

    // GET /api/auth/me (auth:sanctum)
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
