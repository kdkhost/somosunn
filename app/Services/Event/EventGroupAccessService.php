<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

namespace App\Services\Event;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Rules\WhatsAppGroupLinkRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EventGroupAccessService
{
    public function resolveJoinLink(Event $event, User $user, ?Request $request = null): string
    {
        $link = trim((string) $event->whatsapp_group_link);
        if ($link === '' || !WhatsAppGroupLinkRule::passes($link)) {
            $this->logDenied($event, $user, $request, 'group_link_unavailable');

            throw ValidationException::withMessages([
                'group_link' => 'O grupo deste evento ainda nao esta disponivel.',
            ]);
        }

        $registration = $this->findEligibleRegistration($event, $user);
        if (!$registration) {
            $this->logDenied($event, $user, $request, 'registration_not_confirmed');

            throw ValidationException::withMessages([
                'registration' => 'Confirme sua inscricao antes de acessar o grupo do evento.',
            ]);
        }

        if (!$registration->joined_group_at) {
            $registration->forceFill(['joined_group_at' => now()])->save();
        }

        Log::info('Acesso ao grupo do evento liberado', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_id' => $registration->id,
            'ip_hash' => $request ? hash('sha256', (string) $request->ip()) : null,
        ]);

        return $link;
    }

    public function findEligibleRegistration(Event $event, User $user): ?EventRegistration
    {
        return EventRegistration::query()
            ->where('event_id', (int) $event->id)
            ->where('user_id', (int) $user->id)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->whereIn('payment_status', [
                EventRegistration::PAYMENT_PAID,
                EventRegistration::PAYMENT_FREE,
            ])
            ->latest('id')
            ->first();
    }

    private function logDenied(Event $event, User $user, ?Request $request, string $reason): void
    {
        Log::warning('Acesso ao grupo do evento negado', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'reason' => $reason,
            'ip_hash' => $request ? hash('sha256', (string) $request->ip()) : null,
        ]);
    }
}
