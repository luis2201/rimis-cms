<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'site_name' => 'RIMIS',
                'site_description' => 'Red de Investigación Multidisciplinaria',
                'site_slogan' => 'Impulsando la investigación científica',
                'email' => 'investigacion@itsup.edu.ec',
                'status' => true
            ]
        );
    }
}
