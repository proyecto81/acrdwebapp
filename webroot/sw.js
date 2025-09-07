/**
 * Service Worker for Acreditaciones TN PWA
 * Handles caching, offline functionality, and background sync
 */

const CACHE_NAME = 'acreditaciones-tn-v1';
const STATIC_CACHE = 'acreditaciones-static-v1';
const DYNAMIC_CACHE = 'acreditaciones-dynamic-v1';
const API_CACHE = 'acreditaciones-api-v1';

// Files to cache immediately
const STATIC_FILES = [
    '/',
    '/login',
    '/css/app.css',
    '/css/components.css',
    '/css/pwa.css',
    '/js/app.js',
    '/js/auth.js',
    '/js/offline.js',
    '/js/qr.js',
    '/manifest.json',
    '/img/icons/icon-192x192.png',
    '/img/icons/icon-512x512.png'
];

// API endpoints to cache
const API_ENDPOINTS = [
    '/api/user/status',
    '/api/user/qr',
    '/api/user/team',
    '/api/user/history',
    '/api/user/promotions'
];

// Install event - cache static files
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Caching static files...');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('Static files cached successfully');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Error caching static files:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && 
                            cacheName !== DYNAMIC_CACHE && 
                            cacheName !== API_CACHE) {
                            console.log('Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - handle requests
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Handle different types of requests
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(handleAPIRequest(request));
    } else if (url.pathname.startsWith('/css/') || 
               url.pathname.startsWith('/js/') || 
               url.pathname.startsWith('/img/')) {
        event.respondWith(handleStaticRequest(request));
    } else {
        event.respondWith(handlePageRequest(request));
    }
});

// Handle API requests
async function handleAPIRequest(request) {
    const url = new URL(request.url);
    
    try {
        // Try network first for API requests
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache successful API responses
            const cache = await caches.open(API_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('Network failed for API request:', url.pathname);
        
        // Try cache as fallback
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            console.log('Serving API from cache:', url.pathname);
            return cachedResponse;
        }
        
        // Return offline response for critical APIs
        if (API_ENDPOINTS.includes(url.pathname)) {
            return createOfflineResponse(url.pathname);
        }
        
        throw error;
    }
}

// Handle static file requests
async function handleStaticRequest(request) {
    try {
        // Try cache first for static files
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Try network
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache for future use
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('Failed to fetch static file:', request.url);
        throw error;
    }
}

// Handle page requests
async function handlePageRequest(request) {
    try {
        // Try network first
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache successful page responses
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('Network failed for page request:', request.url);
        
        // Try cache as fallback
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            console.log('Serving page from cache:', request.url);
            return cachedResponse;
        }
        
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/offline.html') || createOfflinePage();
        }
        
        throw error;
    }
}

// Create offline response for API endpoints
function createOfflineResponse(endpoint) {
    let data = {};
    
    switch (endpoint) {
        case '/api/user/status':
            data = {
                success: true,
                data: {
                    status: 'offline',
                    message: 'Modo offline - datos no actualizados',
                    next_event: null
                }
            };
            break;
            
        case '/api/user/qr':
            data = {
                success: true,
                data: {
                    image_url: '/img/offline-qr.png',
                    user_data: {
                        name: 'Usuario',
                        dni: '00000000',
                        team: 'Sin equipo',
                        status: 'offline',
                        event: 'Modo offline'
                    }
                }
            };
            break;
            
        case '/api/user/team':
            data = {
                success: true,
                data: {
                    team: {
                        name: 'Equipo no disponible',
                        leader: null,
                        members: []
                    }
                }
            };
            break;
            
        case '/api/user/history':
            data = {
                success: true,
                data: {
                    history: [],
                    statistics: {
                        total_races: 0,
                        attendance_percentage: 0,
                        current_year_races: 0,
                        current_year_attendance: 0
                    }
                }
            };
            break;
            
        case '/api/user/promotions':
            data = {
                success: true,
                data: []
            };
            break;
            
        default:
            data = {
                success: false,
                message: 'Servicio no disponible en modo offline'
            };
    }
    
    return new Response(JSON.stringify(data), {
        status: 200,
        headers: {
            'Content-Type': 'application/json',
            'Cache-Control': 'no-cache'
        }
    });
}

