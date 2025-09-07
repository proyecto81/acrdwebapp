/**
 * QR Code JavaScript
 * Handles QR code generation, display, and interaction
 */

class QRManager {
    constructor() {
        this.currentQR = null;
        this.qrCanvas = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupQRDisplay();
    }

    setupEventListeners() {
        // QR save button
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="save-qr"]')) {
                this.saveQR();
            }
            
            if (e.target.matches('[data-action="share-qr"]')) {
                this.shareQR();
            }
            
            if (e.target.matches('[data-action="refresh-qr"]')) {
                this.refreshQR();
            }
        });

        // QR code click to zoom
        document.addEventListener('click', (e) => {
            if (e.target.matches('.qr-image')) {
                this.showQRModal(e.target.src);
            }
        });

        // Auto-refresh QR every 10 minutes
        setInterval(() => {
            this.refreshQR();
        }, 600000);
    }

    setupQRDisplay() {
        // Initialize QR display if on QR page
        if (window.location.pathname.includes('/qr')) {
            this.loadQRData();
        }
    }

    async loadQRData() {
        try {
            const response = await fetch('/api/user/qr');
            const data = await response.json();
            
            if (data.success) {
                this.currentQR = data.data;
                this.updateQRDisplay();
            } else {
                this.showQRError('Error al cargar código QR');
            }
        } catch (error) {
            console.error('Error loading QR data:', error);
            this.showQRError('Error de conexión');
        }
    }

    updateQRDisplay() {
        if (!this.currentQR) return;

        const qrImage = document.querySelector('.qr-image');
        const qrInfo = document.querySelector('.qr-info');
        const qrStatus = document.querySelector('.qr-status');

        if (qrImage) {
            qrImage.src = this.currentQR.image_url;
            qrImage.alt = 'Código QR de acreditación';
        }

        if (qrInfo && this.currentQR.user_data) {
            const userData = this.currentQR.user_data;
            qrInfo.innerHTML = `
                <h3>${this.escapeHtml(userData.name || 'Usuario')}</h3>
                <p>DNI: ${this.escapeHtml(userData.dni || '')}</p>
                <p>Equipo: ${this.escapeHtml(userData.team || 'Sin equipo')}</p>
            `;
        }

        if (qrStatus && this.currentQR.user_data) {
            const userData = this.currentQR.user_data;
            const statusElement = qrStatus.querySelector('.status-indicator');
            if (statusElement) {
                statusElement.className = `status-indicator status-${userData.status || 'pending'}`;
                statusElement.querySelector('.status-label').textContent = 
                    this.capitalizeFirst(userData.status || 'Pendiente');
            }

            if (userData.event) {
                const eventInfo = qrStatus.querySelector('.event-info');
                if (eventInfo) {
                    eventInfo.innerHTML = `<small>${this.escapeHtml(userData.event)}</small>`;
                }
            }
        }

        // Cache QR data for offline use
        this.cacheQRData();
    }

    async refreshQR() {
        const refreshBtn = document.querySelector('[data-action="refresh-qr"]');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.textContent = 'Actualizando...';
        }

        try {
            await this.loadQRData();
            this.showToast('QR actualizado correctamente');
        } catch (error) {
            console.error('Error refreshing QR:', error);
            this.showToast('Error al actualizar QR', 'error');
        } finally {
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.textContent = '🔄 Actualizar';
            }
        }
    }

    async saveQR() {
        if (!this.currentQR) {
            this.showToast('No hay código QR para guardar', 'error');
            return;
        }

        try {
            // Create a temporary link to download the image
            const response = await fetch(this.currentQR.image_url);
            const blob = await response.blob();
            
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `qr-acreditacion-${this.currentQR.user_data?.dni || 'usuario'}.png`;
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            window.URL.revokeObjectURL(url);
            
            this.showToast('QR guardado en galería');
            this.triggerHaptic('medium');
        } catch (error) {
            console.error('Error saving QR:', error);
            this.showToast('Error al guardar QR', 'error');
        }
    }

    async shareQR() {
        if (!this.currentQR) {
            this.showToast('No hay código QR para compartir', 'error');
            return;
        }

        try {
            if (navigator.share) {
                // Use native sharing if available
                const response = await fetch(this.currentQR.image_url);
                const blob = await response.blob();
                const file = new File([blob], 'qr-acreditacion.png', { type: 'image/png' });

                await navigator.share({
                    title: 'Mi QR - Acreditaciones TN',
                    text: 'Mi código QR para acreditaciones',
                    files: [file]
                });
            } else {
                // Fallback: copy to clipboard
                await this.copyQRToClipboard();
            }
            
            this.showToast('QR compartido correctamente');
            this.triggerHaptic('medium');
        } catch (error) {
            console.error('Error sharing QR:', error);
            this.showToast('Error al compartir QR', 'error');
        }
    }

    async copyQRToClipboard() {
        try {
            const response = await fetch(this.currentQR.image_url);
            const blob = await response.blob();
            
            await navigator.clipboard.write([
                new ClipboardItem({
                    'image/png': blob
                })
            ]);
            
            this.showToast('QR copiado al portapapeles');
        } catch (error) {
            // Fallback: copy URL
            await navigator.clipboard.writeText(this.currentQR.image_url);
            this.showToast('Enlace copiado al portapapeles');
        }
    }

    showQRModal(imageSrc) {
        const modal = document.createElement('div');
        modal.className = 'qr-modal';
        modal.innerHTML = `
            <div class="qr-modal-content">
                <div class="qr-modal-header">
                    <h3>Código QR</h3>
                    <button class="qr-modal-close" data-action="close-modal">×</button>
                </div>
                <div class="qr-modal-body">
                    <img src="${imageSrc}" alt="Código QR" class="qr-modal-image">
                </div>
                <div class="qr-modal-footer">
                    <button class="btn btn-primary" data-action="save-qr">💾 Guardar</button>
                    <button class="btn btn-secondary" data-action="share-qr">📤 Compartir</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        setTimeout(() => {
            modal.classList.add('show');
        }, 100);

        // Close modal handlers
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.matches('[data-action="close-modal"]')) {
                this.closeQRModal(modal);
            }
        });

        // Handle modal actions
        modal.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="save-qr"]')) {
                this.saveQR();
            } else if (e.target.matches('[data-action="share-qr"]')) {
                this.shareQR();
            }
        });
    }

    closeQRModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(modal)) {
                document.body.removeChild(modal);
            }
        }, 300);
    }

    showQRError(message) {
        const errorContainer = document.querySelector('.qr-error');
        if (errorContainer) {
            errorContainer.style.display = 'block';
            errorContainer.querySelector('h3').textContent = 'Error al generar QR';
            errorContainer.querySelector('p').textContent = message;
        } else {
            this.showToast(message, 'error');
        }
    }

    cacheQRData() {
        if (this.currentQR) {
            localStorage.setItem('qrData', JSON.stringify({
                ...this.currentQR,
                cachedAt: Date.now()
            }));
        }
    }

    getCachedQRData() {
        const cached = localStorage.getItem('qrData');
        if (cached) {
            try {
                const data = JSON.parse(cached);
                // Check if cache is not too old (1 hour)
                if (Date.now() - data.cachedAt < 3600000) {
                    return data;
                }
            } catch (error) {
                console.error('Error parsing cached QR data:', error);
            }
        }
        return null;
    }

    generateQRCode(data, size = 200) {
        // Generate QR code using external service
        const encodedData = encodeURIComponent(JSON.stringify(data));
        return `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodedData}`;
    }

    validateQRData(qrData) {
        try {
            const data = typeof qrData === 'string' ? JSON.parse(qrData) : qrData;
            
            const requiredFields = ['dni', 'name', 'timestamp'];
            for (const field of requiredFields) {
                if (!data[field]) {
                    throw new Error(`Missing required field: ${field}`);
                }
            }

            // Check if QR is not too old (24 hours)
            const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
            if (Date.now() - data.timestamp > maxAge) {
                throw new Error('QR code has expired');
            }

            return data;
        } catch (error) {
            throw new Error('Invalid QR code data: ' + error.message);
        }
    }

    scanQRCode() {
        // This would integrate with a QR scanner library
        // For now, we'll show a placeholder
        this.showToast('Función de escaneo no disponible', 'warning');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
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
        }, 3000);
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
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Initialize QR manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.qrManager = new QRManager();
});

// Export for use in other scripts
window.QRManager = QRManager;
