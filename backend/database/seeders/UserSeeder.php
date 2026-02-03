<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(['email' => 'admin@unn.local'], [
            'name' => 'Administrador',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);
    }
}