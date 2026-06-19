<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderPendingPaymentNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendUnpaidOrderReminders extends Command
{
    protected $signature = 'orders:send-unpaid-reminders {--limit=200}';

    protected $description = 'Envia lembretes personalizados para pedidos pendentes antes do cancelamento automatico';

    public function handle(OrderPendingPaymentNotificationService $notifications): int
    {
        if ((int) Setting::get('cron_orders_unpaid_reminders_enabled', 1) !== 1) {
            $this->info('Cron de lembretes de pedidos nao pagos desativado.');

            return self::SUCCESS;
        }

        $cancelAfterHours = $this->cancelAfterHours();
        $reminderHours = $this->reminderHours($cancelAfterHours);

        if (empty($reminderHours)) {
            $this->info('Nenhuma janela de lembrete configurada antes do cancelamento.');

            return self::SUCCESS;
        }

        $timezone = $this->timezone();
        $now = Carbon::now($timezone);
        $oldestReminderAt = $now->copy()->subHours(min($reminderHours));
        $cancelWindowAt = $now->copy()->subHours($cancelAfterHours);
        $limit = max(1, min(1000, (int) $this->option('limit')));

        $orders = Order::query()
            ->with(['user', 'items'])
            ->where('status', 'pending')
            ->whereNotNull('user_id')
            ->where('created_at', '<=', $oldestReminderAt)
            ->where('created_at', '>', $cancelWindowAt)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if (!$order->created_at) {
                continue;
            }

            $dueHour = $this->dueReminderHour($order, $reminderHours, $now);
            if ($dueHour === null) {
                continue;
            }

            if ($notifications->sendReminder($order, $dueHour, $cancelAfterHours)) {
                $this->markReminderSent($order, $dueHour);
                $sent++;
                continue;
            }

            $failed++;
        }

        $this->info("Lembretes enviados: {$sent}. Falhas: {$failed}.");

        Log::info('orders:send-unpaid-reminders completed', [
            'sent' => $sent,
            'failed' => $failed,
            'cancel_after_hours' => $cancelAfterHours,
            'reminder_hours' => $reminderHours,
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param array<int,int> $reminderHours
     */
    private function dueReminderHour(Order $order, array $reminderHours, Carbon $now): ?int
    {
        $createdAt = $order->created_at->copy()->timezone($this->timezone());
        $ageHours = (int) floor($createdAt->diffInMinutes($now) / 60);
        $sent = (array) data_get($order->metadata, 'notifications.unpaid_payment_reminders', []);

        rsort($reminderHours, SORT_NUMERIC);

        foreach ($reminderHours as $hour) {
            if ($ageHours >= $hour && !isset($sent[(string) $hour])) {
                return $hour;
            }
        }

        return null;
    }

    private function markReminderSent(Order $order, int $hour): void
    {
        $metadata = is_array($order->metadata) ? $order->metadata : [];
        $notifications = is_array($metadata['notifications'] ?? null) ? $metadata['notifications'] : [];
        $reminders = is_array($notifications['unpaid_payment_reminders'] ?? null)
            ? $notifications['unpaid_payment_reminders']
            : [];

        $reminders[(string) $hour] = [
            'sent_at' => now($this->timezone())->toIso8601String(),
        ];

        $notifications['unpaid_payment_reminders'] = $reminders;
        $metadata['notifications'] = $notifications;

        $order->update(['metadata' => $metadata]);
    }

    /**
     * @return array<int,int>
     */
    private function reminderHours(int $cancelAfterHours): array
    {
        $raw = (string) Setting::get('orders_unpaid_reminder_hours', '2,12,20');
        $hours = [];

        foreach (preg_split('/[,;\s]+/', $raw) ?: [] as $value) {
            $hour = (int) trim((string) $value);
            if ($hour > 0 && $hour < $cancelAfterHours) {
                $hours[] = $hour;
            }
        }

        $hours = array_values(array_unique($hours));
        sort($hours, SORT_NUMERIC);

        return $hours;
    }

    private function cancelAfterHours(): int
    {
        $hours = (int) Setting::get('orders_unpaid_cancel_after_hours', 24);

        return max(1, min(720, $hours));
    }

    private function timezone(): string
    {
        $timezone = trim((string) Setting::get('system_timezone', config('app.timezone', 'America/Sao_Paulo')));

        return $timezone !== '' ? $timezone : 'America/Sao_Paulo';
    }
}
