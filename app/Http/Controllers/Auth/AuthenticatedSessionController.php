<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            // เก็บเป็น session ปกติ (ไม่ใช่ flash)
            session()->put('toast', [
                'type'     => 'success',
                'message'  => 'Login successful',
                'position' => 'br',
                'timeout'  => 2800,
            ]);

            if ($request->expectsJson() || app()->environment('testing')) {
                return response()->noContent();
            }
            return redirect()->intended('/dashboard');

        } catch (ValidationException $e) {
            // เคสผิดยังใช้ flash ได้ เพราะไม่ redirect ซ้อน
            return back()
                ->with('toast', [
                    'type'     => 'error',
                    'message'  => 'Email or Password is incorrect',
                    'position' => 'tr',
                    'timeout'  => 3200,
                ])
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($request->expectsJson() || app()->environment('testing')) {
            return response()->noContent();
        }
        return redirect('/')->with('toast', [
            'type'     => 'info',
            'message'  => 'Logout successful',
            'position' => 'tr',
            'timeout'  => 2400,
        ]);
    }
}
