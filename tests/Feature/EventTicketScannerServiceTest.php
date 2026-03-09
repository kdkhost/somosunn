<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventScannerLog;
use App\Models\User;
use App\Services\EventScannerAttemptLogger;
use App\Services\EventTicketScannerService;
use App\Services\PointsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventTicketScannerServiceTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-event-ticket-scanner-service.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        EventScannerAttemptLogger::resetTableAvailabilityCache();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('all_day')->default(false);
            $table->boolean('published')->default(true);
            $table->boolean('is_ticket_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default(EventRegistration::STATUS_CONFIRMED);
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('ticket_code')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamps();
        });

        Schema::create('event_scanner_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('event_registration_id')->nullable();
            $table->unsignedBigInteger('scanner_user_id')->nullable();
            $table->string('ticket_code')->nullable();
            $table->string('scanner_context', 50);
            $table->string('outcome', 20);
            $table->string('status_code', 60);
            $table->text('message');
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        EventScannerAttemptLogger::resetTableAvailabilityCache();

        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_validation_within_fifty_meters_is_accepted_and_logged(): void
    {
        [$event, $registration, $organizer] = $this->seedScannerScenario();

        $pointsService = $this->createMock(PointsService::class);
        $pointsService->method('award')->willReturn(true);

        $result = app(EventTicketScannerService::class)->validateForEvent(
            $event,
            $registration->ticket_code,
            -22.90653,
            -43.1729,
            $organizer,
            $pointsService,
            'panel_event'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('validated', EventScannerLog::query()->first()->status_code);
        $this->assertSame('success', EventScannerLog::query()->first()->outcome);
        $this->assertNotNull($registration->fresh()->check_in_at);
        $this->assertLessThanOrEqual(50.0, (float) EventScannerLog::query()->first()->distance_meters);
    }

    public function test_validation_outside_fifty_meters_is_rejected_and_logged(): void
    {
        [$event, $registration, $organizer] = $this->seedScannerScenario('DISTANCIA-2');

        $pointsService = $this->createMock(PointsService::class);
        $pointsService->method('award')->willReturn(true);

        $result = app(EventTicketScannerService::class)->validateForEvent(
            $event,
            $registration->ticket_code,
            -22.90620,
            -43.1729,
            $organizer,
            $pointsService,
            'panel_event'
        );

        $log = EventScannerLog::query()->first();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('50m', $result['message']);
        $this->assertSame('outside_radius', $log->status_code);
        $this->assertSame('error', $log->outcome);
        $this->assertNull($registration->fresh()->check_in_at);
        $this->assertGreaterThan(50.0, (float) $log->distance_meters);
    }

    private function seedScannerScenario(string $ticketCode = 'DISTANCIA-1'): array
    {
        $organizer = User::create([
            'name' => 'Organizador',
            'email' => $ticketCode . '@organizer.test',
            'password' => 'secret',
            'role' => 'member',
            'level' => 'elite',
        ]);

        $participant = User::create([
            'name' => 'Participante',
            'email' => $ticketCode . '@participant.test',
            'password' => 'secret',
            'role' => 'member',
            'level' => 'cliente',
        ]);

        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => 'Evento com geolocalizacao',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'latitude' => -22.9068,
            'longitude' => -43.1729,
            'published' => true,
            'is_ticket_enabled' => true,
        ]);

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'status' => EventRegistration::STATUS_CONFIRMED,
            'price' => 0,
            'quantity' => 1,
            'ticket_code' => $ticketCode,
        ]);

        return [$event, $registration, $organizer];
    }
}
