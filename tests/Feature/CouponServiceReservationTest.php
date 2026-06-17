<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CouponServiceReservationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!app()->environment('testing')) {
            $this->markTestSkipped('Teste permitido somente no ambiente testing.');
        }

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Banco de teste indisponível: ' . $e->getMessage());
        }

        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('discount_type')->default('percent');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('applies_to')->default('all');
            $table->unsignedBigInteger('applies_to_id')->nullable();
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('max_uses_per_user')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status', 20)->default('reserved');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamp('reserved_until')->nullable();
            $table->timestamps();

            $table->unique(['coupon_id', 'order_id']);
        });
    }

    protected function tearDown(): void
    {
        if (app()->environment('testing')) {
            try {
                Schema::dropIfExists('coupon_redemptions');
                Schema::dropIfExists('coupons');
            } catch (\Throwable $e) {
                // Banco de teste pode estar indisponível localmente.
            }
        }

        parent::tearDown();
    }

    public function test_reserving_same_coupon_for_same_order_is_idempotent(): void
    {
        $coupon = $this->createCoupon('EVENTO37');

        $service = app(CouponService::class);

        $first = DB::transaction(function () use ($service, $coupon) {
            return $service->reserveRedemption($coupon, 12, 161, 37);
        });

        $second = DB::transaction(function () use ($service, $coupon) {
            return $service->reserveRedemption($coupon, 12, 161, 37);
        });

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, DB::table('coupon_redemptions')->count());
        $this->assertDatabaseHas('coupon_redemptions', [
            'coupon_id' => $coupon->id,
            'user_id' => 12,
            'order_id' => 161,
            'status' => 'reserved',
        ]);
    }

    public function test_current_order_redemption_is_not_counted_again_in_coupon_limits(): void
    {
        $coupon = $this->createCoupon('LIMITADO');

        app(CouponService::class)->reserveRedemption($coupon, 12, 161, 37);

        $result = app(CouponService::class)->validateAndCalculateLocked(
            'LIMITADO',
            CouponService::CONTEXT_EVENT,
            10,
            12,
            37,
            161
        );

        $this->assertSame($coupon->id, $result['coupon']->id);
        $this->assertSame(37.0, (float) $result['discount_amount']);
    }

    private function createCoupon(string $code): Coupon
    {
        return Coupon::create([
            'code' => $code,
            'discount_type' => 'fixed',
            'discount_value' => 37,
            'is_active' => true,
            'applies_to' => CouponService::CONTEXT_EVENT,
            'applies_to_id' => 10,
            'max_uses' => 1,
            'max_uses_per_user' => 1,
        ]);
    }
}
