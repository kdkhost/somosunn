@php
    $registration = $registration ?? null;
    $buttonClass = $buttonClass ?? 'event-action-button event-action-button-primary';
    $wrapClass = $wrapClass ?? 'mt-3';
    $canShowGroupButton = $event
        && $registration
        && in_array($registration->status, \App\Models\EventRegistration::COUNTED_STATUSES, true)
        && (
            in_array($registration->payment_status, [
                \App\Models\EventRegistration::PAYMENT_PAID,
                \App\Models\EventRegistration::PAYMENT_FREE,
            ], true)
            || $registration->payment_status === null
        )
        && method_exists($event, 'hasWhatsappGroup')
        && $event->hasWhatsappGroup();
@endphp

@if($canShowGroupButton)
    <form method="POST" action="{{ route('events.group.join', $event) }}" class="{{ $wrapClass }}">
        @csrf
        <button type="submit" class="{{ $buttonClass }}">
            <i class="fab fa-whatsapp mr-2"></i>Entrar no grupo do evento
        </button>
    </form>
    @if($registration->joined_group_at)
        <p class="mt-2 text-xs font-bold text-slate-500">
            Entrada registrada em {{ $registration->joined_group_at->format('d/m/Y H:i') }}.
        </p>
    @endif
@endif