// Create offline page
function createOfflinePage() {
    const offlineHTML = `
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sin conexión - Acreditaciones TN</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    margin: 0;
                    padding: 2rem;
                    background: #f5f5f5;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    text-align: center;
                }
                .offline-container {
                    background: white;
                    border-radius: 1rem;
                    padding: 2rem;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    max-width: 400px;
                }
                .offline-icon {
                    font-size: 4rem;
                    margin-bottom: 1rem;
                }
                h1 {
                    color: #333;
                    margin-bottom: 1rem;
                }
                p {
                    color: #666;
                    margin-bottom: 2rem;
                }
                .btn {
                    background: #007AFF;
                    color: white;
                    border: none;
                    padding: 1rem 2rem;
                    border-radius: 0.5rem;
                    font-size: 1rem;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-block;
                }
                .btn:hover {
                    background: #0056CC;
                }
            </style>
        </head>
        <body>
            <div class="offline-container">
                <div class="offline-icon">📡</div>
                <h1>Sin conexión</h1>
                <p>No se puede conectar al servidor. Verifique su conexión a internet e intente nuevamente.</p>
                <a href="/" class="btn">Reintentar</a>
            </div>
        </body>
        </html>
    `;
    
    return new Response(offlineHTML, {
        status: 200,
        headers: {
            'Content-Type': 'text/html',
            'Cache-Control': 'no-cache'
        }
    });
}

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('Background sync triggered:', event.tag);
    
    if (event.tag === 'offline-actions') {
        event.waitUntil(syncOfflineActions());
    }
});

// Sync offline actions when back online
async function syncOfflineActions() {
    try {
        // Get offline actions from IndexedDB or cache
        const offlineActions = await getOfflineActions();
        
        for (const action of offlineActions) {
            try {
                await fetch(action.url, {
                    method: action.method,
                    headers: action.headers,
                    body: action.body
                });
                
                // Remove successful action
                await removeOfflineAction(action.id);
            } catch (error) {
                console.error('Failed to sync action:', action, error);
            }
        }
    } catch (error) {
        console.error('Error syncing offline actions:', error);
    }
}

// Get offline actions (placeholder - would use IndexedDB in real implementation)
async function getOfflineActions() {
    // This would retrieve actions from IndexedDB
    return [];
}

// Remove offline action (placeholder)
async function removeOfflineAction(actionId) {
    // This would remove action from IndexedDB
    console.log('Removing offline action:', actionId);
}

// Push notifications
self.addEventListener('push', (event) => {
    console.log('Push notification received:', event);
    
    const options = {
        body: 'Nueva notificación de Acreditaciones TN',
        icon: '/img/icons/icon-192x192.png',
        badge: '/img/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            url: '/'
        },
        actions: [
            {
                action: 'open',
                title: 'Abrir app',
                icon: '/img/icons/icon-72x72.png'
            },
            {
                action: 'close',
                title: 'Cerrar',
                icon: '/img/icons/icon-72x72.png'
            }
        ]
    };
    
    if (event.data) {
        const data = event.data.json();
        options.body = data.body || options.body;
        options.data = { ...options.data, ...data };
    }
    
    event.waitUntil(
        self.registration.showNotification('Acreditaciones TN', options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    if (event.action === 'open' || !event.action) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

// Handle notification close
self.addEventListener('notificationclose', (event) => {
    console.log('Notification closed:', event);
});

// Message handling from main thread
self.addEventListener('message', (event) => {
    console.log('Message received in service worker:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            caches.open(DYNAMIC_CACHE)
                .then(cache => cache.addAll(event.data.urls))
        );
    }
});

// Periodic background sync (if supported)
self.addEventListener('periodicsync', (event) => {
    console.log('Periodic sync triggered:', event.tag);
    
    if (event.tag === 'content-sync') {
        event.waitUntil(syncContent());
    }
});

// Sync content periodically
async function syncContent() {
    try {
        // Sync critical data
        const criticalUrls = [
            '/api/user/status',
            '/api/user/qr',
            '/api/user/team'
        ];
        
        for (const url of criticalUrls) {
            try {
                const response = await fetch(url);
                if (response.ok) {
                    const cache = await caches.open(API_CACHE);
                    cache.put(url, response.clone());
                }
            } catch (error) {
                console.error('Failed to sync:', url, error);
            }
        }
    } catch (error) {
        console.error('Error in periodic sync:', error);
    }
}
