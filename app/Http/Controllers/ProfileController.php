<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->loadMissing('departmentRef');
        return view('profile.show', compact('user'));
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user()->loadMissing('departmentRef');
        return view('profile.edit', compact('user'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 1) ฟิลด์หลักจาก FormRequest
        $data = $request->validated();

        // 2) รูปภาพ
        $request->validate([
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_avatar') && $request->hasFile('avatar')) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'เลือกระหว่างอัปโหลดรูปใหม่หรือ “ลบรูปปัจจุบัน” อย่างใดอย่างหนึ่ง',
                'position' => 'tc',
                'timeout' => 3800,
                'size' => 'md',
            ]);
        }

        // 3) อัปเดตรายละเอียดทั่วไป
        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $avatarChanged = false;
        $avatarRemoved = false;

        $disk        = Storage::disk('public');
        $disk->makeDirectory('avatars');
        $hasThumbCol = Schema::hasColumn('users', 'profile_photo_thumb');

        // ===== เลือกไดรเวอร์ประมวลผลภาพแบบอัตโนมัติ =====
        $driver = null;
        if (extension_loaded('imagick')) {
            $driver = new ImagickDriver();
        } elseif (extension_loaded('gd') && function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
            $driver = new GdDriver();
        }

        $manager = $driver ? new ImageManager($driver) : null;

        // เช็ค WebP support (ถ้าใช้ GD)
        $supportsWebp = true;
        if ($driver instanceof GdDriver) {
            $supportsWebp = function_exists('imagewebp');
        }
        $targetExt = $supportsWebp ? 'webp' : 'jpg';
        $encodeFn  = $supportsWebp ? 'toWebp' : 'toJpeg'; // method on Intervention v3

        // 4) ลบรูป (ติ๊ก remove)
        if ($request->boolean('remove_avatar') === true) {
            $toDelete = array_values(array_filter([
                $user->profile_photo_path ?: null,
                $hasThumbCol ? ($user->profile_photo_thumb ?: null) : null,
            ]));
            if ($toDelete) $disk->delete($toDelete);

            $user->profile_photo_path = null;
            if ($hasThumbCol) $user->profile_photo_thumb = null;
            $avatarRemoved = true;
        }

        // 5) อัปโหลดใหม่
        if ($request->hasFile('avatar')) {
            // ลบของเก่า
            $toDelete = array_values(array_filter([
                $user->profile_photo_path ?: null,
                $hasThumbCol ? ($user->profile_photo_thumb ?: null) : null,
            ]));
            if ($toDelete) $disk->delete($toDelete);

            $file = $request->file('avatar');

            try {
                // กรณีไม่มี driver ใด ๆ เลย → เก็บไฟล์ต้นฉบับ (ไม่ย่อ/ไม่แปลง)
                if (!$manager) {
                    $basename = $user->id . '-' . time();
                    $ext      = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                    $mainPath = "avatars/{$basename}.{$ext}";
                    $disk->putFileAs('avatars', $file, "{$basename}.{$ext}");

                    // ไม่มีการทำ thumb ก็ให้ใช้ไฟล์เดียวกันไปก่อน หรือปล่อยว่าง
                    $user->profile_photo_path = $mainPath;
                    if ($hasThumbCol) {
                        $user->profile_photo_thumb = $mainPath; // ใช้ไฟล์เดียวกัน
                    }

                    Log::warning('Avatar stored without processing (no image driver).', [
                        'user_id' => $user->id,
                        'php_gd' => extension_loaded('gd') ? 'yes' : 'no',
                        'php_imagick' => extension_loaded('imagick') ? 'yes' : 'no',
                    ]);

                    $avatarChanged = true;
                } else {
                    // มี driver → ประมวลผลปกติ
                    $img = $manager->read($file->getRealPath());

                    $basename = $user->id . '-' . time();
                    $mainPath  = "avatars/{$basename}-512.{$targetExt}";
                    $thumbPath = "avatars/{$basename}-128.{$targetExt}";

                    $main  = (clone $img)->cover(512, 512)->{$encodeFn}(quality: 80);
                    $thumb = (clone $img)->cover(128, 128)->{$encodeFn}(quality: 80);

                    $disk->put($mainPath,  (string) $main);
                    $disk->put($thumbPath, (string) $thumb);

                    $user->profile_photo_path = $mainPath;
                    if ($hasThumbCol) $user->profile_photo_thumb = $thumbPath;

                    $avatarChanged = true;
                }
            } catch (\Throwable $e) {
                Log::warning('Avatar process failed', [
                    'user_id' => $user->id,
                    'driver'  => $driver instanceof ImagickDriver ? 'imagick' : ($driver instanceof GdDriver ? 'gd' : 'none'),
                    'webp'    => $supportsWebp ? 'yes' : 'no',
                    'error'   => $e->getMessage(),
                ]);

                return back()->with('toast', [
                    'type'     => 'error',
                    'message'  => 'ไฟล์รูปไม่ถูกต้องหรืออ่านไม่ได้',
                    'position' => 'tc',
                    'timeout'  => 3600,
                    'size'     => 'md',
                ]);
            }
        }

        // 6) Save
        $user->save();

        $message = 'อัปเดตโปรไฟล์เรียบร้อย';
        if ($avatarChanged)     $message .= ' — อัปเดตรูปโปรไฟล์ใหม่แล้ว';
        elseif ($avatarRemoved) $message .= ' — ลบรูปโปรไฟล์เรียบร้อย';

        // Breeze tests expect redirect to /profile (show page)
        return Redirect::route('profile.show')->with('toast', [
            'type'     => 'success',
            'message'  => $message,
            'position' => 'tc',
            'timeout'  => 2800,
            'size'     => 'lg',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $disk = Storage::disk('public');
        $toDelete = array_values(array_filter([
            $user->profile_photo_path ?: null,
            Schema::hasColumn('users', 'profile_photo_thumb') ? ($user->profile_photo_thumb ?: null) : null,
        ]));
        if ($toDelete) $disk->delete($toDelete);

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('toast', [
            'type'     => 'success',
            'message'  => 'ลบบัญชีเรียบร้อย',
            'position' => 'tc',
            'timeout'  => 3200,
            'size'     => 'lg',
        ]);
    }
}
