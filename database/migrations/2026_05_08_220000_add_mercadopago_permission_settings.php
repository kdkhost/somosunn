<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // Permissões por tipo de usuário
            'mercadopago_allow_members' => '1',
            'mercadopago_allow_instructors' => '1',
            'mercadopago_allow_sellers' => '1',
            'mercadopago_allow_mentors' => '1',

            // Permissões por tipo de produto
            'mercadopago_allow_courses' => '1',
            'mercadopago_allow_mentorships' => '1',
            'mercadopago_allow_events' => '1',
            'mercadopago_allow_marketplace' => '1',
            'mercadopago_allow_subscriptions' => '1',
            'mercadopago_allow_services' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public function down(): void
    {
        $keys = [
            'mercadopago_allow_members', 'mercadopago_allow_instructors',
            'mercadopago_allow_sellers', 'mercadopago_allow_mentors',
            'mercadopago_allow_courses', 'mercadopago_allow_mentorships',
            'mercadopago_allow_events', 'mercadopago_allow_marketplace',
            'mercadopago_allow_subscriptions', 'mercadopago_allow_services',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
