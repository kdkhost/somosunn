<?php

namespace Tests\Unit;

use App\Models\Event;
use Carbon\Carbon;
use Tests\TestCase;

class EventScannerWindowTest extends TestCase
{
    public function test_scanner_without_end_at_stays_open_until_end_of_event_day(): void
    {
        $event = new Event([
            'start_at' => '2026-03-09 10:00:00',
            'end_at' => null,
        ]);

        $this->assertSame('2026-03-09 23:59:59', $event->scannerDeadlineAt()?->format('Y-m-d H:i:s'));
        $this->assertTrue($event->isScannerOpen(Carbon::parse('2026-03-09 22:30:00')));
        $this->assertTrue($event->isScannerOpen(Carbon::parse('2026-03-09 23:59:59')));
        $this->assertFalse($event->isScannerOpen(Carbon::parse('2026-03-10 00:00:00')));
        $this->assertTrue($event->isScannerExpired(Carbon::parse('2026-03-10 00:00:00')));
    }

    public function test_scanner_with_end_at_closes_at_exact_event_end(): void
    {
        $event = new Event([
            'start_at' => '2026-03-09 10:00:00',
            'end_at' => '2026-03-09 13:00:00',
        ]);

        $this->assertSame('2026-03-09 13:00:00', $event->scannerDeadlineAt()?->format('Y-m-d H:i:s'));
        $this->assertTrue($event->isScannerOpen(Carbon::parse('2026-03-09 13:00:00')));
        $this->assertFalse($event->isScannerOpen(Carbon::parse('2026-03-09 13:00:01')));
        $this->assertTrue($event->isScannerExpired(Carbon::parse('2026-03-09 13:00:01')));
    }

    public function test_scanner_status_message_mentions_end_of_day_when_event_has_no_end_at(): void
    {
        $event = new Event([
            'start_at' => '2026-03-09 10:00:00',
            'end_at' => null,
        ]);

        $message = $event->scannerStatusMessage(Carbon::parse('2026-03-10 00:00:00'));

        $this->assertStringContainsString('23:59', $message);
        $this->assertStringContainsString('09/03/2026', $message);
    }

    public function test_scanner_location_messages_reflect_mode_configuration(): void
    {
        $exactEvent = new Event([
            'latitude' => -23.550520,
            'longitude' => -46.633308,
            'scanner_restriction_mode' => Event::SCANNER_RESTRICTION_EXACT,
        ]);

        $radiusEvent = new Event([
            'latitude' => -23.550520,
            'longitude' => -46.633308,
            'scanner_restriction_mode' => Event::SCANNER_RESTRICTION_RADIUS,
            'scanner_radius_meters' => 1500,
        ]);

        $disabledEvent = new Event([
            'scanner_restriction_mode' => Event::SCANNER_RESTRICTION_DISABLED,
        ]);

        $this->assertStringContainsString('5m', $exactEvent->scannerLocationMessage());
        $this->assertSame('1,5 km', $radiusEvent->scannerFormattedRadius());
        $this->assertStringContainsString('sem restricao', strtolower($disabledEvent->scannerLocationMessage()));
    }
}
