<?php

namespace App\Services;

use App\Models\Setting;

class PaymentService
{
    public function computeFee(float $amount, string $gateway, bool $passToBuyer = false, ?float $percentage = null, ?float $fixed = null)
    {
        // read defaults from settings if not provided
        $pKey = "payments.{$gateway}.fee_percentage";
        $fKey = "payments.{$gateway}.fee_fixed";
        $ppKey = "payments.{$gateway}.pass_fee";

        $percentage = $percentage ?? (float)\App\Models\Setting::get($pKey, 0);
        $fixed = $fixed ?? (float)\App\Models\Setting::get($fKey, 0);
        $passToBuyer = $passToBuyer || (bool)\App\Models\Setting::get($ppKey, false);

        $feeFromPerc = round(($percentage / 100.0) * $amount, 2);
        $feeAmount = $feeFromPerc + $fixed;

        $finalAmount = $passToBuyer ? ($amount + $feeAmount) : ($amount - $feeAmount);

        $result = ['fee_amount' => $feeAmount, 'final_amount' => $finalAmount, 'fee_passed' => $passToBuyer, 'percentage' => $percentage, 'fixed' => $fixed];
        return $result;
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