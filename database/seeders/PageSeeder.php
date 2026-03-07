<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\CmsPageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensurePagesTableExists();
        Page::resetTableAvailabilityCache();

        CmsPageCatalog::upsertDefaults();
    }

    private function ensurePagesTableExists(): void
    {
        if (Schema::hasTable('pages')) {
            return;
        }

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 255)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }
}
