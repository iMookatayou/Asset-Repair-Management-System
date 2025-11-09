<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * อนุญาตให้ผู้ใช้ที่ล็อกอินทุกคนอัปเดตโปรไฟล์ของตนเองได้ (สอดคล้อง Breeze tests)
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * ถ้าไม่ผ่าน authorize → flash toast + โยน exception
     */
    protected function failedAuthorization()
    {
        // ยิง toast error
        session()->flash('toast', [
            'type'     => 'error',
            'message'  => 'คุณไม่มีสิทธิ์ดำเนินการนี้',
            'position' => 'tc',
            'timeout'  => 3800,
            'size'     => 'md',
        ]);

        throw new AuthorizationException('This action is unauthorized.');
    }

    /**
     * (ออปชัน) หยุดตรวจทันทีเมื่อเจอ error แรกของแต่ละฟิลด์
     */
    public function stopOnFirstFailure(): bool
    {
        return true;
    }

    /**
     * เตรียมข้อมูลก่อนตรวจสอบ (trim/normalize)
     */
    protected function prepareForValidation(): void
    {
        $name  = $this->input('name');
        $email = $this->input('email');

        $this->merge([
            'name'  => is_string($name)  ? trim($name) : $name,
            'email' => is_string($email) ? trim(strtolower($email)) : $email,
        ]);
    }

    /**
     * กติกาการตรวจสอบ
     */
    public function rules(): array
    {
        $userId = (int) ($this->user()?->id ?? 0);

        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($userId),
            ],
            // เพิ่มเติม: ถ้าฟอร์มมีแผนก ให้ตรวจสอบให้ชัด
            'department' => ['nullable', 'string', 'exists:departments,code'],
            // หมายเหตุ: avatar / remove_avatar ให้ validate ใน Controller ตามที่คุณออกแบบไว้แล้ว
        ];
    }

    /**
     * ใส่ toast ตอน validate ไม่ผ่าน (ดึงข้อความแรก)
     */
    protected function failedValidation(Validator $validator)
    {
        $first = $validator->errors()->first() ?: 'ข้อมูลไม่ถูกต้อง';
        session()->flash('toast', [
            'type'     => 'error',
            'message'  => $first,
            'position' => 'tc',
            'timeout'  => 3800,
            'size'     => 'md',
        ]);

        // ดำเนินการตามปกติของ Laravel (redirect พร้อม errors)
        throw (new ValidationException($validator))
            ->redirectTo($this->getRedirectUrl());
    }

    /**
     * ชื่อฟิลด์ภาษาไทย (ไว้แสดงใน error message)
     */
    public function attributes(): array
    {
        return [
            'name'       => 'ชื่อ',
            'email'      => 'อีเมล',
            'department' => 'แผนก',
        ];
    }

    /**
     * ข้อความ error กำหนดเอง (กรณีต้องการความชัดเจน)
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'กรุณากรอกชื่อ',
            'name.max'          => 'ชื่อต้องไม่เกิน :max ตัวอักษร',
            'email.required'    => 'กรุณากรอกอีเมล',
            'email.email'       => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.max'         => 'อีเมลต้องไม่เกิน :max ตัวอักษร',
            'email.unique'      => 'อีเมลนี้ถูกใช้ไปแล้ว',
            'department.exists' => 'แผนกที่เลือกไม่ถูกต้อง',
        ];
    }
}
