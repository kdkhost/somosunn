<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (Schema::hasTable('coupons')) {
                return;
            }
        } catch (\Throwable $e) {
            // Shared hosting pode bloquear queries em information_schema.
            // Seguimos e deixamos o create() tratar o "already exists" sem quebrar o migrate.
        }

        try {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();

                $table->string('code', 40)->unique();
                $table->string('name')->nullable();
                $table->text('description')->nullable();

                $table->string('discount_type', 20); // percent|fixed
                $table->decimal('discount_value', 10, 2)->default(0);

                $table->boolean('is_active')->default(true);

                $table->string('applies_to', 20)->default('all'); // all|event|course|mentorship
                $table->unsignedBigInteger('applies_to_id')->nullable();

                $table->decimal('min_amount', 10, 2)->nullable();
                $table->unsignedInteger('max_uses')->nullable();
                $table->unsignedInteger('max_uses_per_user')->nullable();

                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();

                $table->timestamps();

                $table->index(['is_active', 'code']);
                $table->index(['applies_to', 'applies_to_id']);
            });
        } catch (\Throwable $e) {
            // Shared hosting/legacy DB: table may already exist even if Schema::hasTable() can't detect it.
            // If so, ignore and let the ensure migration add/normalize columns.
            $message = (string) $e->getMessage();
            $errorInfo = property_exists($e, 'errorInfo') ? $e->errorInfo : null;
            $sqlState = is_array($errorInfo) && isset($errorInfo[0]) ? (string) $errorInfo[0] : null;
            $driverCode = is_array($errorInfo) && isset($errorInfo[1]) ? (string) $errorInfo[1] : null;

            $alreadyExists =
                $sqlState === '42S01'
                || $driverCode === '1050'
                || strpos($message, 'already exists') !== false
                || strpos($message, 'Base table or view already exists') !== false;

            if ($alreadyExists) {
                return;
            }

            throw $e;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
