<?php
$this->assign('title', 'Mi Historial');
?>

<div class="history-container">
    <div class="history-header">
        <h2>Mis Participaciones</h2>
        <p>Historial de carreras y estadísticas</p>
    </div>

    <!-- Statistics -->
    <?php if (!empty($statistics)): ?>
        <div class="statistics-section">
            <h3>Estadísticas <?= date('Y') ?></h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= h($statistics['current_year_races'] ?? 0) ?></div>
                    <div class="stat-label">Carreras</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= h($statistics['current_year_attendance'] ?? 0) ?>%</div>
                    <div class="stat-label">Asistencia</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= h($statistics['total_races'] ?? 0) ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= h($statistics['attendance_percentage'] ?? 0) ?>%</div>
                    <div class="stat-label">Promedio</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- History List -->
    <div class="history-section">
        <h3>Últimas Carreras</h3>
        <div class="history-list">
            <?php if (!empty($history)): ?>
                <?php foreach ($history as $race): ?>
                    <div class="history-item">
                        <div class="race-info">
                            <h4><?= h($race['circuit'] ?? 'Circuito') ?></h4>
                            <p class="race-date"><?= h($race['date'] ?? '') ?></p>
                            <p class="race-location">📍 <?= h($race['location'] ?? '') ?></p>
                        </div>
                        <div class="race-status">
                            <?= $this->element('status_indicator', [
                                'status' => $race['status'] ?? 'pending',
                                'label' => ucfirst($race['status'] ?? 'Pendiente')
                            ]) ?>
                        </div>
                        <div class="race-details">
                            <?php if (isset($race['position'])): ?>
                                <div class="race-position">
                                    <span class="position-label">Posición:</span>
                                    <span class="position-value"><?= h($race['position']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($race['points'])): ?>
                                <div class="race-points">
                                    <span class="points-label">Puntos:</span>
                                    <span class="points-value"><?= h($race['points']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-history">
                    <div class="no-history-icon">📋</div>
                    <h4>No hay historial disponible</h4>
                    <p>Sus participaciones aparecerán aquí cuando se registren.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Statistics Button -->
    <div class="history-actions">
        <a href="/history/statistics" class="btn btn-primary btn-block">
            📊 Ver Estadísticas Detalladas
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh history data every 10 minutes
    setInterval(function() {
        fetch('/api/user/history')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update history display
                    updateHistoryDisplay(data.data);
                }
            })
            .catch(error => {
                console.log('Error refreshing history data:', error);
            });
    }, 600000); // 10 minutes

    function updateHistoryDisplay(data) {
        const { history, statistics } = data;
        
        // Update statistics
        if (statistics) {
            updateStatistics(statistics);
        }
        
        // Update history list
        if (history) {
            updateHistoryList(history);
        }
    }

    function updateStatistics(stats) {
        const statCards = document.querySelectorAll('.stat-card');
        if (statCards.length >= 4) {
            statCards[0].querySelector('.stat-number').textContent = stats.current_year_races || 0;
            statCards[1].querySelector('.stat-number').textContent = (stats.current_year_attendance || 0) + '%';
            statCards[2].querySelector('.stat-number').textContent = stats.total_races || 0;
            statCards[3].querySelector('.stat-number').textContent = (stats.attendance_percentage || 0) + '%';
        }
    }

    function updateHistoryList(history) {
        const historyList = document.querySelector('.history-list');
        if (!historyList) return;

        if (history.length === 0) {
            historyList.innerHTML = `
                <div class="no-history">
                    <div class="no-history-icon">📋</div>
                    <h4>No hay historial disponible</h4>
                    <p>Sus participaciones aparecerán aquí cuando se registren.</p>
                </div>
            `;
            return;
        }

        // Update existing items or create new ones
        historyList.innerHTML = history.map(race => `
            <div class="history-item">
                <div class="race-info">
                    <h4>${race.circuit || 'Circuito'}</h4>
                    <p class="race-date">${race.date || ''}</p>
                    <p class="race-location">📍 ${race.location || ''}</p>
                </div>
                <div class="race-status">
                    <div class="status-indicator status-${race.status || 'pending'}">
                        <div class="status-dot"></div>
                        <span class="status-label">${(race.status || 'Pendiente').charAt(0).toUpperCase() + (race.status || 'Pendiente').slice(1)}</span>
                    </div>
                </div>
                <div class="race-details">
                    ${race.position ? `
                        <div class="race-position">
                            <span class="position-label">Posición:</span>
                            <span class="position-value">${race.position}</span>
                        </div>
                    ` : ''}
                    ${race.points ? `
                        <div class="race-points">
                            <span class="points-label">Puntos:</span>
                            <span class="points-value">${race.points}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }
});
</script>
