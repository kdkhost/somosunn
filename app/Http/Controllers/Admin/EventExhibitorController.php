<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventExhibitor\EventExhibitorActionRequest;
use App\Http\Requests\EventExhibitor\EventExhibitorSettingsRequest;
use App\Models\Event;
use App\Models\EventExhibitorRegistration;
use App\Models\Order;
use App\Services\AuditLogService;
use App\Services\EventExhibitorService;
use App\Services\OrderRefundService;
use App\Services\OrderSettlementService;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventExhibitorController extends Controller
{
    protected string $viewPrefix = 'admin.events.exhibitors';
    protected string $routePrefix = 'admin.events.exhibitors';

    public function __construct(protected EventExhibitorService $exhibitorService)
    {
    }

    public function index(Request $request, Event $event)
    {
        $this->ensureAccess($event);
        $this->exhibitorService->expireInvalidReservations($event);

        $registrations = $this->registrationsQuery($event, $request)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view($this->viewPrefix . '.index', [
            'event' => $event->fresh(),
            'registrations' => $registrations,
            'summary' => $this->summary($event),
            'status' => $event->exhibitorSalesStatus(),
            'currentBatch' => $this->exhibitorService->currentBatch($event),
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function settings(EventExhibitorSettingsRequest $request, Event $event)
    {
        $this->ensureAccess($event);

        $data = $request->validated();
        $countedSlots = $this->exhibitorService->countedSlots($event);
        $newTotal = (int) ($data['exhibitor_total_slots'] ?? 0);

        if ($newTotal > 0 && $newTotal < $countedSlots) {
            return $this->jsonError('A quantidade total nao pode ser menor que as areas ja vendidas/reservadas.', 422);
        }

        if ($request->boolean('remove_exhibitor_area_image') && $event->exhibitor_area_image) {
            UploadStorage::delete($event->exhibitor_area_image);
            $data['exhibitor_area_image'] = null;
        }

        if ($request->hasFile('exhibitor_area_image')) {
            if ($event->exhibitor_area_image) {
                UploadStorage::delete($event->exhibitor_area_image);
            }

            $data['exhibitor_area_image'] = UploadStorage::storeUploadedFile(
                $request->file('exhibitor_area_image'),
                'event-exhibitor-areas',
                null,
                ['prefix' => 'exhibitor-area']
            );
        }

        $data['exhibitor_sales_enabled'] = $request->boolean('exhibitor_sales_enabled');
        $data['exhibitor_includes_ticket'] = $request->boolean('exhibitor_includes_ticket');
        $data['exhibitor_show_publicly'] = $request->has('exhibitor_show_publicly')
            ? $request->boolean('exhibitor_show_publicly')
            : true;

        unset($data['remove_exhibitor_area_image']);

        $old = $event->only(array_keys($data));
        $event->update($data);
        $event->refresh();

        $this->audit('event_exhibitor_settings_updated', $event, $old, $event->only(array_keys($data)));

        return $this->jsonOk('Configuracoes de expositores salvas com sucesso.', [
            'summary' => $this->summary($event),
            'status' => $event->exhibitorSalesStatus(),
            'image_url' => $event->exhibitor_area_image_url,
        ]);
    }

    public function toggle(EventExhibitorActionRequest $request, Event $event)
    {
        $this->ensureAccess($event);

        $old = ['exhibitor_sales_enabled' => (bool) $event->exhibitor_sales_enabled];
        $event->forceFill(['exhibitor_sales_enabled' => !$event->exhibitor_sales_enabled])->save();
        $event->refresh();

        $this->audit('event_exhibitor_sales_toggled', $event, $old, [
            'exhibitor_sales_enabled' => (bool) $event->exhibitor_sales_enabled,
        ]);

        return $this->jsonOk($event->exhibitor_sales_enabled ? 'Venda de expositor ativada.' : 'Venda de expositor desativada.', [
            'enabled' => (bool) $event->exhibitor_sales_enabled,
            'status' => $event->exhibitorSalesStatus(),
        ]);
    }

    public function registrations(Request $request, Event $event)
    {
        $this->ensureAccess($event);
        $this->exhibitorService->expireInvalidReservations($event);

        $rows = $this->registrationsQuery($event, $request)
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(fn (EventExhibitorRegistration $registration) => $this->serializeRegistration($registration))
            ->values();

        return $this->jsonOk('Inscricoes carregadas.', [
            'rows' => $rows,
            'summary' => $this->summary($event),
        ]);
    }

    public function confirm(EventExhibitorActionRequest $request, Event $event, EventExhibitorRegistration $registration, OrderSettlementService $settlementService)
    {
        $this->ensureAccess($event);
        $this->ensureRegistrationBelongsToEvent($event, $registration);

        $order = $registration->order;
        if ($order && (string) $order->status !== 'paid') {
            $settlementService->settleAsPaid($order, [
                'manual_approval' => true,
                'approver_id' => (int) Auth::id(),
                'transaction_id' => 'MANUAL-EXHIBITOR-' . $order->id . '-' . now()->format('YmdHis'),
                'payment_method' => 'manual_approval',
                'queue_invoice_email' => true,
                'send_notifications' => true,
                'gateway_data' => [
                    'source' => 'admin_event_exhibitor_manual_confirm',
                    'approved_by' => (int) Auth::id(),
                    'approved_at' => now()->toIso8601String(),
                ],
            ]);
        }

        $old = $registration->only(['status', 'payment_status', 'paid_at']);
        $registration->update([
            'status' => EventExhibitorRegistration::STATUS_CONFIRMED,
            'payment_status' => EventExhibitorRegistration::PAYMENT_PAID,
            'paid_at' => $registration->paid_at ?: now(),
            'metadata' => array_merge($registration->metadata ?? [], [
                'manual_confirmed_at' => now()->toIso8601String(),
                'manual_confirmed_by' => (int) Auth::id(),
                'manual_confirm_reason' => (string) $request->input('reason', ''),
            ]),
        ]);

        $this->audit('event_exhibitor_registration_confirmed', $registration, $old, $registration->only(['status', 'payment_status', 'paid_at']));

        return $this->jsonOk('Inscricao de expositor confirmada.', [
            'registration' => $this->serializeRegistration($registration->fresh(['order', 'user'])),
            'summary' => $this->summary($event),
        ]);
    }

    public function cancel(EventExhibitorActionRequest $request, Event $event, EventExhibitorRegistration $registration)
    {
        $this->ensureAccess($event);
        $this->ensureRegistrationBelongsToEvent($event, $registration);

        $old = $registration->only(['status', 'payment_status', 'cancelled_at']);
        $registration->update([
            'status' => EventExhibitorRegistration::STATUS_CANCELLED,
            'payment_status' => EventExhibitorRegistration::PAYMENT_CANCELLED,
            'cancelled_at' => now(),
            'metadata' => array_merge($registration->metadata ?? [], [
                'cancelled_by' => (int) Auth::id(),
                'cancelled_reason' => (string) $request->input('reason', ''),
                'cancelled_at' => now()->toIso8601String(),
            ]),
        ]);

        if ($registration->order && (string) $registration->order->status === 'pending') {
            $registration->order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'metadata' => array_merge($registration->order->metadata ?? [], [
                    'cancelled_by' => (int) Auth::id(),
                    'cancelled_reason' => 'event_exhibitor_registration_cancelled',
                ]),
            ]);
        }

        $this->audit('event_exhibitor_registration_cancelled', $registration, $old, $registration->only(['status', 'payment_status', 'cancelled_at']));

        return $this->jsonOk('Inscricao de expositor cancelada.', [
            'registration' => $this->serializeRegistration($registration->fresh(['order', 'user'])),
            'summary' => $this->summary($event),
        ]);
    }

    public function refund(EventExhibitorActionRequest $request, Event $event, EventExhibitorRegistration $registration, OrderRefundService $refundService)
    {
        $this->ensureAccess($event);
        $this->ensureRegistrationBelongsToEvent($event, $registration);

        $order = $registration->order;
        if (!$order) {
            return $this->jsonError('Pedido da inscricao nao encontrado.', 404);
        }

        try {
            $amount = $this->parseAmount($request->input('amount'));
            $order = $refundService->refund($order, $amount);
        } catch (\Throwable $e) {
            return $this->jsonError('Erro ao processar reembolso: ' . $e->getMessage(), 422);
        }

        $this->audit('event_exhibitor_registration_refunded', $registration, [], [
            'order_id' => (int) $order->id,
            'amount' => $amount,
        ]);

        return $this->jsonOk('Reembolso processado.', [
            'registration' => $this->serializeRegistration($registration->fresh(['order', 'user'])),
            'summary' => $this->summary($event),
        ]);
    }

    public function export(Request $request, Event $event): StreamedResponse
    {
        $this->ensureAccess($event);
        $this->audit('event_exhibitor_registrations_exported', $event, [], ['event_id' => (int) $event->id]);

        $filename = 'expositores-evento-' . $event->id . '-' . now()->format('Ymd-His') . '.csv';
        $rows = $this->registrationsQuery($event, $request)->latest('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            if (!$handle) {
                return;
            }

            fputcsv($handle, [
                'ID',
                'Status',
                'Pagamento',
                'Nome',
                'E-mail',
                'Telefone',
                'Documento',
                'Empresa',
                'CNPJ Empresa',
                'Marca',
                'Quantidade',
                'Valor Unitario',
                'Valor Total',
                'Lote',
                'Pedido',
                'Pago em',
                'Criado em',
            ]);

            foreach ($rows as $registration) {
                fputcsv($handle, [
                    $registration->id,
                    $registration->status,
                    $registration->payment_status,
                    $registration->name,
                    $registration->email,
                    $registration->phone,
                    $registration->document,
                    $registration->company_name,
                    $registration->company_document,
                    $registration->brand_name,
                    $registration->quantity,
                    number_format((float) $registration->unit_price, 2, '.', ''),
                    number_format((float) $registration->total_price, 2, '.', ''),
                    $registration->batch_label,
                    $registration->order_id,
                    optional($registration->paid_at)->format('d/m/Y H:i'),
                    optional($registration->created_at)->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function registrationsQuery(Event $event, Request $request): Builder
    {
        $query = EventExhibitorRegistration::query()
            ->with(['order:id,status,total_amount,gateway,payment_method,transaction_id', 'user:id,name,email'])
            ->where('event_id', (int) $event->id);

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $paymentStatus = trim((string) $request->input('payment_status', ''));
        if ($paymentStatus !== '') {
            $query->where('payment_status', $paymentStatus);
        }

        $search = trim((string) ($request->input('search') ?: $request->input('q', '')));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('company_name', 'like', '%' . $search . '%')
                    ->orWhere('brand_name', 'like', '%' . $search . '%')
                    ->orWhere('document', 'like', '%' . $search . '%')
                    ->orWhere('company_document', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    protected function summary(Event $event): array
    {
        $event = $event->fresh();
        $registrations = $event->exhibitorRegistrations()->get();
        $counted = $this->exhibitorService->countedSlots($event);
        $remaining = $this->exhibitorService->remainingSlots($event);

        return [
            'total_slots' => (int) ($event->exhibitor_total_slots ?? 0),
            'sold_slots' => $counted,
            'remaining_slots' => $remaining,
            'expected_revenue' => (float) $registrations
                ->whereNotIn('status', [
                    EventExhibitorRegistration::STATUS_CANCELLED,
                    EventExhibitorRegistration::STATUS_EXPIRED,
                    EventExhibitorRegistration::STATUS_REFUNDED,
                ])
                ->sum('total_price'),
            'confirmed_revenue' => (float) $registrations
                ->whereIn('status', [
                    EventExhibitorRegistration::STATUS_PAID,
                    EventExhibitorRegistration::STATUS_CONFIRMED,
                ])
                ->sum('total_price'),
            'registrations_count' => (int) $registrations->count(),
        ];
    }

    protected function serializeRegistration(EventExhibitorRegistration $registration): array
    {
        return [
            'id' => (int) $registration->id,
            'status' => (string) $registration->status,
            'payment_status' => (string) $registration->payment_status,
            'name' => (string) $registration->name,
            'email' => (string) $registration->email,
            'phone' => (string) $registration->phone,
            'company_name' => (string) $registration->company_name,
            'company_document' => (string) $registration->company_document,
            'brand_name' => (string) $registration->brand_name,
            'quantity' => (int) $registration->quantity,
            'unit_price' => (float) $registration->unit_price,
            'total_price' => (float) $registration->total_price,
            'batch_label' => (string) $registration->batch_label,
            'order_id' => (int) $registration->order_id,
            'order_status' => (string) ($registration->order?->status ?? ''),
            'paid_at' => optional($registration->paid_at)->format('d/m/Y H:i'),
            'created_at' => optional($registration->created_at)->format('d/m/Y H:i'),
        ];
    }

    protected function ensureAccess(Event $event): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $canManage = $user->isAdmin()
            || $user->hasPermission('events.exhibitors.manage')
            || $user->hasPermission('events.edit');

        abort_unless($canManage, 403, 'Voce nao tem permissao para gerenciar expositores.');

        if (!$user->isAdmin() && (int) $event->user_id !== (int) $user->id) {
            abort(403, 'Voce nao tem permissao para gerenciar este evento.');
        }
    }

    protected function ensureRegistrationBelongsToEvent(Event $event, EventExhibitorRegistration $registration): void
    {
        abort_unless((int) $registration->event_id === (int) $event->id, 404);
    }

    protected function jsonOk(string $message, array $data = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function jsonError(string $message, int $status = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => 'error',
            'message' => $message,
        ], $status);
    }

    protected function audit(string $action, mixed $target, array $oldValues = [], array $newValues = []): void
    {
        try {
            app(AuditLogService::class)->log(
                AuditLogService::ACTION_ADMIN_ACTION,
                $target instanceof \Illuminate\Database\Eloquent\Model ? $target : null,
                $oldValues,
                $newValues,
                [
                    'event_exhibitor_action' => $action,
                    'user_id' => (int) Auth::id(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar auditoria de expositor', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function parseAmount(mixed $value): ?float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? round((float) $value, 2) : null;
    }
}
