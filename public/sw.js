const CACHE_NAME = 'wfh-tracker-v3';
const ASSETS = [
  '/manifest.json',
  '/icon-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', event => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) return;

  // Skip POST requests and others
  if (event.request.method !== 'GET') return;

  // For HTML requests (navigate), use Network First strategy
  if (event.request.mode === 'navigate' || event.request.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(event.request)
        .then(networkResponse => {
          return networkResponse;
        })
        .catch(error => {
          return caches.match(event.request).then(response => {
            if (response) return response;
            // Optionally return a fallback offline page here
            // throw error; 
          });
        })
    );
    return;
  }

  // For other requests, use Cache First strategy
  event.respondWith(
    caches.match(event.request).then(response => {
      if (response) return response;

      return fetch(event.request).then(networkResponse => {
        // Don't cache redirects or errors
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
          return networkResponse;
        }

        // Cache static assets only
        const url = new URL(event.request.url);
        if (url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|woff2|json)$/)) {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }

        return networkResponse;
      }).catch(error => {
        // Fallback for offline if needed
        console.error('Fetch failed:', error);
      });
    })
  );
});

