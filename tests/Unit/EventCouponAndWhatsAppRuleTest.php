<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

namespace Tests\Unit;

use App\Models\EventCoupon;
use App\Models\Event;
use App\Rules\WhatsAppGroupLinkRule;
use App\Services\EventCouponService;
use Tests\TestCase;

class EventCouponAndWhatsAppRuleTest extends TestCase
{
    public function test_whatsapp_group_rule_accepts_only_official_hosts(): void
    {
        $this->assertTrue(WhatsAppGroupLinkRule::passes('https://chat.whatsapp.com/abc123'));
        $this->assertTrue(WhatsAppGroupLinkRule::passes('https://www.chat.whatsapp.com/abc123'));
        $this->assertFalse(WhatsAppGroupLinkRule::passes('http://chat.whatsapp.com/abc123'));
        $this->assertFalse(WhatsAppGroupLinkRule::passes('https://wa.me/5521981325441'));
        $this->assertFalse(WhatsAppGroupLinkRule::passes('https://whatsapp.com/channel/abc123'));
        $this->assertFalse(WhatsAppGroupLinkRule::passes('https://example.com/grupo'));
        $this->assertFalse(WhatsAppGroupLinkRule::passes('javascript:alert(1)'));
    }

    public function test_event_only_reports_whatsapp_group_when_group_link_is_valid(): void
    {
        $event = new Event(['whatsapp_group_link' => 'https://chat.whatsapp.com/abc123']);
        $this->assertTrue($event->hasWhatsappGroup());

        $event->whatsapp_group_link = 'https://wa.me/5521981325441';
        $this->assertFalse($event->hasWhatsappGroup());
    }

    public function test_event_coupon_discount_and_limits_are_calculated_without_database(): void
    {
        $service = new EventCouponService();

        $free = new EventCoupon([
            'type' => EventCoupon::TYPE_FREE,
            'discount_value' => 100,
            'active' => true,
            'used_count' => 0,
        ]);
        $this->assertSame(37.0, $service->discountAmount($free, 37));

        $percent = new EventCoupon([
            'type' => EventCoupon::TYPE_PERCENT,
            'discount_value' => 50,
            'active' => true,
            'used_count' => 0,
        ]);
        $this->assertSame(18.5, $service->discountAmount($percent, 37));

        $limited = new EventCoupon([
            'type' => EventCoupon::TYPE_FIXED,
            'discount_value' => 10,
            'active' => true,
            'max_uses' => 2,
            'used_count' => 1,
            'starts_at' => now()->subMinute(),
            'expires_at' => now()->addMinute(),
        ]);

        $this->assertSame(10.0, $service->discountAmount($limited, 37));
        $this->assertTrue($service->canUse($limited));
        $this->assertFalse($service->canUse($limited, 2));
    }
}
