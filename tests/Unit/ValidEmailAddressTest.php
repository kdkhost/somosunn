<?php

namespace Tests\Unit;

use App\Rules\ValidEmailAddress;
use Tests\TestCase;

class ValidEmailAddressTest extends TestCase
{
    public function test_rejects_malformed_email(): void
    {
        $validator = validator(
            ['email' => 'cliente@@dominio'],
            ['email' => ['required', new ValidEmailAddress()]],
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_accepts_valid_email_in_testing_environment(): void
    {
        $validator = validator(
            ['email' => 'cliente@example.com'],
            ['email' => ['required', new ValidEmailAddress()]],
        );

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_email_with_nonexistent_domain(): void
    {
        $validator = validator(
            ['email' => 'cliente@dominio-que-nao-existe.invalid'],
            ['email' => ['required', new ValidEmailAddress()]],
        );

        $this->assertTrue($validator->fails());
    }
}
