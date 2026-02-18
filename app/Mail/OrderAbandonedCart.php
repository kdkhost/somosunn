<?php

namespace App\Mail;

use App\Models\MailTemplate;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;

class OrderAbandonedCart extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        // 1. Fetch Template
        $template = MailTemplate::where('slug', 'abandoned-cart')->first();

        if (!$template) {
            // Fallback if template is deleted or missing
            return $this->subject('Você esqueceu algo no carrinho!')
                ->markdown('emails.raw', ['content' => 'Olá ' . $this->order->user->name . ', você tem um pedido pendente (#' . $this->order->id . '). Acesse sua conta para finalizar a compra.']);
        }

        // 2. Prepare Data
        $data = $this->prepareData();

        // 3. Render Subject and Body
        $subject = $this->renderString($template->subject, $data);
        $body = $this->renderString($template->body, $data);

        // 4. Apply System Layout (header/footer)
        $layoutData = app(SystemMailLayoutData::class)->make();

        return $this->subject($subject)
            ->view('emails.system', array_merge($layoutData, ['content' => $body]));
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

    protected function renderString($string, $data)
    {
        try {
            return Blade::render($string, $data);
        } catch (\Exception $e) {
            // Fallback regex replacement if Blade fails
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $string = str_replace('{{' . $key . '.' . $subKey . '}}', $subValue, $string);
                    }
                } else {
                    $string = str_replace('{{' . $key . '}}', $value, $string);
                }
            }
            return $string;
        }
    }
}
