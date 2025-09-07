<?php
$this->assign('title', 'Test de Conexión API');
?>

<div class="test-container">
    <div class="test-header">
        <h1>🔗 Test de Conexión API - Acreditaciones TN</h1>
        <p>Herramientas de prueba para verificar la conectividad con la API local</p>
    </div>

    <div class="api-info">
        <h3>📋 Información de la API</h3>
        <div class="info-grid">
            <div class="info-item">
                <strong>URL Base:</strong> <?= h($apiBaseUrl) ?>
            </div>
            <div class="info-item">
                <strong>WebApp:</strong> <?= h($this->request->getUri()->getHost() . $this->request->getUri()->getPath()) ?>
            </div>
            <div class="info-item">
                <strong>Framework:</strong> CakePHP <?= h(Configure::version()) ?>
            </div>
            <div class="info-item">
                <strong>PHP:</strong> <?= h(PHP_VERSION) ?>
            </div>
        </div>
    </div>

    <div class="test-sections">
        <!-- Test de Conectividad -->
        <div class="test-section">
            <div class="test-section-header">
                <h2>📡 Conectividad Básica</h2>
                <p>Verifica que la API esté respondiendo correctamente</p>
            </div>
            <div class="test-actions">
                <?= $this->Html->link('Probar Conectividad', 
                    ['controller' => 'Test', 'action' => 'connectivity'], 
                    ['class' => 'btn btn-primary', 'target' => '_blank']
                ) ?>
            </div>
        </div>

        <!-- Test de Login -->
        <div class="test-section">
            <div class="test-section-header">
                <h2>🔐 Test de Login</h2>
                <p>Prueba el endpoint de autenticación</p>
            </div>
            <div class="test-form">
                <?= $this->Form->create(null, [
                    'url' => ['controller' => 'Test', 'action' => 'login'],
                    'class' => 'login-test-form'
                ]) ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <?= $this->Form->control('dni', [
                            'label' => 'DNI de Prueba',
                            'value' => '12345678',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control('password', [
                            'type' => 'password',
                            'label' => 'Contraseña de Prueba',
                            'value' => 'password123',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                </div>
                
                <?= $this->Form->button('Probar Login', [
                    'class' => 'btn btn-success',
                    'type' => 'submit'
                ]) ?>
                
                <?= $this->Form->end() ?>
            </div>
        </div>

        <!-- Test de Endpoints Protegidos -->
        <div class="test-section">
            <div class="test-section-header">
                <h2>🔒 Endpoints Protegidos</h2>
                <p>Prueba endpoints que requieren autenticación</p>
            </div>
            <div class="test-actions">
                <?= $this->Html->link('Probar Endpoints', 
                    ['controller' => 'Test', 'action' => 'endpoints'], 
                    ['class' => 'btn btn-warning', 'target' => '_blank']
                ) ?>
            </div>
        </div>

        <!-- Test Completo -->
        <div class="test-section">
            <div class="test-section-header">
                <h2>🚀 Test Completo</h2>
                <p>Ejecuta todas las pruebas automáticamente</p>
            </div>
            <div class="test-actions">
                <?= $this->Html->link('Ejecutar Todas las Pruebas', 
                    ['controller' => 'Test', 'action' => 'api'], 
                    ['class' => 'btn btn-info btn-large', 'target' => '_blank']
                ) ?>
            </div>
        </div>

        <!-- Acciones de Limpieza -->
        <div class="test-section">
            <div class="test-section-header">
                <h2>🧹 Limpieza</h2>
                <p>Limpiar datos de pruebas</p>
            </div>
            <div class="test-actions">
                <?= $this->Html->link('Limpiar Sesión', 
                    ['controller' => 'Test', 'action' => 'clear'], 
                    ['class' => 'btn btn-secondary', 'confirm' => '¿Limpiar sesión de pruebas?']
                ) ?>
            </div>
        </div>
    </div>

    <!-- Información de Debugging -->
    <div class="debug-section">
        <h3>🔧 Información de Debugging</h3>
        <div class="debug-grid">
            <div class="debug-item">
                <strong>Servidor:</strong> <?= h($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') ?>
            </div>
            <div class="debug-item">
                <strong>Extensiones PHP:</strong>
                <ul>
                    <li>cURL: <?= extension_loaded('curl') ? '✅' : '❌' ?></li>
                    <li>JSON: <?= extension_loaded('json') ? '✅' : '❌' ?></li>
                    <li>OpenSSL: <?= extension_loaded('openssl') ? '✅' : '❌' ?></li>
                </ul>
            </div>
            <div class="debug-item">
                <strong>Configuración:</strong>
                <ul>
                    <li>Debug: <?= Configure::read('debug') ? 'Activado' : 'Desactivado' ?></li>
                    <li>Log: <?= Configure::read('Log.debug.className') ? 'Configurado' : 'No configurado' ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Enlaces Útiles -->
    <div class="links-section">
        <h3>🔗 Enlaces Útiles</h3>
        <div class="links-grid">
            <?= $this->Html->link('API Principal', 
                'http://localhost/acreditacionestn2025/', 
                ['target' => '_blank', 'class' => 'btn btn-outline']
            ) ?>
            <?= $this->Html->link('Test HTML', 
                ['controller' => 'Test', 'action' => 'html'], 
                ['target' => '_blank', 'class' => 'btn btn-outline']
            ) ?>
            <?= $this->Html->link('Logs de Error', 
                ['controller' => 'Test', 'action' => 'logs'], 
                ['target' => '_blank', 'class' => 'btn btn-outline']
            ) ?>
        </div>
    </div>
</div>

<style>
.test-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.test-header {
    text-align: center;
    margin-bottom: 2rem;
}

.test-header h1 {
    color: #333;
    margin-bottom: 1rem;
}

.api-info, .debug-section, .links-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.api-info h3, .debug-section h3, .links-section h3 {
    color: #495057;
    margin-bottom: 1rem;
}

.info-grid, .debug-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.info-item, .debug-item {
    background: white;
    padding: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
}

.test-sections {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.test-section {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.test-section-header h2 {
    color: #007AFF;
    margin-bottom: 0.5rem;
}

.test-section-header p {
    color: #6c757d;
    margin-bottom: 1rem;
}

.test-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.test-form {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    margin-top: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 0.25rem;
    color: #333;
}

.form-control {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    font-size: 1rem;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.25rem;
    text-decoration: none;
    font-weight: 500;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary { background: #007AFF; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-warning { background: #ffc107; color: #212529; }
.btn-info { background: #17a2b8; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-outline { background: transparent; color: #007AFF; border: 1px solid #007AFF; }

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.btn-large {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.links-grid {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.debug-item ul {
    margin: 0.5rem 0 0 0;
    padding-left: 1rem;
}

.debug-item li {
    margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .test-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
