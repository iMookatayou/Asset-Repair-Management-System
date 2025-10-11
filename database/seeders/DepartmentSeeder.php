<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'IT',  'name' => 'ฝ่ายไอที'],
            ['code' => 'HR',  'name' => 'ฝ่ายทรัพยากรบุคคล'],
            ['code' => 'FIN', 'name' => 'ฝ่ายการเงิน'],
            ['code' => 'OPS', 'name' => 'ฝ่ายปฏิบัติการ'],
            ['code' => 'PRC', 'name' => 'ฝ่ายจัดซื้อ'],
            ['code' => 'FAC', 'name' => 'อาคารสถานที่'],
        ];

        foreach ($rows as $r) {
            Department::firstOrCreate(['code' => $r['code']], ['name' => $r['name']]);
        }
    }
}
