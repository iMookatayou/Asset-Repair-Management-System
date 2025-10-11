<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MaintenanceRequest;
use App\Models\Asset;
use App\Models\User;

class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            MaintenanceRequest::STATUS_PENDING,
            MaintenanceRequest::STATUS_IN_PROGRESS,
            MaintenanceRequest::STATUS_COMPLETED,
        ]);

        $priority = $this->faker->randomElement([
            MaintenanceRequest::PRIORITY_LOW,
            MaintenanceRequest::PRIORITY_MEDIUM,
            MaintenanceRequest::PRIORITY_HIGH,
            MaintenanceRequest::PRIORITY_URGENT,
        ]);

        $requestDate = $this->faker->dateTimeBetween('-11 months', 'now');
        $assigned    = $this->faker->optional(0.8)->dateTimeBetween($requestDate, '+7 days');
        $completed   = $status === MaintenanceRequest::STATUS_COMPLETED
            ? $this->faker->dateTimeBetween($assigned ?? $requestDate, '+14 days')
            : null;

        $cost = $status === MaintenanceRequest::STATUS_COMPLETED
            ? $this->faker->randomFloat(2, 200, 15000)
            : $this->faker->optional(0.3, 0.0)->randomFloat(2, 0, 15000);

        return [
            'asset_id'       => Asset::query()->inRandomOrder()->value('id') ?? Asset::factory(),
            'reporter_id'    => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'title'          => 'แจ้งซ่อม: '.$this->faker->words(3, true),
            'description'    => $this->faker->sentence(12),
            'priority'       => $priority,
            'status'         => $status,
            'technician_id'  => $this->faker->optional(0.7)->randomElement([
                                    User::query()->inRandomOrder()->value('id') ?? User::factory()
                                ]),
            'request_date'   => $requestDate,
            'assigned_date'  => $assigned,
            'completed_date' => $completed,
            'remark'         => $this->faker->optional()->sentence(10),
            'cost'           => $cost,
        ];
    }
}
