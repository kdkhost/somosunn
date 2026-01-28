<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder {
    public function run() {
        User::create(['name'=>'Admin','email'=>'admin@unn.local','password'=>bcrypt('password'),'role'=>'admin']);
    }
}
