<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PaymentService;

class PaymentServiceTest extends TestCase
{
    public function test_compute_fee_default()
    {
        $ps = new PaymentService();
        $res = $ps->computeFee(100.00, 'mercadopago', false, 5.0, 0);
        $this->assertEquals(5.00, $res['fee_amount']);
        $this->assertEquals(95.00, $res['final_amount']);
    }

    public function test_compute_fee_passed_to_buyer()
    {
        $ps = new PaymentService();
        $res = $ps->computeFee(200.00, 'mercadopago', true, 5.0, 2.00);
        $this->assertEquals(12.00, $res['fee_amount']);
        $this->assertEquals(212.00, $res['final_amount']);
    }
}