<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Bus\Dispatcher;

class EmailQueueSettings
{
    public static function mode(): string
    {
        $mode = strtolower(trim((string) Setting::get('email_dispatch_mode', 'sync')));
        return $mode === 'queue' ? 'queue' : 'sync';
    }

    public static function shouldQueue(): bool
    {
        return self::mode() === 'queue';
    }

    public static function connection(): string
    {
        if (!self::shouldQueue()) {
            return 'sync';
        }

        $configured = strtolower(trim((string) Setting::get('email_queue_connection', 'database')));
        $available = array_keys((array) config('queue.connections', []));

        if (!in_array($configured, $available, true) || $configured === '') {
            return 'database';
        }

        return $configured;
    }

    public static function queueName(): string
    {
        $queue = trim((string) Setting::get('email_queue_name', 'emails'));
        $queue = preg_replace('/[^a-zA-Z0-9_\-]/', '', $queue) ?: '';

        return $queue !== '' ? $queue : 'emails';
    }

    public static function delaySeconds(): int
    {
        $delay = (int) Setting::get('email_queue_delay_seconds', 0);
        return max(0, min(3600, $delay));
    }

    public static function scheduleEnabled(): bool
    {
        return (int) Setting::get('email_queue_schedule_enabled', 1) === 1;
    }

    public static function tries(): int
    {
        $tries = (int) Setting::get('email_queue_tries', 3);
        return max(1, min(10, $tries));
    }

    public static function timeout(): int
    {
        $timeout = (int) Setting::get('email_queue_timeout', 120);
        return max(30, min(900, $timeout));
    }

    public static function sleep(): int
    {
        $sleep = (int) Setting::get('email_queue_sleep', 1);
        return max(1, min(10, $sleep));
    }

    public static function applyToQueueable(object $queueable): object
    {
        if (method_exists($queueable, 'onConnection')) {
            $queueable->onConnection(self::connection());
        }

        if (method_exists($queueable, 'onQueue')) {
            $queueable->onQueue(self::queueName());
        }

        if (self::shouldQueue() && self::delaySeconds() > 0 && method_exists($queueable, 'delay')) {
            $queueable->delay(now()->addSeconds(self::delaySeconds()));
        }

        return $queueable;
    }

    public static function dispatch(object $job, bool $forceSync = false): void
    {
        if ($forceSync || !self::shouldQueue()) {
            app(Dispatcher::class)->dispatchSync($job);
            return;
        }

        dispatch(self::applyToQueueable($job));
    }
}

