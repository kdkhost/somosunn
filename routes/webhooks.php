<?php

use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\SumUpController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas de Webhooks de Pagamento (api.somosunn.com.br + retrocompatibilidade)
|--------------------------------------------------------------------------
| Processamento rapido com validacao de assinatura, rate limit e despacho
| assincrono em fila dedicada (`webhooks`), evitando travar o painel principal.
*/

Route::match(['get', 'post'], '/webhook/mercadopago', [PaymentWebhookController::class, 'mercadopago'])
    ->middleware('throttle:webhook_mercadopago')
    ->defaults('seller_id', 'platform')
    ->name('webhook.mercadopago');

Route::post('/webhook/sumup/{orderId}/{token}', [PaymentWebhookController::class, 'sumup'])
    ->middleware('throttle:webhook_sumup')
    ->name('webhook.sumup');

Route::post('/webhook/sumup', [SumUpController::class, 'webhook'])
    ->middleware('throttle:webhook_sumup')
    ->name('webhook.sumup.direct');

Route::match(['get', 'post'], '/webhook/efi', [PaymentWebhookController::class, 'efi'])
    ->middleware('throttle:webhook_efi')
    ->name('webhook.efi');
