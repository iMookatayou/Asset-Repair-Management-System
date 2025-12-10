<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\EloquentFactories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // รัน citizen_id ให้ไม่ซ้ำกันใน factory
        static $citizenRunning = 2000000000000; // 13 หลัก
        $citizenRunning++;

        return [
            'name'              => fake()->name(),
            'citizen_id'        => (string) $citizenRunning,                  // ✅ ใส่เลขบัตรประชาชน
            'email'             => fake()->unique()->safeEmail(),            // ยังมี email ไว้ reset / notify ได้
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            // ใส่ default เพิ่มได้ถ้าอยาก เช่น role / department
            // 'role'           => 'member',
            // 'department'     => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
