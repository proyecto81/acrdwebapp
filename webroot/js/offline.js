/**
 * Offline Functionality JavaScript
 * Handles offline data storage, sync, and functionality
 */

class OfflineManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.offlineQueue = [];
        this.syncInProgress = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadOfflineQueue();
        this.setupPeriodicSync();
    }

    setupEventListeners() {
        // Online/Offline status
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.onOnline();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.onOffline();
        });

        // App refresh event
        document.addEventListener('appRefresh', () => {
            this.syncOfflineData();
        });

        // Before unload - save critical data
        window.addEventListener('beforeunload', () => {
            this.saveCriticalData();
        });
    }

    onOnline() {
        console.log('App is online');
        this.syncOfflineData();
        this.updateOfflineIndicator(false);
    }

    onOffline() {
        console.log('App is offline');
        this.updateOfflineIndicator(true);
        this.enableOfflineMode();
    }

    updateOfflineIndicator(show) {
        const indicator = document.querySelector('.offline-indicator');
        if (indicator) {
            if (show) {
                indicator.classList.add('show');
            } else {
                indicator.classList.remove('show');
            }
        }
    }

    enableOfflineMode() {
        // Cache current page data
        this.cacheCurrentPageData();
        
        // Show offline message
        this.showOfflineMessage();
    }

    showOfflineMessage() {
        const message = document.createElement('div');
        message.className = 'offline-message';
        message.innerHTML = `
            <div class="offline-content">
                <div class="offline-icon">📡</div>
                <div class="offline-text">
                    <h4>Sin conexión</h4>
                    <p>Algunas funciones pueden estar limitadas</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(message);
        
        setTimeout(() => {
            message.classList.add('show');
        }, 100);
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            message.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(message)) {
                    document.body.removeChild(message);
                }
            }, 300);
        }, 3000);
    }

    cacheCurrentPageData() {
        const pageData = {
            url: window.location.pathname,
            timestamp: Date.now(),
            data: this.extractPageData()
        };

        // Store page data
        const cacheKey = `page_${pageData.url}`;
        localStorage.setItem(cacheKey, JSON.stringify(pageData));

        // Update cache index
        this.updateCacheIndex(cacheKey);
    }

    extractPageData() {
        const data = {};

        // Extract user data
        const userElement = document.querySelector('[data-user]');
        if (userElement) {
            data.user = JSON.parse(userElement.dataset.user);
        }

        // Extract QR data
        const qrImage = document.querySelector('.qr-image');
        if (qrImage) {
            data.qrUrl = qrImage.src;
        }

        // Extract team data
        const teamData = this.extractTeamData();
        if (teamData) {
            data.team = teamData;
        }

        // Extract history data
        const historyData = this.extractHistoryData();
        if (historyData) {
            data.history = historyData;
        }

        // Extract promotions data
        const promotionsData = this.extractPromotionsData();
        if (promotionsData) {
            data.promotions = promotionsData;
        }

        return data;
    }

    extractTeamData() {
        const teamSection = document.querySelector('.team-section');
        if (!teamSection) return null;

        const team = {
            name: '',
            leader: null,
            members: []
        };

        // Extract team name
        const teamName = teamSection.querySelector('h2');
        if (teamName) {
            team.name = teamName.textContent;
        }

        // Extract leader data
        const leaderCard = teamSection.querySelector('.leader-card');
        if (leaderCard) {
            team.leader = this.extractMemberData(leaderCard);
        }

        // Extract members data
        const memberCards = teamSection.querySelectorAll('.member-card');
        memberCards.forEach(card => {
            const member = this.extractMemberData(card);
            if (member) {
                team.members.push(member);
            }
        });

        return team;
    }

    extractMemberData(card) {
        const nameElement = card.querySelector('h4');
        const phoneElement = card.querySelector('p');
        const statusElement = card.querySelector('.status-indicator');

        if (!nameElement) return null;

        return {
            name: nameElement.textContent,
            phone: phoneElement ? phoneElement.textContent : '',
            status: statusElement ? this.getStatusFromElement(statusElement) : 'unknown'
        };
    }

    getStatusFromElement(element) {
        if (element.classList.contains('status-active')) return 'active';
        if (element.classList.contains('status-pending')) return 'pending';
        if (element.classList.contains('status-inactive')) return 'inactive';
        return 'unknown';
    }

    extractHistoryData() {
        const historyItems = document.querySelectorAll('.history-item');
        if (historyItems.length === 0) return null;

        const history = [];
        historyItems.forEach(item => {
            const raceInfo = item.querySelector('.race-info');
            const statusElement = item.querySelector('.status-indicator');
            
            if (raceInfo) {
                const title = raceInfo.querySelector('h4');
                const date = raceInfo.querySelector('.race-date');
                const location = raceInfo.querySelector('.race-location');
                
                history.push({
                    circuit: title ? title.textContent : '',
                    date: date ? date.textContent : '',
                    location: location ? location.textContent.replace('📍 ', '') : '',
                    status: statusElement ? this.getStatusFromElement(statusElement) : 'unknown'
                });
            }
        });

        return history;
    }

    extractPromotionsData() {
        const promotionCards = document.querySelectorAll('.promotion-card');
        if (promotionCards.length === 0) return null;

        const promotions = [];
        promotionCards.forEach(card => {
            const title = card.querySelector('.promotion-title');
            const description = card.querySelector('.promotion-description');
            const discount = card.querySelector('.promotion-discount');
            const validity = card.querySelector('.promotion-validity');

            if (title) {
                promotions.push({
                    title: title.textContent,
                    description: description ? description.textContent : '',
                    discount: discount ? discount.textContent : '',
                    validUntil: validity ? validity.textContent.replace('Válido hasta: ', '') : '',
                    active: !card.classList.contains('inactive')
                });
            }
        });

        return promotions;
    }

    updateCacheIndex(cacheKey) {
        let index = JSON.parse(localStorage.getItem('cacheIndex') || '[]');
        
        if (!index.includes(cacheKey)) {
            index.push(cacheKey);
            
            // Limit cache size (keep last 10 pages)
            if (index.length > 10) {
                const oldKey = index.shift();
                localStorage.removeItem(oldKey);
            }
            
            localStorage.setItem('cacheIndex', JSON.stringify(index));
        }
    }

    saveCriticalData() {
        const criticalData = {
            user: this.getCurrentUser(),
            qr: this.getCachedQR(),
            team: this.getCachedTeam(),
            timestamp: Date.now()
        };

        localStorage.setItem('criticalData', JSON.stringify(criticalData));
    }

    getCurrentUser() {
        const userElement = document.querySelector('[data-user]');
        return userElement ? JSON.parse(userElement.dataset.user) : null;
    }

    getCachedQR() {
        const qrImage = document.querySelector('.qr-image');
        return qrImage ? qrImage.src : null;
    }

    getCachedTeam() {
        return this.extractTeamData();
    }

    queueOfflineAction(action) {
        this.offlineQueue.push({
            ...action,
            timestamp: Date.now(),
            id: this.generateActionId()
        });

        this.saveOfflineQueue();
    }

    generateActionId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    saveOfflineQueue() {
        localStorage.setItem('offlineQueue', JSON.stringify(this.offlineQueue));
    }

    loadOfflineQueue() {
        const queue = localStorage.getItem('offlineQueue');
        if (queue) {
            try {
                this.offlineQueue = JSON.parse(queue);
            } catch (error) {
                console.error('Error loading offline queue:', error);
                this.offlineQueue = [];
            }
        }
    }

    async syncOfflineData() {
        if (!this.isOnline || this.syncInProgress || this.offlineQueue.length === 0) {
            return;
        }

        this.syncInProgress = true;
        console.log('Syncing offline data...');

        const actionsToSync = [...this.offlineQueue];
        const successfulActions = [];

        for (const action of actionsToSync) {
            try {
                await this.syncAction(action);
                successfulActions.push(action.id);
            } catch (error) {
                console.error('Error syncing action:', action, error);
            }
        }

        // Remove successful actions from queue
        this.offlineQueue = this.offlineQueue.filter(
            action => !successfulActions.includes(action.id)
        );

        this.saveOfflineQueue();
        this.syncInProgress = false;

        if (successfulActions.length > 0) {
            this.showSyncSuccess(successfulActions.length);
        }
    }

    async syncAction(action) {
        const response = await fetch(action.url, {
            method: action.method,
            headers: action.headers,
            body: action.body
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    showSyncSuccess(count) {
        const message = document.createElement('div');
        message.className = 'sync-success';
        message.innerHTML = `
            <div class="sync-content">
                <div class="sync-icon">✅</div>
                <div class="sync-text">
                    <h4>Sincronización completada</h4>
                    <p>${count} acción(es) sincronizada(s)</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(message);
        
        setTimeout(() => {
            message.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            message.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(message)) {
                    document.body.removeChild(message);
                }
            }, 300);
        }, 3000);
    }

    setupPeriodicSync() {
        // Sync every 30 seconds when online
        setInterval(() => {
            if (this.isOnline && !this.syncInProgress) {
                this.syncOfflineData();
            }
        }, 30000);
    }

    // Offline form submission
    async submitFormOffline(form, url, method = 'POST') {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const action = {
            url: url,
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': this.getAuthToken()
            },
            body: JSON.stringify(data)
        };

        if (this.isOnline) {
            try {
                return await this.syncAction(action);
            } catch (error) {
                // If online but sync fails, queue for later
                this.queueOfflineAction(action);
                throw error;
            }
        } else {
            // Queue for later sync
            this.queueOfflineAction(action);
            return { success: true, message: 'Datos guardados para sincronización' };
        }
    }

    getAuthToken() {
        return localStorage.getItem('authToken') || '';
    }

    // Offline data retrieval
    getOfflineData(key) {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    }

    setOfflineData(key, data) {
        localStorage.setItem(key, JSON.stringify(data));
    }

    // Cache management
    clearCache() {
        const index = JSON.parse(localStorage.getItem('cacheIndex') || '[]');
        index.forEach(key => {
            localStorage.removeItem(key);
        });
        localStorage.removeItem('cacheIndex');
    }

    getCacheSize() {
        let totalSize = 0;
        for (let key in localStorage) {
            if (localStorage.hasOwnProperty(key)) {
                totalSize += localStorage[key].length;
            }
        }
        return totalSize;
    }

    // Offline status check
    isOffline() {
        return !this.isOnline;
    }

    getOfflineQueueLength() {
        return this.offlineQueue.length;
    }
}

// Initialize offline manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.offlineManager = new OfflineManager();
});

// Export for use in other scripts
window.OfflineManager = OfflineManager;
