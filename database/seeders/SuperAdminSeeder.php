<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::create([
            'name' => 'Admin',
            'email' => 'felipemendoza3247@gmail.com',
            'password' => bcrypt('admin123'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(), // Puedes dejarlo null si no usas verificación
        ]);
    }
}
