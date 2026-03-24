<?php

namespace Database\Seeders;

use Database\Seeders\Support\LegalPagesPublisher;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        LegalPagesPublisher::publishMissing();
    }
}
