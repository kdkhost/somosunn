<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RunInternalCron
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        try {
            if (app()->environment('testing')) {
                return;
            }

            $enabled = $this->boolSetting('internal_cron_enabled', (bool) config('internal_cron.enabled', true));
            if (!$enabled) {
                return;
            }

            if (!$request instanceof Request) {
                return;
            }

            if (!$request->isMethod('GET')) {
                return;
            }

            if ($request->expectsJson() || $request->ajax()) {
                return;
            }

            if ($request->is('api*')) {
                return;
            }

            if ($request->is('storage*') || $request->is('img*') || $request->is('uploads*')) {
                return;
            }

            $path = '/' . ltrim($request->path(), '/');
            if (in_array($path, ['/favicon.ico', '/service-worker.js', '/manifest.webmanifest'], true)) {
                return;
            }

            $runQueueWorker = $this->boolSetting('internal_cron_run_queue_worker', (bool) config('internal_cron.run_queue_worker', true));
            config(['internal_cron.run_queue_worker' => $runQueueWorker]);

            $minInterval = (int) Setting::get('internal_cron_min_interval_seconds', (int) config('internal_cron.min_interval_seconds', 60));
            if ($minInterval < 10) {
                $minInterval = 10;
            }

            $lastRunAt = (int) Cache::get('internal_cron:last_run_at', 0);
            if ($lastRunAt > 0 && (time() - $lastRunAt) < $minInterval) {
                return;
            }

            $lock = Cache::lock('internal_cron:lock', $minInterval - 1);
            if (!$lock->get()) {
                return;
            }

            try {
                Cache::put('internal_cron:last_run_at', time(), $minInterval * 2);
                Artisan::call('schedule:run', ['--no-interaction' => true]);
            } finally {
                $lock->release();
            }
        } catch (\Throwable $e) {
            Log::debug('RunInternalCron falhou: ' . $e->getMessage());
        }
    }

    private function boolSetting(string $key, bool $default): bool
    {
        try {
            $raw = Setting::get($key, null);
            if ($raw === null || $raw === '') {
                return $default;
            }

            if (is_bool($raw)) {
                return $raw;
            }

            $rawString = trim((string) $raw);
            if ($rawString === '') {
                return $default;
            }

            $bool = filter_var($rawString, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($bool !== null) {
                return $bool;
            }

            return (bool) ((int) $rawString);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
