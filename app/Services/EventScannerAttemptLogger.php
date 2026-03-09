<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventScannerLog;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class EventScannerAttemptLogger
{
    protected static ?bool $tableAvailable = null;

    public function log(
        ?Event $event,
        ?EventRegistration $registration,
        ?User $scannerUser,
        string $context,
        bool $success,
        string $statusCode,
        string $message,
        ?string $ticketCode = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?float $distanceMeters = null
    ): void {
        try {
            if (!$this->tableAvailable()) {
                return;
            }

            EventScannerLog::query()->create([
                'event_id' => $event?->id,
                'event_registration_id' => $registration?->id,
                'scanner_user_id' => $scannerUser?->id,
                'ticket_code' => $ticketCode,
                'scanner_context' => $context,
                'outcome' => $success ? 'success' : 'error',
                'status_code' => $statusCode,
                'message' => $message,
                'distance_meters' => $distanceMeters,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'ip_address' => request()?->ip(),
                'user_agent' => (string) (request()?->userAgent() ?? ''),
                'attempted_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Falha ao gravar log do scanner de eventos: ' . $e->getMessage());
        }
    }

    public static function resetTableAvailabilityCache(): void
    {
        static::$tableAvailable = null;
    }

    protected function tableAvailable(): bool
    {
        if (static::$tableAvailable !== null) {
            return static::$tableAvailable;
        }

        try {
            static::$tableAvailable = Schema::hasTable('event_scanner_logs');
        } catch (\Throwable) {
            static::$tableAvailable = false;
        }

        return static::$tableAvailable;
    }
}
