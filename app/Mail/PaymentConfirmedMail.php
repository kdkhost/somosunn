<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $this->order->loadMissing(['items', 'user']);
        $layout = app(SystemMailLayoutData::class)->make();
        $siteUrl = url('/');
        $planTitle = (string) (optional($this->order->items->first())->title ?: 'Plano');

        $data = [
            'year' => date('Y'),
            'user' => [
                'name' => (string) ($this->order->user?->name ?? 'Cliente'),
                'email' => (string) ($this->order->user?->email ?? ''),
            ],
            'site' => [
                'name' => (string) ($layout['siteName'] ?? config('app.name', 'UNN')),
                'url' => $siteUrl,
                'logo' => (string) ($layout['logoUrl'] ?? asset('img/logo.svg')),
                'primary_color' => (string) ($layout['primaryColor'] ?? '#1F5EDB'),
                'secondary_color' => (string) ($layout['secondaryColor'] ?? '#177FD6'),
            ],
            'order' => [
                'id' => (string) $this->order->id,
                'date' => ($this->order->created_at ? $this->order->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')),
                'total' => 'R$ ' . number_format((float) ($this->order->total_amount ?? 0), 2, ',', '.'),
                'plan_title' => $planTitle,
            ],
            'links' => [
                'portal_url' => route('portal'),
            ],
        ];

        $rendered = app(SystemMailTemplateService::class)->renderBySlug('payment_paid', $data);
        if ($rendered) {
            return $this
                ->subject($rendered['subject'])
                ->view('emails.system', array_merge($layout, [
                    'content' => $rendered['content'],
                ]));
        }

        return $this
            ->subject('Pagamento Confirmado - Pedido #' . $this->order->id)
            ->view('emails.payment_confirmed', array_merge($layout, [
                'order' => $this->order,
            ]));
    }
}
