<?php
$this->assign('title', 'Mi QR');
?>

<div class="qr-container">
    <?php if ($qrData): ?>
        <div class="qr-display">
            <div class="qr-code">
                <img src="<?= h($qrData['image_url']) ?>" alt="Código QR" class="qr-image">
            </div>
            
            <div class="qr-info">
                <h3><?= h($user['name'] ?? 'Usuario') ?></h3>
                <p>DNI: <?= h($user['dni'] ?? '') ?></p>
                <p>Equipo: <?= h($user['team'] ?? 'Sin equipo') ?></p>
            </div>

            <div class="qr-status">
                <?= $this->element('status_indicator', [
                    'status' => $user['status'] ?? 'pending',
                    'label' => strtoupper($user['status'] ?? 'Pendiente')
                ]) ?>
                <?php if (isset($user['event'])): ?>
                    <div class="event-info">
                        <small><?= h($user['event']) ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="qr-actions">
            <button class="btn btn-primary btn-block" onclick="saveQR()">
                💾 Guardar en Galería
            </button>
            <button class="btn btn-secondary btn-block" onclick="shareQR()">
                📤 Compartir
            </button>
        </div>

        <div class="qr-instructions">
            <h4>Instrucciones:</h4>
            <ul>
                <li>Muestre este código QR en el acceso al circuito</li>
                <li>Mantenga el código visible y legible</li>
                <li>El código es válido solo para el evento actual</li>
            </ul>
        </div>
    <?php else: ?>
        <div class="qr-error">
            <div class="error-icon">❌</div>
            <h3>Error al generar QR</h3>
            <p>No se pudo generar el código QR. Intente nuevamente.</p>
            <button class="btn btn-primary" onclick="location.reload()">
                🔄 Reintentar
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
function saveQR() {
    const qrImage = document.querySelector('.qr-image');
    if (!qrImage) return;

    // Create a temporary link to download the image
    const link = document.createElement('a');
    link.href = qrImage.src;
    link.download = 'qr-acreditacion-<?= h($user['dni'] ?? '') ?>.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Show success message
    showToast('QR guardado en galería');
}

function shareQR() {
    if (navigator.share) {
        navigator.share({
            title: 'Mi QR - Acreditaciones TN',
            text: 'Mi código QR para acreditaciones',
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy to clipboard
        const qrImage = document.querySelector('.qr-image');
        if (qrImage) {
            navigator.clipboard.writeText(qrImage.src).then(() => {
                showToast('Enlace copiado al portapapeles');
            });
        }
    }
}

function showToast(message) {
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
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Refresh QR data every 10 minutes
setInterval(function() {
    fetch('/api/user/qr')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.image_url) {
                const qrImage = document.querySelector('.qr-image');
                if (qrImage) {
                    qrImage.src = data.data.image_url;
                }
            }
        })
        .catch(error => {
            console.log('Error refreshing QR:', error);
        });
}, 600000); // 10 minutes
</script>
