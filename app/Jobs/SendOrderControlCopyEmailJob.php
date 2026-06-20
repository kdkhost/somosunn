<?php

namespace App\Jobs;

use App\Mail\OrderControlCopyMail;
use App\Models\Order;
use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;
use App\Services\OrderControlCopyRecipientService;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderControlCopyEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public array $backoff = [15, 60, 180];

    public function __construct(public int $orderId)
    {
        EmailQueueSettings::applyToQueueable($this);
    }

    public function middleware(): array
    {
        return [new RateLimited('order_control_email')];
    }

    public function handle(
        SystemMailLayoutData $layoutData,
        SystemMailTemplateService $templates,
        OrderControlCopyRecipientService $recipients
    ): void {
        $order = Order::with(['items', 'user', 'seller', 'invoice'])->find($this->orderId);
        if (!$order || (string) $order->status !== 'paid') {
            return;
        }

        $bcc = $recipients->emails();
        if (empty($bcc)) {
            Log::warning('order.control_copy.no_recipients', ['order_id' => $this->orderId]);
            return;
        }

        if (!$this->claim()) {
            return;
        }

        try {
            $rendered = $templates->renderOrCreate(
                'order_sale_control_copy',
                $this->templateData($order, $layoutData->make()),
                $this->defaultTemplate()
            );

            if (!$rendered) {
                throw new \RuntimeException('Template de cópia de controle está inativo.');
            }

            Mail::send(new OrderControlCopyMail(
                (string) $rendered['subject'],
                (string) $rendered['html'],
                $bcc
            ));

            $this->markAsSent(count($bcc));
        } catch (\Throwable $exception) {
            $this->releaseClaim();
            Log::error('order.control_copy.send_failed', [
                'order_id' => $this->orderId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function claim(): bool
    {
        return DB::transaction(function (): bool {
            $order = Order::query()->whereKey($this->orderId)->lockForUpdate()->first();
            if (!$order) {
                return false;
            }

            $metadata = is_array($order->metadata) ? $order->metadata : [];
            if (data_get($metadata, 'emails.control_copy_sent_at')) {
                return false;
            }

            $claimedAt = data_get($metadata, 'emails.control_copy_claimed_at');
            if (is_string($claimedAt) && $claimedAt !== '') {
                try {
                    if (Carbon::parse($claimedAt)->isAfter(now()->subMinutes(10))) {
                        return false;
                    }
                } catch (\Throwable) {
                    // Reivindicação inválida ou antiga pode ser substituída.
                }
            }

            $metadata['emails'] = is_array($metadata['emails'] ?? null) ? $metadata['emails'] : [];
            $metadata['emails']['control_copy_claimed_at'] = now()->toIso8601String();
            $order->metadata = $metadata;
            $order->save();

            return true;
        });
    }

    private function markAsSent(int $recipientCount): void
    {
        DB::transaction(function () use ($recipientCount): void {
            $order = Order::query()->whereKey($this->orderId)->lockForUpdate()->first();
            if (!$order) {
                return;
            }

            $metadata = is_array($order->metadata) ? $order->metadata : [];
            $metadata['emails'] = is_array($metadata['emails'] ?? null) ? $metadata['emails'] : [];
            unset($metadata['emails']['control_copy_claimed_at']);
            $metadata['emails']['control_copy_sent_at'] = now()->toIso8601String();
            $metadata['emails']['control_copy_recipient_count'] = $recipientCount;
            $order->metadata = $metadata;
            $order->save();
        });
    }

    private function releaseClaim(): void
    {
        try {
            $order = Order::query()->find($this->orderId);
            if (!$order) {
                return;
            }

            $metadata = is_array($order->metadata) ? $order->metadata : [];
            if (is_array($metadata['emails'] ?? null)) {
                unset($metadata['emails']['control_copy_claimed_at']);
            }
            $order->metadata = $metadata;
            $order->save();
        } catch (\Throwable $exception) {
            Log::warning('order.control_copy.release_claim_failed', [
                'order_id' => $this->orderId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $layout
     * @return array<string, mixed>
     */
    private function templateData(Order $order, array $layout): array
    {
        $timezone = (string) config('app.timezone', 'America/Sao_Paulo');
        $createdAt = $order->created_at?->copy()->timezone($timezone);
        $paidAt = $order->paid_at?->copy()->timezone($timezone);

        return [
            'site' => [
                'name' => (string) ($layout['siteName'] ?? config('app.name', 'SOMOS UNN')),
                'url' => url('/'),
                'logo' => (string) ($layout['logoUrl'] ?? ''),
                'primary_color' => (string) ($layout['primaryColor'] ?? '#1F5EDB'),
            ],
            'order' => [
                'id' => (string) $order->id,
                'status' => 'Pago',
                'sale_type' => $order->saleTypeLabel(),
                'context' => (string) data_get($order->metadata, 'context', 'Não informado'),
                'created_at' => $createdAt?->format('d/m/Y H:i') ?? '',
                'paid_at' => $paidAt?->format('d/m/Y H:i') ?? '',
                'gross_total' => $this->money($order->gross_amount),
                'discount_total' => $this->money($order->financial_discount_amount),
                'net_total' => $this->money($order->net_amount),
                'gateway_fee' => $this->money((float) ($order->fee_amount ?? 0)),
                'platform_fee' => $this->money((float) ($order->platform_fee_amount ?? 0)),
                'currency' => (string) ($order->currency ?: 'BRL'),
                'gateway' => (string) ($order->gateway ?: 'Não informado'),
                'payment_method' => (string) ($order->payment_method ?: 'Não informado'),
                'transaction_id' => (string) ($order->transaction_id ?: 'Não informado'),
                'coupon_code' => (string) ($order->coupon_code ?: 'Não utilizado'),
                'manual_approval' => $order->is_manual_approval ? 'Sim' : 'Não',
                'items_html' => $this->itemsHtml($order),
            ],
            'buyer' => [
                'name' => (string) ($order->user?->name ?? 'Não informado'),
                'email' => (string) ($order->user?->email ?? 'Não informado'),
                'phone' => (string) ($order->user?->phone ?? 'Não informado'),
                'document' => (string) ($order->user?->doc ?? 'Não informado'),
                'address' => $order->buyerAddress() ?: 'Não informado',
            ],
            'seller' => [
                'name' => (string) ($order->seller?->name ?? 'Plataforma'),
                'email' => (string) ($order->seller?->email ?? 'Não informado'),
            ],
            'invoice' => [
                'number' => (string) ($order->invoice?->number ?? 'Não emitida'),
                'status' => (string) ($order->invoice?->status ?? 'Não emitida'),
            ],
            'links' => [
                'admin_order_url' => route('admin.orders.show', $order->id),
            ],
        ];
    }

    private function itemsHtml(Order $order): string
    {
        if ($order->items->isEmpty()) {
            return '<p>Nenhum item registrado.</p>';
        }

        $rows = $order->items->map(function ($item): string {
            $quantity = max(1, (int) ($item->quantity ?? 1));
            $unitPrice = (float) ($item->price ?? 0);
            $total = $unitPrice * $quantity;
            $type = Order::SALE_TYPE_LABELS[(string) $item->item_type] ?? (string) ($item->item_type ?: 'Outro');

            return '<tr>'
                . '<td style="padding:8px;border:1px solid #dbe3ef;">' . e((string) ($item->title ?: 'Item')) . '</td>'
                . '<td style="padding:8px;border:1px solid #dbe3ef;">' . e($type) . '</td>'
                . '<td style="padding:8px;border:1px solid #dbe3ef;text-align:center;">' . $quantity . '</td>'
                . '<td style="padding:8px;border:1px solid #dbe3ef;text-align:right;">' . e($this->money($unitPrice)) . '</td>'
                . '<td style="padding:8px;border:1px solid #dbe3ef;text-align:right;">' . e($this->money($total)) . '</td>'
                . '</tr>';
        })->implode('');

        return '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
            . '<thead><tr style="background:#edf4ff;">'
            . '<th style="padding:8px;border:1px solid #dbe3ef;text-align:left;">Item</th>'
            . '<th style="padding:8px;border:1px solid #dbe3ef;text-align:left;">Tipo</th>'
            . '<th style="padding:8px;border:1px solid #dbe3ef;">Qtd.</th>'
            . '<th style="padding:8px;border:1px solid #dbe3ef;text-align:right;">Unitário</th>'
            . '<th style="padding:8px;border:1px solid #dbe3ef;text-align:right;">Total</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table>';
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultTemplate(): array
    {
        return [
            'name' => 'Controle: Cópia de venda confirmada',
            'category' => 'vendas',
            'locale' => 'pt-BR',
            'subject' => 'Controle de venda #{{order.id}} - {{order.sale_type}} - {{order.net_total}}',
            'body' => '<h2 style="margin:0 0 16px;color:#0f172a;">Venda confirmada</h2>
<p style="margin:0 0 18px;color:#475569;">Cópia de controle enviada automaticamente por CCO ao Administrador e ao Super Administrador.</p>
<div style="background:#f8fafc;border:1px solid #dbe3ef;padding:16px;margin-bottom:18px;">
  <p><strong>Pedido:</strong> #{{order.id}} | <strong>Tipo:</strong> {{order.sale_type}} | <strong>Status:</strong> {{order.status}}</p>
  <p><strong>Criado em:</strong> {{order.created_at}} | <strong>Pago em:</strong> {{order.paid_at}}</p>
  <p><strong>Contexto:</strong> {{order.context}} | <strong>Aprovação manual:</strong> {{order.manual_approval}}</p>
</div>
<h3>Comprador</h3>
<p><strong>Nome:</strong> {{buyer.name}}<br><strong>E-mail:</strong> {{buyer.email}}<br><strong>Telefone:</strong> {{buyer.phone}}<br><strong>CPF/CNPJ:</strong> {{buyer.document}}<br><strong>Endereço:</strong> {{buyer.address}}</p>
<h3>Vendedor</h3>
<p><strong>Nome:</strong> {{seller.name}}<br><strong>E-mail:</strong> {{seller.email}}</p>
<h3>Itens da venda</h3>
{!! $order[\'items_html\'] ?? \'\' !!}
<h3>Financeiro</h3>
<p><strong>Valor bruto:</strong> {{order.gross_total}}<br><strong>Desconto:</strong> {{order.discount_total}}<br><strong>Total líquido:</strong> {{order.net_total}}<br><strong>Taxa do gateway:</strong> {{order.gateway_fee}}<br><strong>Taxa da plataforma:</strong> {{order.platform_fee}}</p>
<p><strong>Gateway:</strong> {{order.gateway}}<br><strong>Forma de pagamento:</strong> {{order.payment_method}}<br><strong>ID da transação:</strong> {{order.transaction_id}}<br><strong>Cupom:</strong> {{order.coupon_code}}</p>
<h3>Fatura</h3>
<p><strong>Número:</strong> {{invoice.number}} | <strong>Status:</strong> {{invoice.status}}</p>
<p style="text-align:center;margin-top:24px;"><a href="{{links.admin_order_url}}" style="background:#1F5EDB;color:#fff;padding:12px 20px;text-decoration:none;font-weight:bold;">Abrir pedido no administrativo</a></p>',
            'is_active' => true,
        ];
    }

    private function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}
