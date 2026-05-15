const CACHE_NAME = 'wfh-tracker-v2';
const ASSETS = [
  '/',
  '/manifest.json',
  '/icon-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
});

self.addEventListener('fetch', event => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) return;

  // Skip POST requests and others
  if (event.request.method !== 'GET') return;

  event.respondWith(
    caches.match(event.request).then(response => {
      if (response) return response;

      return fetch(event.request).then(networkResponse => {
        // Don't cache redirects or errors
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
          return networkResponse;
        }

        // Cache static assets only (optional, but safer)
        const url = new URL(event.request.url);
        if (url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|woff2|json)$/)) {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }

        return networkResponse;
      });
    }).catch(() => {
      // Fallback for offline if needed
    })
  );
});

