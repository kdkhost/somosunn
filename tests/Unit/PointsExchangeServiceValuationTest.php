<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\PointsExchangeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PointsExchangeServiceValuationTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-points-exchange-service.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_it_persists_and_converts_unnbit_values(): void
    {
        $service = app(PointsExchangeService::class);

        $service->persist([
            'base_points' => 100,
            'unit_value' => 0.375,
            'usd_reference_rate' => 5.42,
            'market_note' => 'ajuste manual por dolar',
        ]);

        $settings = $service->settings();

        $this->assertSame('UNNBIT', $settings['coin_name']);
        $this->assertSame(100, $settings['base_points']);
        $this->assertSame(37.50, (float) $settings['base_amount']);
        $this->assertSame(0.3750, (float) $settings['unit_value_brl']);
        $this->assertSame(5.4200, (float) $settings['usd_reference_rate']);
        $this->assertSame('ajuste manual por dolar', $settings['market_note']);
        $this->assertNotEmpty($settings['last_repriced_at']);
        $this->assertSame(4, $service->moneyToPoints(1.50));
        $this->assertSame(3.75, $service->pointsToMoney(10));
        $this->assertSame(37.50, (float) $settings['valuation_table'][3]['amount']);
    }

    public function test_it_can_derive_unit_value_from_batch_amount(): void
    {
        $service = app(PointsExchangeService::class);

        $service->persist([
            'base_points' => 50,
            'base_amount' => 20,
            'usd_reference_rate' => 5.00,
        ]);

        $this->assertSame(0.4000, $service->getUnitValue());
        $this->assertSame(20.00, $service->getBaseAmount());
        $this->assertSame(50, $service->moneyToPoints(20.00));
    }
}
