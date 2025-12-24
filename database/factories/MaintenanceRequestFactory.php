<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MaintenanceRequest;
use App\Models\Asset;
use App\Models\User;
use Carbon\Carbon;

class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            MaintenanceRequest::STATUS_PENDING,
            MaintenanceRequest::STATUS_ACCEPTED,
            MaintenanceRequest::STATUS_IN_PROGRESS,
            MaintenanceRequest::STATUS_RESOLVED,
            MaintenanceRequest::STATUS_CLOSED,
        ]);

        $priority = $this->faker->randomElement([
            MaintenanceRequest::PRIORITY_LOW,
            MaintenanceRequest::PRIORITY_MEDIUM,
            MaintenanceRequest::PRIORITY_HIGH,
            MaintenanceRequest::PRIORITY_URGENT,
        ]);

        $requestDate = Carbon::instance(
            $this->faker->dateTimeBetween('-11 months', 'now')
        );

        $acceptedAt = in_array($status, ['accepted','in_progress','resolved','closed'], true)
            ? $requestDate->copy()->addHours(rand(1, 48))
            : null;

        $startedAt = in_array($status, ['in_progress','resolved','closed'], true)
            ? optional($acceptedAt)->copy()->addHours(rand(1, 24))
            : null;

        $resolvedAt = in_array($status, ['resolved','closed'], true)
            ? optional($startedAt)->copy()->addHours(rand(2, 72))
            : null;

        $closedAt = $status === 'closed'
            ? optional($resolvedAt)->copy()->addHours(rand(1, 24))
            : null;

        // เลือกเฉพาะทีมช่างจริง
        $technicianId = User::query()
            ->whereIn('role', User::teamRoles())
            ->inRandomOrder()
            ->value('id');

        return [
            'asset_id'      => Asset::query()->inRandomOrder()->value('id') ?? Asset::factory(),
            'reporter_id'   => User::query()->inRandomOrder()->value('id') ?? User::factory(),

            'title'         => 'แจ้งซ่อม: '.$this->faker->words(3, true),
            'description'   => $this->faker->sentence(12),

            'priority'      => $priority,
            'status'        => $status,

            'technician_id' => $technicianId,

            'request_date'  => $requestDate,
            'assigned_date' => $acceptedAt,
            'accepted_at'   => $acceptedAt,
            'started_at'    => $startedAt,
            'resolved_at'   => $resolvedAt,
            'closed_at'     => $closedAt,
            'completed_date'=> $closedAt,

            'cost'          => in_array($status, ['resolved','closed'], true)
                ? $this->faker->randomFloat(2, 200, 15000)
                : null,

            'resolution_note' => in_array($status, ['resolved','closed'], true)
                ? $this->faker->sentence(10)
                : null,
        ];
    }
}
