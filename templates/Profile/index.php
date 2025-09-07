<?php
$this->assign('title', 'Mi Perfil');
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <div class="avatar-placeholder">
                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
        </div>
        <div class="profile-info">
            <h2><?= h($user['name'] ?? 'Usuario') ?></h2>
            <p>DNI: <?= h($user['dni'] ?? '') ?></p>
        </div>
    </div>

    <div class="profile-sections">
        <div class="profile-section">
            <h3>Información Personal</h3>
            <div class="info-list">
                <div class="info-item">
                    <div class="info-label">📧 Email</div>
                    <div class="info-value"><?= h($user['email'] ?? 'No especificado') ?></div>
                    <a href="/profile/edit" class="info-action">▶</a>
                </div>
                <div class="info-item">
                    <div class="info-label">📱 Teléfono</div>
                    <div class="info-value"><?= h($user['phone'] ?? 'No especificado') ?></div>
                    <a href="/profile/edit" class="info-action">▶</a>
                </div>
                <div class="info-item">
                    <div class="info-label">🏁 Equipo</div>
                    <div class="info-value"><?= h($user['team'] ?? 'Sin equipo') ?></div>
                    <a href="/team" class="info-action">▶</a>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h3>Configuración</h3>
            <div class="info-list">
                <div class="info-item">
                    <div class="info-label">🔒 Cambiar Contraseña</div>
                    <div class="info-value"></div>
                    <a href="/profile/change-password" class="info-action">▶</a>
                </div>
                <div class="info-item">
                    <div class="info-label">🔔 Notificaciones</div>
                    <div class="info-value"><?= $user['notifications'] ?? true ? 'Activadas' : 'Desactivadas' ?></div>
                    <a href="/profile/edit" class="info-action">▶</a>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h3>Información de Cuenta</h3>
            <div class="info-list">
                <div class="info-item">
                    <div class="info-label">📅 Miembro desde</div>
                    <div class="info-value"><?= h($user['created_at'] ?? 'No disponible') ?></div>
                    <div class="info-action"></div>
                </div>
                <div class="info-item">
                    <div class="info-label">🏆 Participaciones</div>
                    <div class="info-value"><?= h($user['total_races'] ?? '0') ?></div>
                    <a href="/history" class="info-action">▶</a>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-actions">
        <a href="/profile/edit" class="btn btn-primary btn-block">
            ✏️ Editar Perfil
        </a>
        <a href="/logout" class="btn btn-danger btn-block" onclick="return confirm('¿Está seguro que desea cerrar sesión?')">
            🚪 Cerrar Sesión
        </a>
    </div>
</div>
