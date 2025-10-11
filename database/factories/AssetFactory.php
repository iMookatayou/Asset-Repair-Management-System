<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Asset;
use App\Models\Department;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition()
    {
        $cats  = ['คอมพิวเตอร์', 'เครื่องพิมพ์', 'เครือข่าย', 'ยานพาหนะ', 'เครื่องมือ', 'เฟอร์นิเจอร์'];
        $brands = ['HP','Dell','Lenovo','Acer','Canon','Brother','Cisco','MikroTik','Yamaha','3M'];
        $locs  = ['สำนักงานใหญ่', 'อาคาร A', 'อาคาร B', 'คลังสินค้า', 'สาขาเชียงใหม่', 'สาขาภูเก็ต'];

        return [
            'asset_code'      => 'ASSET-'.fake()->unique()->numerify('#####'),
            'name'            => fake()->words(2, true),
            'category'        => fake()->randomElement($cats),
            'brand'           => fake()->randomElement($brands),
            'model'           => strtoupper(fake()->bothify('??-###')),
            'serial_number'   => strtoupper(fake()->unique()->bothify('SN########')),
            'location'        => fake()->randomElement($locs),
            'purchase_date'   => fake()->dateTimeBetween('-5 years', '-6 months'),
            'warranty_expire' => fake()->dateTimeBetween('-1 years', '+2 years'),
            'status'          => fake()->randomElement(['active','in_repair','disposed']),
            'department_id'   => Department::inRandomOrder()->value('id') ?? null,
        ];
    }
}
