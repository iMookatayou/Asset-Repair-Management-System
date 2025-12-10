<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-users');
    }

    public function rules(): array
    {
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof User ? $routeUser->getKey() : $routeUser;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'citizen_id' => [
                'required',
                'digits:13',
                Rule::unique('users', 'citizen_id')->ignore($userId),
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'password' => [
                'nullable',
                'confirmed',
                'min:8',
            ],

            'department' => [
                'nullable',
                'string',
                'max:100',
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'technician', 'computer_officer']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'citizen_id.required' => 'กรุณากรอกเลขบัตรประชาชน',
            'citizen_id.digits'   => 'เลขบัตรประชาชนต้องมี 13 หลัก',
            'citizen_id.unique'   => 'เลขบัตรประชาชนนี้ถูกใช้ไปแล้ว',

            'role.in' => 'Role ต้องเป็น admin, technician หรือ บุคลากร Member เท่านั้น',
        ];
    }
}
