<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\MaintenanceRequest as MR;
use App\Policies\MaintenanceRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        MR::class   => MaintenanceRequestPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // จัดการผู้ใช้: admin หรือหัวหน้า (supervisor)
        Gate::define('manage-users', function (User $user): bool {
            return $user->role === User::ROLE_ADMIN || $user->isSupervisor();
        });

        // Dashboard งานซ่อม: admin, หัวหน้า + ทีมปฏิบัติการ (worker)
        Gate::define('view-repair-dashboard', function (User $user): bool {
            return $user->role === User::ROLE_ADMIN || $user->isSupervisor() || $user->isWorker();
        });

        // หน้างานของฉัน: admin, หัวหน้า, หรือทีมปฏิบัติการ (worker)
        Gate::define('view-my-jobs', function (User $user): bool {
            return $user->role === User::ROLE_ADMIN || $user->isSupervisor() || $user->isWorker();
        });

        // ใช้ในบาง Blade: admin, หัวหน้า, หรือช่าง
        Gate::define('tech-only', function (User $user): bool {
            return $user->role === User::ROLE_ADMIN || $user->isSupervisor() || $user->isWorker();
        });
    }
}
