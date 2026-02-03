<?php

namespace App\Services;

class MercadoPagoService
{
    public static function init()
    {
        if (!class_exists('MercadoPago\SDK')) return null;
        \MercadoPago\SDK::setAccessToken(config('payments.mercadopago.access_token'));
        return new \MercadoPago\Preference();
    }

    public static function createPreference(array $data)
    {
        if (!class_exists('MercadoPago\SDK')) return null;
        \MercadoPago\SDK::setAccessToken(config('payments.mercadopago.access_token'));

        $preference = new \MercadoPago\Preference();
        $item = new \MercadoPago\Item();
        $item->title = $data['title'] ?? 'Compra UNN';
        $item->quantity = 1;
        $item->unit_price = floatval($data['amount'] ?? 0);

        $preference->items = array($item);
        $preference->back_urls = [
            'success' => $data['success_url'] ?? url('/'),
            'failure' => $data['failure_url'] ?? url('/'),
            'pending' => $data['pending_url'] ?? url('/')
        ];
        $preference->auto_return = 'approved';
        $preference->save();

        return $preference;
    }
}