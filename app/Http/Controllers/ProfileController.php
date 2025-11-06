<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // ดูโปรไฟล์ (ทุกคน)
    public function show(Request $request): View
    {
        $user = $request->user()->loadMissing('departmentRef');
        return view('profile.show', compact('user'));
    }

    // แก้ไขโปรไฟล์ (เฉพาะแอดมิน) — ใช้ Union Type
    public function edit(Request $request): View|RedirectResponse
    {
        if (!$request->user()->isAdmin()) {
            return Redirect::route('profile.show')
                ->with('status', 'คุณไม่มีสิทธิ์แก้ไขโปรไฟล์');
        }

        $user = $request->user()->loadMissing('departmentRef');
        return view('profile.edit', compact('user'));
    }

    // อัปเดตโปรไฟล์ (เฉพาะแอดมิน)
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // ลบบัญชี (ถ้าไม่ใช้ ตัด route และเมธอดนี้ออกได้)
    public function destroy(Request $request): RedirectResponse
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Forbidden');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
