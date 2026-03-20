// Cache version — bump this to invalidate all caches on deploy
const CACHE_VERSION = 'v1.3';
const STATIC_CACHE   = `attendance-static-${CACHE_VERSION}`;
const IMAGE_CACHE    = `attendance-images-${CACHE_VERSION}`;
const PAGE_CACHE_NAME = `attendance-v1.2`; // keep old name for backwards compat cleanup

const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/pwa-icon.png',
    '/favicon.ico',
];

// ─── Install ────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// ─── Activate ───────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    const validCaches = [STATIC_CACHE, IMAGE_CACHE];
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => !validCaches.includes(key))
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// ─── Helpers ─────────────────────────────────────────────────────────────────
const isViteAsset  = (url) => url.pathname.startsWith('/build/assets/');
const isImage      = (url) => /\.(png|jpg|jpeg|webp|gif|svg|ico)$/i.test(url.pathname);
const isNavigation = (req) => req.mode === 'navigate';
const isApiRequest = (url) => url.pathname.startsWith('/api/');

// Cache-First strategy — returns cache immediately, updates cache in background
async function cacheFirst(request, cacheName, maxAge = null) {
    const cache    = await caches.open(cacheName);
    const cached   = await cache.match(request);

    if (cached) {
        // If maxAge, check if still fresh
        if (maxAge) {
            const date = cached.headers.get('sw-cached-at');
            if (date && (Date.now() - Number(date)) < maxAge) return cached;
        } else {
            return cached; // Vite assets: hashed URL = always fresh
        }
    }

    const response = await fetch(request);
    if (response.ok) {
        const toCache = response.clone();
        const headers = new Headers(toCache.headers);
        headers.set('sw-cached-at', String(Date.now()));
        const modified = new Response(await toCache.blob(), { headers, status: toCache.status, statusText: toCache.statusText });
        cache.put(request, modified);
    }
    return response;
}

// Network-First strategy — tries network, falls back to cache
async function networkFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    try {
        const response = await fetch(request);
        if (response.ok) cache.put(request, response.clone());
        return response;
    } catch {
        const cached = await cache.match(request);
        if (cached) return cached;
        // Offline fallback for navigation
        if (isNavigation(request)) return cache.match('/');
    }
}

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Skip cross-origin requests and API calls
    if (url.origin !== self.location.origin) return;
    if (isApiRequest(url)) return;

    // 1. Vite JS/CSS assets — Cache-First (hashed filenames = safe forever)
    if (isViteAsset(url)) {
        event.respondWith(cacheFirst(event.request, STATIC_CACHE));
        return;
    }

    // 2. Images (profile photos, icons) — Cache-First, 30 days TTL
    if (isImage(url)) {
        const THIRTY_DAYS = 30 * 24 * 60 * 60 * 1000;
        event.respondWith(cacheFirst(event.request, IMAGE_CACHE, THIRTY_DAYS));
        return;
    }

    // 3. Static assets (manifest, favicon) — Network-First
    if (STATIC_ASSETS.includes(url.pathname)) {
        event.respondWith(networkFirst(event.request, STATIC_CACHE));
        return;
    }

    // 4. Inertia page navigations — Network-First (always fresh data)
    if (isNavigation(event.request)) {
        event.respondWith(networkFirst(event.request, STATIC_CACHE));
        return;
    }
});
