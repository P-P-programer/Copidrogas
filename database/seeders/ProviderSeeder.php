<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['name' => 'Laboratorios Genéricos S.A.S.', 'location' => 'Bogotá'],
            ['name' => 'Distribuidora FarmaPlus', 'location' => 'Medellín'],
            ['name' => 'Salud y Vida Ltda.', 'location' => 'Cali'],
            ['name' => 'Farmacéutica Andina', 'location' => 'Barranquilla'],
        ];

        Provider::insert($providers);
    }
}
