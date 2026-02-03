<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentWebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        // Validate webhook signature according to MercadoPago docs (placeholder)
        \Log::info('MercadoPago webhook', $request->all());

        // Find payment by external id if exists and update
        // For now compute fee and log it (actual wallet reconciliation must be implemented)
        $amount = $request->input('transaction_amount') ?? $request->input('amount') ?? null;
        if($amount){
            $ps = new \App\Services\PaymentService();
            $res = $ps->computeFee((float)$amount, 'mercadopago');
            \Log::info('Computed fee', $res);
        }

        return response('OK', 200);
    }

    public function pagSeguro(Request $request)
    {
        \Log::info('PagSeguro webhook', $request->all());
        // TODO: validate and process
        return response('OK', 200);
    }
}