<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventExhibitorRegistration;
use App\Models\Setting;
use App\Models\User;
use App\Services\EventExhibitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventExhibitorSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_exhibitor_sales_with_json_response(): void
    {
        $admin = $this->user(['role' => 'admin', 'level' => 'superadmin']);
        $event = $this->event($admin, [
            'exhibitor_sales_enabled' => false,
            'exhibitor_total_slots' => 5,
            'exhibitor_batch_1_price' => 150,
            'exhibitor_batch_1_deadline' => now()->addDays(10),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.events.exhibitors.toggle', $event));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
            ]);

        $this->assertTrue((bool) $event->fresh()->exhibitor_sales_enabled);
    }

    public function test_panel_member_with_content_permission_can_manage_own_event_exhibitors(): void
    {
        $member = $this->user([
            'extra_features' => ['courses.create'],
        ]);

        $event = $this->event($member, [
            'exhibitor_total_slots' => 4,
            'exhibitor_batch_1_price' => 250,
            'exhibitor_batch_1_deadline' => now()->addDays(10),
        ]);

        $this->actingAs($member)
            ->get(route('panel.admin.events.exhibitors.index', $event))
            ->assertOk()
            ->assertSee('Expositores', false);
    }

    public function test_panel_event_list_publishes_exhibitor_area_for_content_member(): void
    {
        $member = $this->user([
            'extra_features' => ['mentorships.create'],
        ]);

        $event = $this->event($member);

        $this->actingAs($member)
            ->get(route('panel.admin.events.list'))
            ->assertOk()
            ->assertSee(route('panel.admin.events.exhibitors.index', $event), false);
    }

    public function test_public_event_shows_ticket_and_exhibitor_options_when_available(): void
    {
        Setting::set('feature_events', '1');

        $seller = $this->user();
        $event = $this->event($seller, [
            'price' => 20,
            'capacity' => 30,
            'exhibitor_sales_enabled' => true,
            'exhibitor_show_publicly' => true,
            'exhibitor_total_slots' => 3,
            'exhibitor_batch_1_price' => 250,
            'exhibitor_batch_1_deadline' => now()->addDays(10),
        ]);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Comprar Ingresso')
            ->assertSee('Comprar área para expositor');
    }

    public function test_reservation_uses_batch_slots_and_prevents_overselling_without_consuming_tickets(): void
    {
        $seller = $this->user();
        $buyer = $this->user();
        $event = $this->event($seller, [
            'capacity' => 20,
            'exhibitor_sales_enabled' => true,
            'exhibitor_total_slots' => 2,
            'exhibitor_batch_1_price' => 300,
            'exhibitor_batch_1_deadline' => now()->addDays(10),
            'exhibitor_batch_1_slots' => 2,
            'exhibitor_includes_ticket' => true,
        ]);

        $service = app(EventExhibitorService::class);
        $reservation = $service->createReservation($event, $buyer, $this->payload(['quantity' => 2]), 'mercadopago');

        $this->assertSame(EventExhibitorRegistration::STATUS_RESERVED, $reservation['registration']->status);
        $this->assertSame(0, $service->remainingSlots($event->fresh()));
        $this->assertSame(20, $event->fresh()->remaining_seats);

        $this->expectException(\RuntimeException::class);
        $service->createReservation($event->fresh(), $buyer, $this->payload(['quantity' => 1]), 'mercadopago');
    }

    public function test_mercado_pago_webhook_resolves_exhibitor_reference_and_confirms_registration(): void
    {
        Queue::fake();
        Notification::fake();
        Config::set('payments.mercadopago.access_token', 'TEST-123');

        $seller = $this->user();
        $buyer = $this->user();
        $event = $this->event($seller, [
            'exhibitor_sales_enabled' => true,
            'exhibitor_total_slots' => 2,
            'exhibitor_batch_1_price' => 180,
            'exhibitor_batch_1_deadline' => now()->addDays(10),
        ]);

        $reservation = app(EventExhibitorService::class)
            ->createReservation($event, $buyer, $this->payload(), 'mercadopago');

        $reference = data_get($reservation['order']->metadata, 'gateway_reference');

        Http::fake([
            'api.mercadopago.com/v1/payments/987654*' => Http::response([
                'id' => 987654,
                'status' => 'approved',
                'external_reference' => $reference,
                'transaction_amount' => 180,
            ], 200),
        ]);

        $this->postJson(route('api.webhooks.mercadopago'), [
            'type' => 'payment',
            'data' => ['id' => '987654'],
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $reservation['order']->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('event_exhibitor_registrations', [
            'id' => $reservation['registration']->id,
            'status' => EventExhibitorRegistration::STATUS_PAID,
            'payment_status' => EventExhibitorRegistration::PAYMENT_PAID,
        ]);
    }

    public function test_failed_payment_releases_exhibitor_reservation(): void
    {
        $seller = $this->user();
        $buyer = $this->user();
        $event = $this->event($seller, [
            'exhibitor_sales_enabled' => true,
            'exhibitor_total_slots' => 1,
            'exhibitor_batch_1_price' => 180,
            'exhibitor_batch_1_deadline' => now()->addDays(10),
        ]);

        $reservation = app(EventExhibitorService::class)
            ->createReservation($event, $buyer, $this->payload(), 'sumup');

        app(EventExhibitorService::class)->releaseOrder($reservation['order'], 'failed');

        $this->assertDatabaseHas('event_exhibitor_registrations', [
            'id' => $reservation['registration']->id,
            'status' => EventExhibitorRegistration::STATUS_CANCELLED,
            'payment_status' => EventExhibitorRegistration::PAYMENT_CANCELLED,
        ]);

        $this->assertSame(1, app(EventExhibitorService::class)->remainingSlots($event->fresh()));
    }

    private function user(array $attributes = []): User
    {
        $id = str_replace('.', '', uniqid('', true));

        return User::create(array_merge([
            'name' => 'Usuário Teste',
            'email' => 'usuario-' . $id . '@teste.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'level' => 'iniciante',
        ], $attributes));
    }

    private function event(User $seller, array $attributes = []): Event
    {
        $id = str_replace('.', '', uniqid('', true));

        return Event::create(array_merge([
            'user_id' => $seller->id,
            'type' => 'event',
            'title' => 'Evento Teste ' . $id,
            'slug' => 'evento-teste-' . $id,
            'published' => true,
            'price' => 0,
            'capacity' => null,
            'start_at' => now()->addDays(20),
            'end_at' => now()->addDays(20)->addHours(4),
            'location' => 'Centro de Eventos',
            'address' => 'Rua Teste, 123',
        ], $attributes));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Responsável Expositor',
            'email' => 'expositor-' . str_replace('.', '', uniqid('', true)) . '@teste.com',
            'phone' => '(21) 99999-0000',
            'document' => '123.456.789-09',
            'company_name' => 'Empresa Expositora',
            'company_document' => '12.345.678/0001-90',
            'brand_name' => 'Marca Expositora',
            'description' => 'Exposição de produtos e serviços.',
            'quantity' => 1,
        ], $overrides);
    }
}
