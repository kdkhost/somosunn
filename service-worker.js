self.addEventListener('install', function(event) {
  console.log('[SW] Instalado');
  self.skipWaiting();
});

self.addEventListener('activate', function(event) {
  console.log('[SW] Ativado');
});

self.addEventListener('fetch', function(event) {
  // Placeholder: cache-first strategy can be implemented here
});
