<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // Configuração individual de cada cron
            'cron_subscriptions_enabled' => '1',
            'cron_invoices_enabled' => '1',
            'cron_events_reminders_enabled' => '1',
            'cron_mentorships_reminders_enabled' => '1',
            'cron_marketplace_abandoned_cart_enabled' => '1',
            'cron_notifications_cleanup_enabled' => '1',
            'cron_orders_cancel_enabled' => '1',
            'cron_cart_cleanup_enabled' => '1',
            'cron_points_ranking_enabled' => '1',
            'cron_points_birthday_enabled' => '1',

            // Configurações de lembrete
            'subscription_reminder_days' => '3',
            'event_reminder_hours' => '24',
            'mentorship_reminder_hours' => '24',
            'abandoned_cart_hours' => '6',
            'notifications_cleanup_days' => '90',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public function down(): void
    {
        $keys = array_keys([
            'cron_subscriptions_enabled', 'cron_invoices_enabled',
            'cron_events_reminders_enabled', 'cron_mentorships_reminders_enabled',
            'cron_marketplace_abandoned_cart_enabled', 'cron_notifications_cleanup_enabled',
            'cron_orders_cancel_enabled', 'cron_cart_cleanup_enabled',
            'cron_points_ranking_enabled', 'cron_points_birthday_enabled',
            'subscription_reminder_days', 'event_reminder_hours',
            'mentorship_reminder_hours', 'abandoned_cart_hours',
            'notifications_cleanup_days',
        ]);

        Setting::whereIn('key', $keys)->delete();
    }
};
