<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailTemplate;
use App\Models\Order;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmedMail extends Mailable implements ShouldQueue
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
        return $this->buildFromTemplate('payment_confirmed', [
            'user' => ['name' => $this->order->user->name ?? 'Cliente'],
            'order' => [
                'id' => $this->order->id,
                'total' => 'R$ ' . number_format((float) $this->order->total_amount, 2, ',', '.'),
            ],
        ], [
            'name' => 'Pagamento Confirmado',
            'category' => 'financeiro',
            'subject' => 'Pagamento confirmado - Pedido #{{order.id}}',
            'body' => '<h2>Pagamento confirmado!</h2><p>Ola, {{user.name}}.</p><p>O pagamento do pedido <strong>#{{order.id}}</strong>, no valor de <strong>{{order.total}}</strong>, foi confirmado.</p>',
        ]);
    }
}
