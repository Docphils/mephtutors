// Bump this whenever cache behavior changes. Activation clears all prior caches,
// including cached HTML that may contain an expired Laravel CSRF token.
const SW_VERSION = 'mephed-pwa-v3';
const STATIC_CACHE = `${SW_VERSION}-static`;
const RUNTIME_CACHE = `${SW_VERSION}-runtime`;
const IMAGE_CACHE = `${SW_VERSION}-images`;

const CORE_ASSETS = [
    '/offline',
    '/manifest.webmanifest',
    '/pwa/icons/apple-touch-icon.png',
    '/pwa/icons/icon-192.png',
    '/pwa/icons/icon-512.png',
    '/images/MephEd.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(CORE_ASSETS)).catch(() => null)
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const cacheNames = await caches.keys();
        await Promise.all(
            cacheNames
                .filter((cacheName) => ![STATIC_CACHE, RUNTIME_CACHE, IMAGE_CACHE].includes(cacheName))
                .map((cacheName) => caches.delete(cacheName))
        );

        if ('navigationPreload' in self.registration) {
            await self.registration.navigationPreload.enable();
        }

        await self.clients.claim();
    })());
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigationRequest(event));
        return;
    }

    if (request.destination === 'image') {
        event.respondWith(cacheFirst(request, IMAGE_CACHE, 80));
        return;
    }

    if (['style', 'script', 'font', 'worker'].includes(request.destination)) {
        event.respondWith(staleWhileRevalidate(request, RUNTIME_CACHE, 100));
        return;
    }

    // Laravel pages, Livewire requests, and other data must always reach the
    // server. Their responses can contain session-specific or private data.
    event.respondWith(fetch(request));
});

async function handleNavigationRequest(event) {
    try {
        const preloadResponse = await event.preloadResponse;
        if (preloadResponse) {
            return preloadResponse;
        }

        return await fetch(event.request);
    } catch (_error) {
        // Do not serve a cached Laravel page here: it can contain an expired
        // CSRF token and may expose user-specific data.
        return caches.match('/offline');
    }
}

async function staleWhileRevalidate(request, cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);

    const fetchPromise = fetch(request)
        .then((networkResponse) => {
            if (networkResponse.ok) {
                cache.put(request, networkResponse.clone());
                trimCache(cacheName, maxEntries);
            }
            return networkResponse;
        })
        .catch(() => cachedResponse);

    return cachedResponse || fetchPromise;
}

async function cacheFirst(request, cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
            trimCache(cacheName, maxEntries);
        }
        return networkResponse;
    } catch (_error) {
        return caches.match('/images/MephEd.png');
    }
}

async function trimCache(cacheName, maxEntries = 60) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length <= maxEntries) {
        return;
    }

    const toDelete = keys.slice(0, keys.length - maxEntries);
    await Promise.all(toDelete.map((key) => cache.delete(key)));
}
