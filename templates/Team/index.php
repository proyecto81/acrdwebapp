<?php
$this->assign('title', 'Mi Equipo');
?>

<div class="team-container">
    <div class="team-header">
        <h2><?= h($team['name'] ?? 'Mi Equipo') ?></h2>
        <p>Información del equipo y miembros</p>
    </div>

    <!-- Team Leader -->
    <?php if (isset($team['leader'])): ?>
        <div class="team-section">
            <h3>Líder del Equipo</h3>
            <div class="leader-card">
                <div class="member-avatar">
                    <div class="avatar-placeholder">
                        <?= strtoupper(substr($team['leader']['name'] ?? 'L', 0, 1)) ?>
                    </div>
                </div>
                <div class="member-info">
                    <h4><?= h($team['leader']['name'] ?? 'Líder') ?></h4>
                    <p>📞 <?= h($team['leader']['phone'] ?? 'No disponible') ?></p>
                    <p>📧 <?= h($team['leader']['email'] ?? 'No disponible') ?></p>
                </div>
                <div class="member-status">
                    <?= $this->element('status_indicator', [
                        'status' => $team['leader']['status'] ?? 'active',
                        'label' => 'Líder'
                    ]) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Team Members -->
    <div class="team-section">
        <h3>Miembros del Equipo (<?= count($team['members'] ?? []) ?>)</h3>
        <div class="members-list">
            <?php if (!empty($team['members'])): ?>
                <?php foreach ($team['members'] as $member): ?>
                    <div class="member-card">
                        <div class="member-avatar">
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($member['name'] ?? 'M', 0, 1)) ?>
                            </div>
                        </div>
                        <div class="member-info">
                            <h4><?= h($member['name'] ?? 'Miembro') ?></h4>
                            <p><?= h($member['role'] ?? 'Miembro') ?></p>
                            <?php if (isset($member['phone'])): ?>
                                <p>📞 <?= h($member['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="member-status">
                            <?= $this->element('status_indicator', [
                                'status' => $member['status'] ?? 'active',
                                'label' => ucfirst($member['status'] ?? 'Activo')
                            ]) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-members">
                    <div class="no-members-icon">👥</div>
                    <h4>No hay miembros registrados</h4>
                    <p>Los miembros del equipo aparecerán aquí cuando se registren.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Team Statistics -->
    <div class="team-section">
        <h3>Estadísticas del Equipo</h3>
        <div class="team-stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($team['members'] ?? []) + 1 ?></div>
                <div class="stat-label">Total Miembros</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($team['members'] ?? [], function($m) { return ($m['status'] ?? '') === 'active'; })) + (($team['leader']['status'] ?? '') === 'active' ? 1 : 0) ?></div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($team['members'] ?? [], function($m) { return ($m['status'] ?? '') === 'pending'; })) ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh team data every 5 minutes
    setInterval(function() {
        fetch('/api/user/team')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update team display
                    updateTeamDisplay(data.data);
                }
            })
            .catch(error => {
                console.log('Error refreshing team data:', error);
            });
    }, 300000); // 5 minutes

    function updateTeamDisplay(teamData) {
        // Update team name
        const teamHeader = document.querySelector('.team-header h2');
        if (teamHeader) {
            teamHeader.textContent = teamData.name || 'Mi Equipo';
        }

        // Update member count
        const memberCount = document.querySelector('.team-section h3');
        if (memberCount) {
            const count = teamData.members ? teamData.members.length : 0;
            memberCount.textContent = `Miembros del Equipo (${count})`;
        }

        // Update statistics
        updateTeamStats(teamData);
    }

    function updateTeamStats(teamData) {
        const members = teamData.members || [];
        const leader = teamData.leader || {};
        
        const totalMembers = members.length + 1;
        const activeMembers = members.filter(m => m.status === 'active').length + (leader.status === 'active' ? 1 : 0);
        const pendingMembers = members.filter(m => m.status === 'pending').length;

        const statCards = document.querySelectorAll('.stat-card');
        if (statCards.length >= 3) {
            statCards[0].querySelector('.stat-number').textContent = totalMembers;
            statCards[1].querySelector('.stat-number').textContent = activeMembers;
            statCards[2].querySelector('.stat-number').textContent = pendingMembers;
        }
    }
});
</script>
