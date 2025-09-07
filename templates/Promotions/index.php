<?php
$this->assign('title', 'Promociones');
?>

<div class="promotions-container">
    <div class="promotions-header">
        <h2>Promociones</h2>
        <p>Ofertas especiales y beneficios exclusivos</p>
    </div>

    <div class="promotions-list">
        <?php if (!empty($promotions)): ?>
            <?php foreach ($promotions as $promotion): ?>
                <?= $this->element('promotion_card', ['promotion' => $promotion]) ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-promotions">
                <div class="no-promotions-icon">🎯</div>
                <h4>No hay promociones disponibles</h4>
                <p>Las promociones aparecerán aquí cuando estén disponibles.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Refresh Button -->
    <div class="promotions-actions">
        <button class="btn btn-secondary btn-block" onclick="refreshPromotions()">
            🔄 Actualizar Promociones
        </button>
    </div>
</div>

<script>
function refreshPromotions() {
    const refreshBtn = document.querySelector('.promotions-actions button');
    const originalText = refreshBtn.textContent;
    
    refreshBtn.textContent = '🔄 Actualizando...';
    refreshBtn.disabled = true;

    fetch('/api/user/promotions')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePromotionsDisplay(data.data);
                showToast('Promociones actualizadas');
            } else {
                showToast('Error al actualizar promociones');
            }
        })
        .catch(error => {
            console.log('Error refreshing promotions:', error);
            showToast('Error al actualizar promociones');
        })
        .finally(() => {
            refreshBtn.textContent = originalText;
            refreshBtn.disabled = false;
        });
}

function updatePromotionsDisplay(promotions) {
    const promotionsList = document.querySelector('.promotions-list');
    if (!promotionsList) return;

    if (promotions.length === 0) {
        promotionsList.innerHTML = `
            <div class="no-promotions">
                <div class="no-promotions-icon">🎯</div>
                <h4>No hay promociones disponibles</h4>
                <p>Las promociones aparecerán aquí cuando estén disponibles.</p>
            </div>
        `;
        return;
    }

    // Update promotions list
    promotionsList.innerHTML = promotions.map(promotion => `
        <div class="promotion-card ${promotion.type || 'discount'} ${promotion.active ? 'active' : 'inactive'}">
            <div class="promotion-header">
                <h3 class="promotion-title">${promotion.title || 'Promoción'}</h3>
                ${promotion.discount ? `<div class="promotion-discount">${promotion.discount}</div>` : ''}
            </div>
            
            <div class="promotion-content">
                <p class="promotion-description">${promotion.description || ''}</p>
                
                ${promotion.valid_until ? `
                    <div class="promotion-validity">
                        <small>Válido hasta: ${promotion.valid_until}</small>
                    </div>
                ` : ''}
            </div>
            
            ${promotion.active ? `
                <div class="promotion-action">
                    <button class="btn btn-primary btn-sm" onclick="applyPromotion(${promotion.id})">Aplicar</button>
                </div>
            ` : ''}
        </div>
    `).join('');
}

function applyPromotion(promotionId) {
    // Show loading state
    const applyBtn = event.target;
    const originalText = applyBtn.textContent;
    applyBtn.textContent = 'Aplicando...';
    applyBtn.disabled = true;

    // Simulate API call
    setTimeout(() => {
        showToast('Promoción aplicada correctamente');
        applyBtn.textContent = 'Aplicada';
        applyBtn.classList.add('applied');
    }, 1000);
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

// Auto-refresh promotions every 15 minutes
setInterval(function() {
    fetch('/api/user/promotions')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePromotionsDisplay(data.data);
            }
        })
        .catch(error => {
            console.log('Error auto-refreshing promotions:', error);
        });
}, 900000); // 15 minutes
</script>
