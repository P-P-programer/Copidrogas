<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Medicamentos'],
            ['name' => 'Cuidado personal'],
            ['name' => 'Vitaminas y suplementos'],
            ['name' => 'Higiene'],
            ['name' => 'Dispositivos médicos'],
        ];

        Category::insert($categories);
    }
}
