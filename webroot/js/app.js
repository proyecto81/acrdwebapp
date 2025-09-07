/**
 * Main Application JavaScript
 * Handles general app functionality, PWA features, and user interactions
 */

class App {
    constructor() {
        this.isOnline = navigator.onLine;
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupPWA();
        this.setupOfflineHandling();
        this.setupPullToRefresh();
        this.setupSwipeGestures();
        this.setupHapticFeedback();
        this.checkInstallStatus();
    }

    setupEventListeners() {
        // Online/Offline status
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.hideOfflineIndicator();
            this.syncOfflineData();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.showOfflineIndicator();
        });

        // Before install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallPrompt();
        });

        // App installed
        window.addEventListener('appinstalled', () => {
            this.isInstalled = true;
            this.hideInstallPrompt();
            this.showToast('App instalada correctamente');
        });

        // Visibility change (app backgrounded/foregrounded)
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.onAppForeground();
            } else {
                this.onAppBackground();
            }
        });

        // Form submissions
        document.addEventListener('submit', (e) => {
            this.handleFormSubmission(e);
        });

        // Button clicks
        document.addEventListener('click', (e) => {
            this.handleButtonClick(e);
        });
    }

    setupPWA() {
        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                    this.setupServiceWorkerUpdates(registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        }

        // Setup app-like behavior
        this.setupAppLikeBehavior();
    }

    setupAppLikeBehavior() {
        // Prevent context menu on long press
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });

        // Prevent text selection
        document.addEventListener('selectstart', (e) => {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (e) => {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    }

    setupServiceWorkerUpdates(registration) {
        registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;
            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    this.showUpdatePrompt();
                }
            });
        });
    }

    setupOfflineHandling() {
        // Show offline indicator when needed
        if (!this.isOnline) {
            this.showOfflineIndicator();
        }

        // Cache critical data for offline use
        this.cacheCriticalData();
    }

    setupPullToRefresh() {
        let startY = 0;
        let currentY = 0;
        let isPulling = false;
        let pullDistance = 0;

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
                isPulling = true;
            }
        });

        document.addEventListener('touchmove', (e) => {
            if (isPulling && window.scrollY === 0) {
                currentY = e.touches[0].clientY;
                pullDistance = currentY - startY;

                if (pullDistance > 0) {
                    e.preventDefault();
                    this.updatePullToRefresh(pullDistance);
                }
            }
        });

        document.addEventListener('touchend', () => {
            if (isPulling && pullDistance > 100) {
                this.triggerRefresh();
            }
            this.resetPullToRefresh();
            isPulling = false;
        });
    }

    setupSwipeGestures() {
        let startX = 0;
        let startY = 0;
        let endX = 0;
        let endY = 0;

        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        });

        document.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            endY = e.changedTouches[0].clientY;
            this.handleSwipe(startX, startY, endX, endY);
        });
    }

    setupHapticFeedback() {
        // Add haptic feedback to buttons
        const buttons = document.querySelectorAll('.btn, .nav-item, .action-btn');
        buttons.forEach(button => {
            button.addEventListener('touchstart', () => {
                this.triggerHaptic('light');
            });
        });
    }

    handleSwipe(startX, startY, endX, endY) {
        const deltaX = endX - startX;
        const deltaY = endY - startY;
        const minSwipeDistance = 50;

        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
            if (deltaX > 0) {
                this.handleSwipeRight();
            } else {
                this.handleSwipeLeft();
            }
        }
    }

    handleSwipeRight() {
        // Navigate back or show previous content
        if (window.history.length > 1) {
            window.history.back();
        }
    }

    handleSwipeLeft() {
        // Navigate forward or show next content
        // This could be used for navigation between sections
    }

    handleFormSubmission(e) {
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn) {
            this.triggerHaptic('medium');
            this.showLoadingState(submitBtn);
        }
    }

    handleButtonClick(e) {
        const button = e.target.closest('.btn, .nav-item, .action-btn');
        if (button) {
            this.triggerHaptic('light');
        }
    }

    triggerHaptic(type = 'light') {
        if ('vibrate' in navigator) {
            const patterns = {
                light: [10],
                medium: [20],
                heavy: [30]
            };
            navigator.vibrate(patterns[type] || patterns.light);
        }

        // Visual feedback
        const element = event.target;
        if (element) {
            element.classList.add(`haptic-${type}`);
            setTimeout(() => {
                element.classList.remove(`haptic-${type}`);
            }, 200);
        }
    }

    showOfflineIndicator() {
        const indicator = document.querySelector('.offline-indicator');
        if (indicator) {
            indicator.classList.add('show');
        }
    }

    hideOfflineIndicator() {
        const indicator = document.querySelector('.offline-indicator');
        if (indicator) {
            indicator.classList.remove('show');
        }
    }

    showInstallPrompt() {
        if (this.isInstalled || !this.deferredPrompt) return;

        const prompt = document.querySelector('.install-prompt');
        if (prompt) {
            prompt.classList.add('show');
        }
    }

    hideInstallPrompt() {
        const prompt = document.querySelector('.install-prompt');
        if (prompt) {
            prompt.classList.remove('show');
        }
    }

    async installApp() {
        if (!this.deferredPrompt) return;

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
            this.showToast('Instalando app...');
        }
        
        this.deferredPrompt = null;
        this.hideInstallPrompt();
    }

    showUpdatePrompt() {
        if (confirm('Hay una nueva versión disponible. ¿Desea actualizar?')) {
            window.location.reload();
        }
    }

    updatePullToRefresh(distance) {
        const indicator = document.querySelector('.pull-to-refresh');
        if (indicator) {
            const progress = Math.min(distance / 100, 1);
            indicator.style.transform = `translateX(-50%) translateY(${distance - 60}px)`;
            
            if (distance > 100) {
                indicator.classList.add('show');
            }
        }
    }

    resetPullToRefresh() {
        const indicator = document.querySelector('.pull-to-refresh');
        if (indicator) {
            indicator.style.transform = 'translateX(-50%) translateY(-60px)';
            indicator.classList.remove('show');
        }
    }

    triggerRefresh() {
        this.showToast('Actualizando...');
        window.location.reload();
    }

    showLoadingState(button) {
        const originalText = button.textContent;
        button.textContent = 'Cargando...';
        button.disabled = true;
        
        // Restore after 3 seconds as fallback
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
        }, 3000);
    }

    showToast(message, duration = 3000) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, duration);
    }

    cacheCriticalData() {
        // Cache user data, QR codes, and other critical information
        const criticalData = {
            user: this.getCurrentUser(),
            qr: this.getCachedQR(),
            team: this.getCachedTeam(),
            timestamp: Date.now()
        };
        
        localStorage.setItem('criticalData', JSON.stringify(criticalData));
    }

    syncOfflineData() {
        // Sync any offline changes when back online
        const offlineData = localStorage.getItem('offlineChanges');
        if (offlineData) {
            try {
                const changes = JSON.parse(offlineData);
                this.processOfflineChanges(changes);
                localStorage.removeItem('offlineChanges');
            } catch (e) {
                console.error('Error syncing offline data:', e);
            }
        }
    }

    processOfflineChanges(changes) {
        // Process any changes made while offline
        changes.forEach(change => {
            // Send to server
            this.sendOfflineChange(change);
        });
    }

    sendOfflineChange(change) {
        // Send offline change to server
        fetch('/api/offline-sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(change)
        }).catch(error => {
            console.error('Error sending offline change:', error);
        });
    }

    getCurrentUser() {
        // Get current user data from page
        const userElement = document.querySelector('[data-user]');
        return userElement ? JSON.parse(userElement.dataset.user) : null;
    }

    getCachedQR() {
        // Get cached QR data
        return localStorage.getItem('qrData');
    }

    getCachedTeam() {
        // Get cached team data
        return localStorage.getItem('teamData');
    }

    checkInstallStatus() {
        // Check if app is already installed
        if (window.matchMedia('(display-mode: standalone)').matches) {
            this.isInstalled = true;
        }
    }

    onAppForeground() {
        // App came to foreground
        this.refreshData();
    }

    onAppBackground() {
        // App went to background
        this.cacheCriticalData();
    }

    refreshData() {
        // Refresh critical data when app comes to foreground
        if (this.isOnline) {
            // Trigger data refresh for current page
            const refreshEvent = new CustomEvent('appRefresh');
            document.dispatchEvent(refreshEvent);
        }
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});

// Export for use in other scripts
window.App = App;
