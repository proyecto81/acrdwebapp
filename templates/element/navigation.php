<nav class="bottom-navigation">
    <a href="/" class="nav-item <?= $this->request->getParam('controller') === 'Dashboard' ? 'active' : '' ?>">
        <div class="nav-icon">🏠</div>
        <div class="nav-label">Inicio</div>
    </a>
    <a href="/team" class="nav-item <?= $this->request->getParam('controller') === 'Team' ? 'active' : '' ?>">
        <div class="nav-icon">👥</div>
        <div class="nav-label">Equipo</div>
    </a>
    <a href="/history" class="nav-item <?= $this->request->getParam('controller') === 'History' ? 'active' : '' ?>">
        <div class="nav-icon">📋</div>
        <div class="nav-label">Historial</div>
    </a>
    <a href="/profile" class="nav-item <?= $this->request->getParam('controller') === 'Profile' ? 'active' : '' ?>">
        <div class="nav-icon">👤</div>
        <div class="nav-label">Perfil</div>
    </a>
</nav>
