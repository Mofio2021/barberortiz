// Service Worker — Barber Ortiz PWA
// Estrategia: Network-first (el backend es dinámico, no cachea contenido)

const CACHE_NAME = 'barberortiz-v1';

// Activa inmediatamente sin esperar a que se cierre la pestaña anterior
self.addEventListener('install', (e) => {
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    // Limpia caches de versiones anteriores
    e.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    return clients.claim();
});

// Network-first: siempre va al servidor, fallback offline solo si falla
self.addEventListener('fetch', (e) => {
    // Solo intercepta requests del mismo origin
    if (!e.request.url.startsWith(self.location.origin)) return;
    // No intercepta peticiones POST (POS, Livewire)
    if (e.request.method !== 'GET') return;

    e.respondWith(
        fetch(e.request).catch(() =>
            caches.match(e.request)
        )
    );
});
