<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Menu::LOCATIONS as $location => $name) {
            Menu::firstOrCreate(['location' => $location], ['name' => $name, 'is_active' => true]);
        }
    }
}
