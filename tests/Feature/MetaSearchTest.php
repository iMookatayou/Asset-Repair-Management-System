<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\AssetCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MetaSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user for sanctum authentication (assuming default user factory sets required fields)
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_departments_search_filters_results(): void
    {
        Department::factory()->create(['name_th' => 'แผนกคอมพิวเตอร์', 'name_en' => 'Computer Department', 'code' => 'COMP']);
        Department::factory()->create(['name_th' => 'แผนกไฟฟ้า', 'name_en' => 'Electrical', 'code' => 'ELEC']);
        Department::factory()->create(['name_th' => 'แผนกการแพทย์', 'name_en' => 'Medical', 'code' => 'MEDX']);

    // Search by code (simpler deterministic for collation)
    $res = $this->getJson('/api/meta/departments?q=ELEC')->assertOk()->json('data');
        $this->assertCount(1, $res);
        $this->assertEquals('ELEC', $res[0]['code']);

        // Search for English fragment
        $res2 = $this->getJson('/api/meta/departments?q=Computer')->assertOk()->json('data');
        $this->assertCount(1, $res2);
        $this->assertEquals('COMP', $res2[0]['code']);

        // Empty query returns limited list (<=50, here 3)
        $res3 = $this->getJson('/api/meta/departments')->assertOk()->json('data');
        $this->assertCount(3, $res3);
    }

    public function test_categories_search_filters_results(): void
    {
        AssetCategory::query()->create(['name' => 'Laptop', 'slug' => 'laptop', 'color' => null, 'description' => null, 'is_active' => true]);
        AssetCategory::query()->create(['name' => 'Printer', 'slug' => 'printer', 'color' => null, 'description' => null, 'is_active' => true]);
        AssetCategory::query()->create(['name' => 'Scanner', 'slug' => 'scanner', 'color' => null, 'description' => null, 'is_active' => true]);

        $res = $this->getJson('/api/meta/categories?q=print')->assertOk()->json('data');
        $this->assertCount(1, $res);
        $this->assertEquals('Printer', $res[0]['name']);

        $res2 = $this->getJson('/api/meta/categories?q=sca')->assertOk()->json('data');
        $this->assertCount(1, $res2);
        $this->assertEquals('Scanner', $res2[0]['name']);

        $res3 = $this->getJson('/api/meta/categories')->assertOk()->json('data');
        $this->assertCount(3, $res3);
    }
}
