const CACHE_NAME = 'unn-v1';
const URLS_TO_CACHE = [
  '/offline',
  '/img/logo.svg'
];

self.addEventListener('install', function(event) {
  console.log('[SW] Instalado');
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        return cache.addAll(URLS_TO_CACHE);
      })
      .catch((err) => console.log('Cache error', err))
  );
});

self.addEventListener('activate', function(event) {
  console.log('[SW] Ativado');
  event.waitUntil(clients.claim());
});

self.addEventListener('fetch', function(event) {
  // Ignora requisições não-GET ou APIs/Admin
  if (event.request.method !== 'GET' || event.request.url.includes('/api/') || event.request.url.includes('/admin')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request).then(
          function(response) {
            // Check if we received a valid response
            if(!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Apenas cacheia assets estáticos (imagens, css, js)
            if (event.request.url.match(/\.(js|css|png|jpg|svg|woff2)$/)) {
                var responseToCache = response.clone();
                caches.open(CACHE_NAME)
                .then(function(cache) {
                    cache.put(event.request, responseToCache);
                });
            }

            return response;
          }
        );
      })
  );
});
