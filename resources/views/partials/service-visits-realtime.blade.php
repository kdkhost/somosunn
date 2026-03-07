@php($unnServiceVisitsRealtimeConfig = [
    'driver' => config('broadcasting.default'),
    'key' => config('broadcasting.connections.pusher.key'),
    'cluster' => config('broadcasting.connections.pusher.options.cluster'),
])

@once
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        window.UNNServiceVisitsRealtime = window.UNNServiceVisitsRealtime || {
            echo: null,
            config: {{ \Illuminate\Support\Js::from($unnServiceVisitsRealtimeConfig) }},

            ensureEcho() {
                if (this.echo) {
                    return this.echo;
                }

                if (this.config.driver !== 'pusher' || !this.config.key || !window.Pusher || typeof window.Echo !== 'function') {
                    return null;
                }

                window.Pusher = window.Pusher || Pusher;
                this.echo = new window.Echo({
                    broadcaster: 'pusher',
                    key: this.config.key,
                    cluster: this.config.cluster || undefined,
                    forceTLS: true,
                    encrypted: true,
                    enabledTransports: ['ws', 'wss'],
                });

                return this.echo;
            },

            start(options) {
                if (!options || !options.statsUrl || typeof options.onPayload !== 'function') {
                    return null;
                }

                const refreshMs = Number(options.refreshMs || 10000);
                let inFlight = false;

                const refresh = (fresh = false) => {
                    if (inFlight) {
                        return;
                    }

                    inFlight = true;
                    const separator = options.statsUrl.includes('?') ? '&' : '?';
                    const url = fresh ? `${options.statsUrl}${separator}fresh=1` : options.statsUrl;

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then((response) => response.json())
                        .then((payload) => {
                            if (payload && payload.success) {
                                options.onPayload(payload);
                            }
                        })
                        .catch((error) => console.error('Realtime dashboard refresh failed:', error))
                        .finally(() => {
                            inFlight = false;
                        });
                };

                refresh();
                const timer = window.setInterval(() => refresh(false), refreshMs);

                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        refresh(true);
                    }
                });

                const echo = this.ensureEcho();
                if (echo) {
                    echo.channel('service-visits').listen('.service.visit.registered', () => refresh(true));
                }

                return { refresh, timer };
            },
        };
    </script>
@endonce
