<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('gateway');
            $table->string('gateway_id')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedDecimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('BRL');
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->nullableMorphs('payable'); // can be enrollment, order, etc
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};