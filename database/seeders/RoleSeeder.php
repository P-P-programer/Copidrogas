<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 4, 'name' => 'super_admin'],
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'proveedor'],
            ['id' => 3, 'name' => 'usuario'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }
    }
}
