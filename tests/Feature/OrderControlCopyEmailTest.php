<?php

namespace Tests\Feature;

use App\Jobs\SendOrderControlCopyEmailJob;
use App\Mail\OrderControlCopyMail;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;
use App\Services\OrderControlCopyRecipientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderControlCopyEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_sale_is_sent_once_by_bcc_to_primary_admin_and_superadmin(): void
    {
        Mail::fake();

        $buyer = User::factory()->create([
            'name' => 'Cliente Controle',
            'email' => 'cliente-controle@example.com',
            'phone' => '(21) 99999-1111',
            'doc' => '12345678900',
            'role' => 'member',
        ]);
        $seller = User::factory()->create([
            'name' => 'Vendedor Controle',
            'email' => 'vendedor-controle@example.com',
            'role' => 'member',
        ]);
        $admin = User::factory()->create([
            'email' => 'admin-controle@example.com',
            'role' => 'admin',
        ]);
        User::factory()->create([
            'email' => 'admin-secundario@example.com',
            'role' => 'admin',
        ]);
        $superadmin = User::factory()->create([
            'email' => 'superadmin-controle@example.com',
            'role' => 'superadmin',
        ]);

        Setting::set('platform_admin_user_id', (string) $admin->id);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'paid',
            'paid_at' => now(),
            'total_amount' => 150,
            'fee_amount' => 5,
            'platform_fee_amount' => 15,
            'currency' => 'BRL',
            'gateway' => 'mercadopago',
            'payment_method' => 'pix',
            'transaction_id' => 'TX-CONTROLE-1',
            'metadata' => ['context' => 'course', 'sale_type' => 'course'],
        ]);
        $order->items()->create([
            'item_type' => 'course',
            'item_id' => 10,
            'title' => 'Curso de Controle',
            'price' => 75,
            'quantity' => 2,
        ]);

        $job = new SendOrderControlCopyEmailJob($order->id);
        $job->handle(
            app(SystemMailLayoutData::class),
            app(SystemMailTemplateService::class),
            app(OrderControlCopyRecipientService::class)
        );

        Mail::assertSent(OrderControlCopyMail::class, function (OrderControlCopyMail $mail) use ($admin, $superadmin): bool {
            $mail->build();

            return $mail->hasBcc($admin->email)
                && $mail->hasBcc($superadmin->email)
                && !$mail->hasBcc('admin-secundario@example.com')
                && empty($mail->to)
                && str_contains($mail->renderedHtml, 'Cliente Controle')
                && str_contains($mail->renderedHtml, 'Curso de Controle')
                && str_contains($mail->renderedHtml, 'TX-CONTROLE-1');
        });

        $metadata = $order->fresh()->metadata;
        $this->assertNotEmpty(data_get($metadata, 'emails.control_copy_sent_at'));
        $this->assertSame(2, data_get($metadata, 'emails.control_copy_recipient_count'));

        $job->handle(
            app(SystemMailLayoutData::class),
            app(SystemMailTemplateService::class),
            app(OrderControlCopyRecipientService::class)
        );

        Mail::assertSentCount(1);
    }

    public function test_control_copy_template_and_dispatch_are_present_in_all_paid_order_flows(): void
    {
        $this->assertFileExists(app_path('Jobs/SendOrderControlCopyEmailJob.php'));
        $this->assertFileExists(app_path('Mail/OrderControlCopyMail.php'));

        foreach ([
            app_path('Services/OrderSettlementService.php'),
            app_path('Http/Controllers/PaymentWebhookController.php'),
            app_path('Http/Controllers/Api/WebhookController.php'),
            app_path('Http/Controllers/SumUpController.php'),
        ] as $file) {
            $this->assertStringContainsString('OrderControlCopyDispatcher', file_get_contents($file));
        }
    }
}
