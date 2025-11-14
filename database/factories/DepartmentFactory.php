<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $nameTh = $this->faker->unique()->company();
        $nameEn = Str::title($nameTh);
        return [
            'code'    => strtoupper(Str::substr(Str::slug($nameTh,''),0,5)) . rand(10,99),
            'name_th' => $nameTh,
            'name_en' => $nameEn,
        ];
    }
}
