<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\BrazilPhone;
use Tests\TestCase;

class BrazilPhoneTest extends TestCase
{
    public function test_it_formats_eleven_digit_mobile_numbers(): void
    {
        $this->assertSame('(21) 98132-5441', BrazilPhone::format('21981325441'));
        $this->assertSame('(21) 98132-5441', BrazilPhone::format('+55 (21) 98132-5441'));
    }

    public function test_it_preserves_ten_digit_landlines(): void
    {
        $this->assertSame('(21) 3456-7890', BrazilPhone::format('2134567890'));
    }

    public function test_it_restores_missing_ninth_digit_for_legacy_mobile_numbers(): void
    {
        $this->assertSame('(21) 99813-2544', BrazilPhone::format('(21) 9813-2544'));
    }

    public function test_it_does_not_use_country_code_as_area_code(): void
    {
        $this->assertSame('(21) 968-0546', BrazilPhone::format('(55) 21968-0546'));
        $this->assertSame('(21) 968-0546', BrazilPhone::format('55219680546'));
        $this->assertSame('(55) 99999-9999', BrazilPhone::format('55999999999'));
    }

    public function test_user_model_formats_phone_on_read_and_write(): void
    {
        $user = new User();
        $user->phone = '21981325441';

        $this->assertSame('(21) 98132-5441', $user->getAttributes()['phone']);
        $this->assertSame('(21) 98132-5441', $user->phone);
    }
}
