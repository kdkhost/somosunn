<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventGroupController extends Controller
{
    public function join(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $link = trim((string) $event->whatsapp_group_link);
        if ($link === '') {
            return redirect()->route('events.show', $event)->with('error', 'O grupo deste evento ainda não está disponível.');
        }

        $registration = EventRegistration::query()
            ->where('event_id', (int) $event->id)
            ->where('user_id', (int) $user->id)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->latest('id')
            ->first();

        if (!$registration) {
            return redirect()->route('events.show', $event)->with('error', 'Confirme sua inscrição antes de acessar o grupo do evento.');
        }

        if (!$registration->joined_group_at) {
            $registration->forceFill(['joined_group_at' => now()])->save();
        }

        return redirect()->away($link);
    }
}
