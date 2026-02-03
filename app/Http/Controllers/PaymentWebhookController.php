<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentWebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        // Minimal validation of "topic" or type
        $type = $request->input('type') ?? $request->input('topic');
        
        if ($type === 'payment') {
            try {
                // Fetch payment info from API to verify status
                $paymentId = $request->input('data.id') ?? $request->input('id');
                
                // We're blindly trusting the webhook ID presence, but ideally we query the API using the seller's token.
                // Since we have multiple sellers, it's tricky without knowing which seller.
                // However, internal logic: if we have the order ID in external_reference, use it.
                // BUT MP webhook only sends ID. We need to query API.
                // Simplification for now: Assume valid if signature check passes (TODO) or trust data.
                
                // Better approach: We can't query API without Token. 
                // We can try to find Order by transaction_id if we saved it during checkout creation? 
                // No, checkout creation yields Preference ID (MP). 
                // MP sends 'external_reference' in the payment object.
                // So we MUST query the payment.
                // Limitation: We don't know which seller token to use unless we iterate or use a global token (not the case here).
                
                // ALTERNATIVE: Use the `seller_id` passed in the URL (we added this in service).
                $sellerId = $request->query('seller_id');
                $sellerAccount = \App\Models\GatewayAccount::where('user_id', $sellerId)->where('gateway', 'mercadopago')->first();
                
                if ($sellerAccount) {
                     $token = $sellerAccount->access_token;
                     $response = \Illuminate\Support\Facades\Http::withToken($token)->get('https://api.mercadopago.com/v1/payments/' . $paymentId);
                     
                     if ($response->successful()) {
                         $data = $response->json();
                         $orderId = $data['external_reference'] ?? null;
                         $status = $data['status'] ?? '';
                         
                         if ($orderId && $status === 'approved') {
                             $order = \App\Models\Order::find($orderId);
                             if ($order && $order->status !== 'paid') {
                                 $order->update([
                                     'status' => 'paid', 
                                     'transaction_id' => $paymentId,
                                     'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data])
                                 ]);
                                 \Log::info("Order #{$orderId} marked as PAID via MP Webhook");
                             }
                         }
                     }
                }
            } catch (\Exception $e) {
                \Log::error('MP Webhook Error: ' . $e->getMessage());
            }
        }

        return response('OK', 200);
    }

    public function pagSeguro(Request $request)
    {
        // PagSeguro V4 sends 'reference_id' in the body directly often.
        \Log::info('PagSeguro webhook', $request->all());
        
        $referenceId = $request->input('reference_id');
        $charges = $request->input('charges');
        
        if ($referenceId && $charges) {
            $order = \App\Models\Order::find($referenceId);
            if ($order) {
                // Check if any charge is PAID
                foreach ($charges as $charge) {
                    if (($charge['status'] ?? '') === 'PAID') {
                         if ($order->status !== 'paid') {
                             $order->update([
                                 'status' => 'paid',
                                 'transaction_id' => $charge['id'] ?? null, // Save Charge ID for refund
                                 'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $request->all()])
                             ]);
                             \Log::info("Order #{$referenceId} marked as PAID via PS Webhook");
                         }
                         break;
                    }
                }
            }
        }
        
        return response('OK', 200);
    }
}