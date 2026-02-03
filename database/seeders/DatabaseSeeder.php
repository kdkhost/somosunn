<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (!class_exists(\Database\Seeders\MailTemplateSeeder::class) && file_exists(database_path('seeders/MailTemplateSeeder.php'))) {
            require_once database_path('seeders/MailTemplateSeeder.php');
        }
        $this->call([
            SettingsSeeder::class,
            UserSeeder::class,
            \Database\Seeders\PointsRulesSeeder::class,
            PermissionsSeeder::class,
            PlansSeederUtf8::class,
            MailTemplateSeeder::class,
        ]);
    }
}
