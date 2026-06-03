<?php

namespace Tests\Feature;

use App\Http\Controllers\Panel\MarketplaceAccountingController;
use App\Models\Conversation;
use App\Models\Enrollment;
use App\Models\Order;
use App\Notifications\JobVacancyPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class DatabasePerformanceGuardTest extends TestCase
{
    public function test_marketplace_accounting_summary_accepts_incremental_iterables(): void
    {
        $controller = app(MarketplaceAccountingController::class);
        $method = new ReflectionMethod($controller, 'buildSummary');
        $method->setAccessible(true);

        $sales = (function () {
            yield new Order([
                'status' => 'paid',
                'total_amount' => 100,
                'fee_amount' => 5,
                'platform_fee_amount' => 10,
            ]);
        })();

        $purchases = (function () {
            yield new Order([
                'status' => 'paid',
                'total_amount' => 30,
            ]);
        })();

        $summary = $method->invoke($controller, $sales, $purchases);

        $this->assertSame(1, $summary['sales_count']);
        $this->assertSame(85.0, $summary['sales_net']);
        $this->assertSame(30.0, $summary['purchase_net']);
        $this->assertSame(55.0, $summary['overall_net']);
    }

    public function test_marketplace_accounting_period_filter_does_not_use_coalesce(): void
    {
        $controller = app(MarketplaceAccountingController::class);
        $method = new ReflectionMethod($controller, 'buildQueries');
        $method->setAccessible(true);

        [$salesQuery] = $method->invoke($controller, 1, Request::create('/', 'GET'));
        $sql = strtolower($salesQuery->toSql());

        $this->assertStringNotContainsString('coalesce', $sql);
        $this->assertStringContainsString('paid_at', $sql);
        $this->assertStringContainsString('manual_approved_at', $sql);
        $this->assertStringContainsString('created_at', $sql);
    }

    public function test_pending_certificates_are_filtered_in_one_correlated_query(): void
    {
        $sql = strtolower(Enrollment::query()->withoutCertificate()->toSql());

        $this->assertStringContainsString('not exists', $sql);
        $this->assertStringContainsString('certificates', $sql);
    }

    public function test_private_conversation_lookup_counts_participants_in_database(): void
    {
        $sql = strtolower(Conversation::query()->privateBetween(1, 2)->toSql());

        $this->assertStringContainsString('users_count', $sql);
        $this->assertStringContainsString('having', $sql);
    }

    public function test_mass_job_notification_is_queued(): void
    {
        $notification = new JobVacancyPublished((object) ['id' => 1]);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
    }
}
