<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    // POST /api/auth/password/email  { email }
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required','email']]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __($status),
            'code'    => 'reset_link_failed',
        ], Response::HTTP_BAD_REQUEST);
    }

    // POST /api/auth/password/reset  { email, token, password, password_confirmation }
    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => ['required','string'],
            'email'                 => ['required','email'],
            'password'              => ['required','confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function (User $user, string $password) use ($request) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __($status),
            'code'    => 'password_reset_failed',
        ], Response::HTTP_BAD_REQUEST);
    }
}
