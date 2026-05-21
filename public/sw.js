/* PWA service worker with static Cache-First and dynamic Network-First strategies. */
const STATIC_CACHE = 'mycrm-static-v1';
const DYNAMIC_CACHE = 'mycrm-dynamic-v1';

const PRECACHE_URLS = ['/', '/dashboard', '/login', '/manifest.json'];
const EXCLUDED_PREFIXES = ['/api/', '/logout', '/sanctum/'];
const STATIC_EXTENSIONS = [
  '.css', '.js', '.mjs', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp', '.ico',
  '.woff', '.woff2', '.ttf', '.eot', '.map',
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE_URLS)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys
        .filter((key) => ![STATIC_CACHE, DYNAMIC_CACHE].includes(key))
        .map((key) => caches.delete(key)),
    )),
  );
  self.clients.claim();
});

function isExcludedPath(pathname) {
  return EXCLUDED_PREFIXES.some((prefix) => pathname.startsWith(prefix));
}

function isStaticAsset(pathname) {
  return pathname.startsWith('/build/')
    || STATIC_EXTENSIONS.some((ext) => pathname.toLowerCase().includes(ext));
}

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  const response = await fetch(request);
  if (response && response.ok) {
    const cache = await caches.open(STATIC_CACHE);
    cache.put(request, response.clone());
  }
  return response;
}

async function networkFirst(request) {
  try {
    const response = await fetch(request);
    if (response && response.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) return cached;
    throw error;
  }
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Only handle same-origin GET requests to avoid interfering with CSRF-protected form/API actions.
  if (request.method !== 'GET' || url.origin !== self.location.origin) {
    return;
  }

  if (isExcludedPath(url.pathname)) {
    return;
  }

  if (isStaticAsset(url.pathname)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  event.respondWith(networkFirst(request));
});
