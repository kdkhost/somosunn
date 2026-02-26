<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\EmailQueueSettings;
use App\Services\Mail\SystemMailLayoutData;
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
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function build()
    {
        $layout = app(SystemMailLayoutData::class)->make();

        return $this
            ->subject('Pagamento Confirmado - Pedido #' . $this->order->id)
            ->view('emails.payment_confirmed', array_merge($layout, [
                'order' => $this->order,
            ]));
    }
}
