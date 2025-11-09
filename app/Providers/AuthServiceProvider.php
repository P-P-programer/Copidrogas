<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        // Analítica: SOLO Super Admin
        Gate::define('view-analytics', function (\App\Models\User $user) {
            return $user->role_id === Role::SUPER_ADMIN;
        });

        // Gestionar productos (Stock): Super Admin, Admin, Proveedor
        Gate::define('manage-products', function (\App\Models\User $user) {
            return in_array($user->role_id, [Role::SUPER_ADMIN, Role::ADMIN, Role::PROVEEDOR]);
        });

        // Gestionar usuarios: Admin y Super Admin
        Gate::define('manage-users', function (\App\Models\User $user) {
            return in_array($user->role_id, [Role::ADMIN, Role::SUPER_ADMIN]);
        });
    }
}
