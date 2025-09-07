<?php
$this->assign('title', 'Dashboard');
?>

<div class="dashboard-container">
    <!-- Status Section -->
    <div class="status-section">
        <h2>Estado Actual</h2>
        <div class="status-card <?= $status['status'] ?? 'pending' ?>">
            <div class="status-content">
                <?= $this->element('status_indicator', [
                    'status' => $status['status'] ?? 'pending',
                    'label' => $status['message'] ?? 'Estado pendiente'
                ]) ?>
                <?php if (isset($status['next_event'])): ?>
                    <div class="next-event">
                        <small>Próxima fecha: <?= h($status['next_event']) ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Acceso Rápido</h2>
        <div class="action-buttons">
            <a href="/qr" class="action-btn primary">
                <div class="action-icon">📱</div>
                <div class="action-label">Ver Mi QR</div>
            </a>
            <a href="/promotions" class="action-btn secondary">
                <div class="action-icon">🎯</div>
                <div class="action-label">Promociones</div>
            </a>
        </div>
    </div>

    <!-- Promotions Section -->
    <?php if (!empty($promotions)): ?>
        <div class="promotions-section">
            <h2>Promociones</h2>
            <div class="promotions-list">
                <?php foreach (array_slice($promotions, 0, 2) as $promotion): ?>
                    <?= $this->element('promotion_card', ['promotion' => $promotion]) ?>
                <?php endforeach; ?>
            </div>
            <?php if (count($promotions) > 2): ?>
                <div class="view-all">
                    <a href="/promotions" class="btn btn-outline">Ver todas las promociones</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- User Info -->
    <div class="user-info-section">
        <div class="user-card">
            <div class="user-avatar">
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
            </div>
            <div class="user-details">
                <h3><?= h($user['name'] ?? 'Usuario') ?></h3>
                <p>DNI: <?= h($user['dni'] ?? '') ?></p>
                <p>Equipo: <?= h($user['team'] ?? 'Sin equipo') ?></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh status every 5 minutes
    setInterval(function() {
        fetch('/api/user/status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update status display
                    updateStatusDisplay(data.data);
                }
            })
            .catch(error => {
                console.log('Error refreshing status:', error);
            });
    }, 300000); // 5 minutes

    function updateStatusDisplay(statusData) {
        const statusCard = document.querySelector('.status-card');
        const statusIndicator = document.querySelector('.status-indicator');
        
        if (statusCard && statusIndicator) {
            statusCard.className = `status-card ${statusData.status}`;
            statusIndicator.className = `status-indicator status-${statusData.status}`;
            statusIndicator.querySelector('.status-label').textContent = statusData.message;
        }
    }
});
</script>
