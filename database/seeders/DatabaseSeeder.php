<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Raw material', 'Finish goods', 'Spares', 'Machines', 'Others'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
