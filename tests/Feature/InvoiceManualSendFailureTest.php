<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class InvoiceManualSendFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_admin_returns_friendly_error_when_invoice_send_fails(): void
    {
        $this->assertSendFailureIsHandled('admin.invoices.send');
    }

    public function test_new_panel_returns_friendly_error_when_invoice_send_fails(): void
    {
        $this->assertSendFailureIsHandled('panel.admin.invoices.send');
    }

    private function assertSendFailureIsHandled(string $routeName): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'status' => 'issued',
            'currency' => 'BRL',
            'subtotal' => 10,
            'discount_amount' => 0,
            'total_amount' => 10,
            'issued_at' => now(),
        ]);

        $service = Mockery::mock(InvoiceService::class);
        $service->shouldReceive('queueInvoiceEmail')
            ->once()
            ->with(Mockery::on(fn (Invoice $argument) => $argument->is($invoice)), true)
            ->andThrow(new \RuntimeException('SMTP authentication failed'));
        $this->app->instance(InvoiceService::class, $service);

        $response = $this->withoutMiddleware()
            ->from(route('admin.invoices.show', $invoice))
            ->post(route($routeName, $invoice));

        $response->assertRedirect(route('admin.invoices.show', $invoice));
        $response->assertSessionHas('error', 'Nao foi possivel enviar a fatura. Verifique as configuracoes e credenciais SMTP e tente novamente.');
    }
}
