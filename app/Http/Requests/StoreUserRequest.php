<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-users');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],

            // ✅ บังคับใช้ citizen_id เป็นตัวหลัก
            'citizen_id'  => [
                'required',
                'digits:13',
                'unique:users,citizen_id',
            ],

            // ✅ email ไม่บังคับแล้ว แต่ถ้ามีต้อง unique
            'email'       => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password'    => ['required', 'confirmed', 'min:8'],
            'department'  => ['nullable', 'string', 'max:100'],

            // ยังใช้ role ชุดนี้ตามที่กำหนดไว้เดิม
            'role'        => [
                'required',
                Rule::in(['admin','technician','computer_officer']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'citizen_id.required' => 'กรุณากรอกเลขบัตรประชาชน',
            'citizen_id.digits'   => 'เลขบัตรประชาชนต้องมีความยาว 13 หลัก',
            'citizen_id.unique'   => 'เลขบัตรประชาชนนี้ถูกใช้ไปแล้ว',
            'role.in'             => 'Role ต้องเป็น admin, technician หรือ บุคลากร Member เท่านั้น',
        ];
    }
}
