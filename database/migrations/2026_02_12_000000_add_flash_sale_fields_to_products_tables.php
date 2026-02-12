<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addFlashSaleColumns('courses');
        $this->addFlashSaleColumns('mentorships');
        $this->addFlashSaleColumns('events');
    }

    public function down(): void
    {
        $this->dropFlashSaleColumns('courses');
        $this->dropFlashSaleColumns('mentorships');
        $this->dropFlashSaleColumns('events');
    }

    private function addFlashSaleColumns(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'flash_sale_price')) {
                $table->decimal('flash_sale_price', 10, 2)->nullable()->after('price');
            }

            if (!Schema::hasColumn($tableName, 'flash_sale_ends_at')) {
                $table->dateTime('flash_sale_ends_at')->nullable()->after('flash_sale_price');
            }
        });
    }

    private function dropFlashSaleColumns(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $columns = [];

            if (Schema::hasColumn($tableName, 'flash_sale_price')) {
                $columns[] = 'flash_sale_price';
            }
            if (Schema::hasColumn($tableName, 'flash_sale_ends_at')) {
                $columns[] = 'flash_sale_ends_at';
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};

