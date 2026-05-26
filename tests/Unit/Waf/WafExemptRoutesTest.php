<?php

namespace Tests\Unit\Waf;

use PHPUnit\Framework\TestCase;

class WafExemptRoutesTest extends TestCase
{
    public function test_sumup_payment_routes_are_exempt_from_waf_inspection(): void
    {
        $patterns = require __DIR__ . '/../../../config/waf.php';
        $patterns = $patterns['exempt_routes'] ?? [];

        $paths = [
            '/checkout/sumup/pix',
            '/checkout/sumup/status',
            '/checkout/sumup/recreate',
            '/webhook/sumup/123/token-seguro',
            '/api/v1/webhooks/sumup',
        ];

        foreach ($paths as $path) {
            $matched = false;

            foreach ($patterns as $pattern) {
                if (@preg_match($pattern, $path) === 1) {
                    $matched = true;
                    break;
                }
            }

            $this->assertTrue($matched, "A rota {$path} deve estar isenta no WAF.");
        }
    }
}
