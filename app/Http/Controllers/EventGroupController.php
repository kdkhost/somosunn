<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\Event\EventGroupAccessService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventGroupController extends Controller
{
    public function __construct(private EventGroupAccessService $groupAccess)
    {
    }

    public function join(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user, 403);

        try {
            $link = $this->groupAccess->resolveJoinLink($event, $user, $request);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', $this->firstValidationMessage($exception));
        }

        return redirect()->away($link);
    }

    private function firstValidationMessage(ValidationException $exception): string
    {
        $messages = collect($exception->errors())->flatten();

        return (string) ($messages->first() ?: 'Não foi possível liberar o grupo deste evento.');
    }
}
