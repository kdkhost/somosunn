<?php

namespace App\Http\Middleware;

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

            if (!config('internal_cron.enabled', true)) {
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

            if ($request->is('admin*') || $request->is('api*')) {
                return;
            }

            if ($request->is('storage*') || $request->is('img*') || $request->is('uploads*')) {
                return;
            }

            $path = '/' . ltrim($request->path(), '/');
            if (in_array($path, ['/favicon.ico', '/service-worker.js', '/manifest.webmanifest'], true)) {
                return;
            }

            $minInterval = (int) config('internal_cron.min_interval_seconds', 60);
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
}
