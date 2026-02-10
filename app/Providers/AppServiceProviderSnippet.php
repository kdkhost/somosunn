\Illuminate\Support\Facades\RateLimiter::for('invoices_email', function ($job) {
return \Illuminate\Cache\RateLimiting\Limit::perHour(100);
});