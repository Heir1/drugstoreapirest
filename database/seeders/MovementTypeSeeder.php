<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MovementType;

class MovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MovementType::insert([
            ['name' => 'Entée', 'description' => 'Stock entry'],
            ['name' => 'Sortie', 'description' => 'Stock exit'],
            ['name' => 'Ajustment entréé', 'description' => 'Entry Stock adjustment'],
            ['name' => 'Ajustment sortie', 'description' => 'Exit Stock adjustment'],
        ]);
    }
}
