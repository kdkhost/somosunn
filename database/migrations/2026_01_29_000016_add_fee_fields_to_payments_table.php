<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('fee_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('fee_percentage', 5, 2)->nullable()->after('fee_amount');
            $table->boolean('fee_passed')->default(false)->after('fee_percentage');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['fee_amount','fee_percentage','fee_passed']);
        });
    }
};