<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $couponsExists = false;
        try {
            $couponsExists = Schema::hasTable('coupons');
        } catch (\Throwable $e) {
            $couponsExists = false;
        }

        if (!$couponsExists) {
            try {
                Schema::create('coupons', function (Blueprint $table) {
                    $table->id();
                    $table->string('code')->unique();
                    $table->enum('type', ['percent','fixed'])->default('percent');
                    $table->decimal('value', 10, 2);
                    $table->integer('max_uses')->nullable();
                    $table->integer('max_uses_per_user')->nullable();
                    $table->boolean('auto_generate')->default(false);
                    $table->boolean('active')->default(true);
                    $table->string('target_type')->nullable(); // plan, course, event, mentorship, global
                    $table->unsignedBigInteger('target_id')->nullable();
                    $table->timestamp('starts_at')->nullable();
                    $table->timestamp('ends_at')->nullable();
                    $table->timestamps();
                });
            } catch (\Throwable $e) {
                $message = (string) $e->getMessage();
                $errorInfo = property_exists($e, 'errorInfo') ? $e->errorInfo : null;
                $sqlState = is_array($errorInfo) && isset($errorInfo[0]) ? (string) $errorInfo[0] : null;
                $driverCode = is_array($errorInfo) && isset($errorInfo[1]) ? (string) $errorInfo[1] : null;

                $alreadyExists =
                    $sqlState === '42S01'
                    || $driverCode === '1050'
                    || strpos($message, 'already exists') !== false
                    || strpos($message, 'Base table or view already exists') !== false;

                if (!$alreadyExists) {
                    throw $e;
                }
            }
        }

        $usagesExists = false;
        try {
            $usagesExists = Schema::hasTable('coupon_usages');
        } catch (\Throwable $e) {
            $usagesExists = false;
        }

        if (!$usagesExists) {
            try {
                Schema::create('coupon_usages', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('coupon_id');
                    $table->unsignedBigInteger('user_id');
                    $table->timestamp('used_at');
                    $table->index('coupon_id');
                    $table->index('user_id');
                });
            } catch (\Throwable $e) {
                $message = (string) $e->getMessage();
                $errorInfo = property_exists($e, 'errorInfo') ? $e->errorInfo : null;
                $sqlState = is_array($errorInfo) && isset($errorInfo[0]) ? (string) $errorInfo[0] : null;
                $driverCode = is_array($errorInfo) && isset($errorInfo[1]) ? (string) $errorInfo[1] : null;

                $alreadyExists =
                    $sqlState === '42S01'
                    || $driverCode === '1050'
                    || strpos($message, 'already exists') !== false
                    || strpos($message, 'Base table or view already exists') !== false;

                if (!$alreadyExists) {
                    throw $e;
                }
            }

            // Foreign keys are optional on shared hosting/legacy DBs
            try {
                Schema::table('coupon_usages', function (Blueprint $table) {
                    $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
