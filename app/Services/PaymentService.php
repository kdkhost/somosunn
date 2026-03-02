<?php

namespace App\Services;

use App\Models\Setting;

class PaymentService
{
    /**
     * Compute the fee for a payment.
     *
     * IMPORTANT: $passToBuyer uses nullable tri-state:
     *   null  → read from DB/config (default)
     *   true  → always add fee on top of amount
     *   false → always deduct fee from amount (explicit override, DB setting ignored)
     */
    public function computeFee(float $amount, string $gateway, ?bool $passToBuyer = null, ?float $percentage = null, ?float $fixed = null)
    {
        // read defaults from settings if not provided
        $pKey  = "payments.{$gateway}.fee_percentage";
        $fKey  = "payments.{$gateway}.fee_fixed";
        $ppKey = "payments.{$gateway}.pass_fee";

        $percentage  = $percentage  ?? (float) \App\Models\Setting::get($pKey, 0);
        $fixed       = $fixed       ?? (float) \App\Models\Setting::get($fKey, 0);

        // Only read pass_fee from DB when not explicitly provided by the caller
        if ($passToBuyer === null) {
            $passToBuyer = (bool) \App\Models\Setting::get($ppKey, false);
        }

        $feeFromPerc = round(($percentage / 100.0) * $amount, 2);
        $feeAmount   = $feeFromPerc + $fixed;

        $finalAmount = $passToBuyer ? ($amount + $feeAmount) : ($amount - $feeAmount);

        return [
            'fee_amount'   => $feeAmount,
            'final_amount' => $finalAmount,
            'fee_passed'   => $passToBuyer,
            'percentage'   => $percentage,
            'fixed'        => $fixed,
        ];
    }

    public function applyToPayment(\App\Models\Payment $payment, ?bool $passToBuyer = null)
    {
        $res = $this->computeFee((float)$payment->amount, $payment->gateway, $passToBuyer);
        $payment->fee_amount = $res['fee_amount'];
        $payment->fee_percentage = $res['percentage'];
        $payment->fee_passed = $res['fee_passed'];
        // if fee_passed true, change amount payable to final_amount
        $payment->amount = $res['final_amount'];
        $payment->save();
        return $payment;
    }
}