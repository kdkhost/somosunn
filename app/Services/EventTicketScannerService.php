<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;

class EventTicketScannerService
{
    public function __construct(
        protected EventScannerAttemptLogger $attemptLogger
    ) {
    }

    public function validateForEvent(
        Event $event,
        string $ticketCode,
        ?float $latitude,
        ?float $longitude,
        User $scannerUser,
        PointsService $pointsService,
        string $context
    ): array {
        $now = now();

        if (!$event->isScannerOpen($now)) {
            $message = $event->scannerStatusMessage($now);

            $this->attemptLogger->log(
                $event,
                null,
                $scannerUser,
                $context,
                false,
                'scanner_closed',
                $message,
                $ticketCode,
                $latitude,
                $longitude
            );

            return $this->failure($message);
        }

        $locationFailure = $this->validateLocation($event, $ticketCode, $latitude, $longitude, $scannerUser, $context);
        if ($locationFailure !== null) {
            return $locationFailure;
        }

        $registration = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('ticket_code', $ticketCode)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->with('user')
            ->first();

        if (!$registration) {
            $message = 'Ingresso nao encontrado ou invalido para este evento.';

            $this->attemptLogger->log(
                $event,
                null,
                $scannerUser,
                $context,
                false,
                'ticket_not_found',
                $message,
                $ticketCode,
                $latitude,
                $longitude
            );

            return $this->failure($message);
        }

        return $this->completeValidation($event, $registration, $ticketCode, $latitude, $longitude, $scannerUser, $pointsService, $context);
    }

    public function validateQuickScan(
        string $ticketCode,
        ?float $latitude,
        ?float $longitude,
        User $scannerUser,
        PointsService $pointsService,
        string $context
    ): array {
        $registration = EventRegistration::query()
            ->where('ticket_code', $ticketCode)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->with(['event', 'user'])
            ->first();

        if (!$registration || !$registration->event) {
            $message = 'Ingresso nao encontrado ou invalido.';

            $this->attemptLogger->log(
                null,
                $registration,
                $scannerUser,
                $context,
                false,
                'ticket_not_found',
                $message,
                $ticketCode,
                $latitude,
                $longitude
            );

            return $this->failure($message);
        }

        $event = $registration->event;

        if (!$scannerUser->isAdmin() && $event->user_id !== $scannerUser->id) {
            $message = 'Voce nao tem permissao para validar ingressos deste evento.';

            $this->attemptLogger->log(
                $event,
                $registration,
                $scannerUser,
                $context,
                false,
                'forbidden',
                $message,
                $ticketCode,
                $latitude,
                $longitude
            );

            return $this->failure($message, [
                'event_title' => $event->title,
            ]);
        }

        $now = now();
        if (!$event->isScannerOpen($now)) {
            $message = $event->scannerStatusMessage($now);

            $this->attemptLogger->log(
                $event,
                $registration,
                $scannerUser,
                $context,
                false,
                'scanner_closed',
                $message,
                $ticketCode,
                $latitude,
                $longitude
            );

            return $this->failure($message, [
                'event_title' => $event->title,
            ]);
        }

        $locationFailure = $this->validateLocation($event, $ticketCode, $latitude, $longitude, $scannerUser, $context, $registration);
        if ($locationFailure !== null) {
            $locationFailure['event_title'] = $event->title;

            return $locationFailure;
        }

        return $this->completeValidation($event, $registration, $ticketCode, $latitude, $longitude, $scannerUser, $pointsService, $context, [
            'event_title' => $event->title,
        ]);
    }

    protected function validateLocation(
        Event $event,
        string $ticketCode,
        ?float $latitude,
        ?float $longitude,
        User $scannerUser,
        string $context,
        ?EventRegistration $registration = null
    ): ?array {
        if (!$event->hasScannerLocationConstraint()) {
            return null;
        }

        if ($latitude === null || $longitude === null) {
            $message = 'Validacao por localizacao ativa: permita o GPS. O scanner precisa estar em ate '
                . $event->scannerLocationRadiusMeters() . 'm do ponto do evento.';

            $this->attemptLogger->log(
                $event,
                $registration,
                $scannerUser,
                $context,
                false,
                'gps_required',
                $message,
                $ticketCode,
                $latitude,
                $longitude
            );

            return $this->failure($message);
        }

        $distanceMeters = $event->distanceToScannerLocationMeters($latitude, $longitude);

        if ($distanceMeters === null || !$event->isWithinScannerLocationRadius($latitude, $longitude)) {
            $roundedDistance = $distanceMeters === null ? null : (int) round($distanceMeters);
            $message = 'Validacao por localizacao ativa: o scanner precisa estar em ate '
                . $event->scannerLocationRadiusMeters() . 'm do ponto do evento.'
                . ($roundedDistance !== null ? ' Distancia atual: ' . $roundedDistance . 'm.' : '');

            $this->attemptLogger->log(
                $event,
                $registration,
                $scannerUser,
                $context,
                false,
                'outside_radius',
                $message,
                $ticketCode,
                $latitude,
                $longitude,
                $distanceMeters
            );

            return $this->failure($message, [
                'distance_meters' => $roundedDistance,
            ]);
        }

        return null;
    }

    protected function completeValidation(
        Event $event,
        EventRegistration $registration,
        string $ticketCode,
        ?float $latitude,
        ?float $longitude,
        User $scannerUser,
        PointsService $pointsService,
        string $context,
        array $extraPayload = []
    ): array {
        if ($registration->check_in_at) {
            $message = 'Este ingresso ja foi validado em ' . $registration->check_in_at->format('d/m/Y H:i');

            $this->attemptLogger->log(
                $event,
                $registration,
                $scannerUser,
                $context,
                false,
                'already_checked_in',
                $message,
                $ticketCode,
                $latitude,
                $longitude,
                $event->distanceToScannerLocationMeters($latitude, $longitude)
            );

            return $this->failure($message, array_merge($extraPayload, [
                'participant_name' => $this->participantName($registration),
            ]));
        }

        $registration->update([
            'check_in_at' => now(),
        ]);

        try {
            if ($registration->user_id && $registration->user) {
                $pointsService->award($registration->user, 'event_scan_participant');
            }

            if ($event->user_id) {
                $organizer = User::find($event->user_id);
                if ($organizer) {
                    $pointsService->award($organizer, 'event_scan_organizer');
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Erro ao atribuir pontos no check-in do evento: ' . $e->getMessage());
        }

        $message = 'Ingresso validado com sucesso! Check-in realizado.';
        $participantName = $this->participantName($registration);

        $this->attemptLogger->log(
            $event,
            $registration,
            $scannerUser,
            $context,
            true,
            'validated',
            $message,
            $ticketCode,
            $latitude,
            $longitude,
            $event->distanceToScannerLocationMeters($latitude, $longitude)
        );

        return array_merge([
            'success' => true,
            'message' => $message,
            'participant_name' => $participantName,
        ], $extraPayload);
    }

    protected function participantName(EventRegistration $registration): string
    {
        return (string) ($registration->user?->name ?? data_get($registration, 'name') ?? 'Participante');
    }

    protected function failure(string $message, array $payload = []): array
    {
        return array_merge([
            'success' => false,
            'message' => $message,
        ], $payload);
    }
}
