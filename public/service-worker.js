const CACHE_VERSION = 'unn-v3';
const STATIC_CACHE = CACHE_VERSION + '-static';
const OFFLINE_URL = '/offline';
const STATIC_ASSETS = [
    OFFLINE_URL,
    '/img/logo.svg',
];

self.addEventListener('install', function (event) {
    console.log('[SW] Installing', CACHE_VERSION);
    self.skipWaiting();
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(function (cache) {
                return cache.addAll(STATIC_ASSETS);
            })
            .catch(function (err) { console.log('[SW] pre-cache error', err); })
    );
});

self.addEventListener('activate', function (event) {
    console.log('[SW] Activating', CACHE_VERSION);
    // Deletar caches antigos
    event.waitUntil(
        caches.keys().then(function (names) {
            return Promise.all(
                names
                    .filter(function (name) { return name.indexOf(CACHE_VERSION) !== 0; })
                    .map(function (name) { return caches.delete(name); })
            );
        }).then(function () { return self.clients.claim(); })
    );
});

self.addEventListener('fetch', function (event) {
    var req = event.request;
    if (req.method !== 'GET') return;

    var url = new URL(req.url);

    // Nunca cachear API/admin/painel/checkout - sempre rede
    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/painel') ||
        url.pathname.startsWith('/checkout') ||
        url.pathname.startsWith('/loja') ||
        url.pathname.startsWith('/marketplace')
    ) {
        return; // deixa o browser fazer a requisição direto
    }

    // Assets estáticos: cache-first
    if (req.url.match(/\.(js|css|png|jpg|jpeg|svg|webp|woff2|ico|gif)$/)) {
        event.respondWith(
            caches.match(req).then(function (cached) {
                if (cached) return cached;
                return fetch(req).then(function (response) {
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    var copy = response.clone();
                    caches.open(STATIC_CACHE).then(function (cache) { cache.put(req, copy); });
                    return response;
                });
            }).catch(function () { return fetch(req); })
        );
        return;
    }

    // HTML/navegação: network-first, fallback para offline
    if (req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html')) {
        event.respondWith(
            fetch(req).catch(function () {
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // Default: apenas fetch normal
});
