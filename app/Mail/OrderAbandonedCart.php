<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailTemplate;
use App\Models\Order;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderAbandonedCart extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, UsesMailTemplate;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function build()
    {
        return $this->buildFromTemplate('abandoned-cart', $this->prepareData(), [
            'name' => 'Carrinho Abandonado',
            'category' => 'marketplace',
            'subject' => 'Voce esqueceu algo no carrinho - {{site.name}}',
            'body' => '<h2>Ola, {{user.name}}!</h2><p>Voce possui o pedido pendente <strong>#{{order.id}}</strong>, no valor de <strong>{{order.total}}</strong>.</p><p><a href="{{abandoned_cart.link}}">Finalizar compra</a></p>',
        ]);
    }

    protected function prepareData()
    {
        $itemsList = $this->order->items->pluck('title')->implode(', ');
        $link = route('panel.purchases.index'); // Redirect to purchases/orders page

        return [
            'user' => [
                'name' => $this->order->user->name,
                'email' => $this->order->user->email,
            ],
            'order' => [
                'id' => $this->order->id,
                'total' => number_format($this->order->total_amount, 2, ',', '.'),
                'date' => $this->order->created_at->format('d/m/Y'),
            ],
            'abandoned_cart' => [
                'items' => $itemsList,
                'link' => $link
            ]
        ];
    }
}
