<?php
$this->assign('title', 'Resultado de Prueba API');
?>

<div class="test-result-container">
    <div class="test-result-header">
        <h1>📊 Resultado de Prueba API</h1>
        <p>Resultado de la prueba ejecutada</p>
    </div>

    <div class="result-summary">
        <div class="result-status <?= $result['success'] ? 'success' : 'error' ?>">
            <div class="status-icon">
                <?= $result['success'] ? '✅' : '❌' ?>
            </div>
            <div class="status-content">
                <h2><?= $result['success'] ? 'Prueba Exitosa' : 'Prueba Fallida' ?></h2>
                <p><?= h($result['message']) ?></p>
            </div>
        </div>
    </div>

    <?php if (isset($result['tests'])): ?>
        <!-- Resultados de múltiples tests -->
        <div class="tests-results">
            <h3>📋 Detalle de Pruebas</h3>
            <div class="tests-grid">
                <?php foreach ($result['tests'] as $test): ?>
                    <div class="test-item <?= $test['success'] ? 'success' : 'error' ?>">
                        <div class="test-header">
                            <span class="test-icon"><?= $test['success'] ? '✅' : '❌' ?></span>
                            <span class="test-name"><?= h($test['name']) ?></span>
                            <span class="test-status"><?= h($test['status_code']) ?></span>
                        </div>
                        <div class="test-message"><?= h($test['message']) ?></div>
                        <?php if (isset($test['data']) && $test['data']): ?>
                            <div class="test-data">
                                <details>
                                    <summary>Ver datos</summary>
                                    <pre><?= h(json_encode($test['data'], JSON_PRETTY_PRINT)) ?></pre>
                                </details>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (isset($result['summary'])): ?>
            <div class="summary-stats">
                <h3>📈 Resumen Estadístico</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?= $result['summary']['total'] ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item success">
                        <div class="stat-number"><?= $result['summary']['successful'] ?></div>
                        <div class="stat-label">Exitosos</div>
                    </div>
                    <div class="stat-item error">
                        <div class="stat-number"><?= $result['summary']['failed'] ?></div>
                        <div class="stat-label">Fallidos</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif (isset($result['endpoints'])): ?>
        <!-- Resultados de endpoints -->
        <div class="endpoints-results">
            <h3>🔗 Resultados de Endpoints</h3>
            <div class="endpoints-grid">
                <?php foreach ($result['endpoints'] as $endpoint): ?>
                    <div class="endpoint-item <?= $endpoint['success'] ? 'success' : 'error' ?>">
                        <div class="endpoint-header">
                            <span class="endpoint-icon"><?= $endpoint['success'] ? '✅' : '❌' ?></span>
                            <span class="endpoint-name"><?= h($endpoint['description']) ?></span>
                            <span class="endpoint-url"><?= h($endpoint['endpoint']) ?></span>
                            <span class="endpoint-status"><?= h($endpoint['status_code']) ?></span>
                        </div>
                        <div class="endpoint-message"><?= h($endpoint['message']) ?></div>
                        <?php if (isset($endpoint['data']) && $endpoint['data']): ?>
                            <div class="endpoint-data">
                                <details>
                                    <summary>Ver respuesta</summary>
                                    <pre><?= h(json_encode($endpoint['data'], JSON_PRETTY_PRINT)) ?></pre>
                                </details>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Resultado simple -->
        <div class="simple-result">
            <h3>📄 Detalles de la Prueba</h3>
            <div class="result-details">
                <div class="detail-item">
                    <strong>Estado HTTP:</strong> <?= h($result['status_code'] ?? 'N/A') ?>
                </div>
                <div class="detail-item">
                    <strong>Mensaje:</strong> <?= h($result['message']) ?>
                </div>
                <?php if (isset($result['token']) && $result['token']): ?>
                    <div class="detail-item">
                        <strong>Token:</strong> 
                        <code><?= h(substr($result['token'], 0, 50)) ?>...</code>
                    </div>
                <?php endif; ?>
                <?php if (isset($result['data']) && $result['data']): ?>
                    <div class="detail-item">
                        <strong>Datos de Respuesta:</strong>
                        <details>
                            <summary>Ver datos completos</summary>
                            <pre><?= h(json_encode($result['data'], JSON_PRETTY_PRINT)) ?></pre>
                        </details>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="result-actions">
        <?= $this->Html->link('← Volver a Pruebas', 
            ['controller' => 'Test', 'action' => 'index'], 
            ['class' => 'btn btn-secondary']
        ) ?>
        
        <?= $this->Html->link('🔄 Ejecutar Otra Prueba', 
            ['controller' => 'Test', 'action' => 'api'], 
            ['class' => 'btn btn-primary']
        ) ?>
        
        <?= $this->Html->link('🧹 Limpiar Sesión', 
            ['controller' => 'Test', 'action' => 'clear'], 
            ['class' => 'btn btn-warning', 'confirm' => '¿Limpiar sesión de pruebas?']
        ) ?>
    </div>

    <!-- Información de Debugging -->
    <div class="debug-info">
        <h3>🔧 Información de Debugging</h3>
        <div class="debug-details">
            <div class="debug-item">
                <strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?>
            </div>
            <div class="debug-item">
                <strong>User Agent:</strong> <?= h($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') ?>
            </div>
            <div class="debug-item">
                <strong>IP:</strong> <?= h($_SERVER['REMOTE_ADDR'] ?? 'N/A') ?>
            </div>
            <div class="debug-item">
                <strong>Session ID:</strong> <?= h(session_id()) ?>
            </div>
        </div>
    </div>
</div>

<style>
.test-result-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.test-result-header {
    text-align: center;
    margin-bottom: 2rem;
}

.test-result-header h1 {
    color: #333;
    margin-bottom: 1rem;
}

.result-summary {
    margin-bottom: 2rem;
}

.result-status {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 2px solid;
}

.result-status.success {
    background: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.result-status.error {
    background: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.status-icon {
    font-size: 2rem;
    margin-right: 1rem;
}

.status-content h2 {
    margin: 0 0 0.5rem 0;
}

.status-content p {
    margin: 0;
    font-size: 1.1rem;
}

.tests-results, .endpoints-results, .simple-result {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.tests-results h3, .endpoints-results h3, .simple-result h3 {
    color: #495057;
    margin-bottom: 1rem;
}

.tests-grid, .endpoints-grid {
    display: grid;
    gap: 1rem;
}

.test-item, .endpoint-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 1rem;
    border-left: 4px solid;
}

.test-item.success, .endpoint-item.success {
    border-left-color: #28a745;
}

.test-item.error, .endpoint-item.error {
    border-left-color: #dc3545;
}

.test-header, .endpoint-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.test-icon, .endpoint-icon {
    font-size: 1.2rem;
}

.test-name, .endpoint-name {
    font-weight: 500;
    flex: 1;
}

.endpoint-url {
    font-family: monospace;
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.test-status, .endpoint-status {
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.test-message, .endpoint-message {
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.test-data, .endpoint-data {
    margin-top: 0.5rem;
}

.test-data pre, .endpoint-data pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    overflow-x: auto;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.summary-stats {
    background: #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.summary-stats h3 {
    color: #495057;
    margin-bottom: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-item {
    background: white;
    padding: 1rem;
    border-radius: 0.25rem;
    text-align: center;
    border: 2px solid #dee2e6;
}

.stat-item.success {
    border-color: #28a745;
}

.stat-item.error {
    border-color: #dc3545;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.result-details {
    background: white;
    border-radius: 0.25rem;
    padding: 1rem;
}

.detail-item {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.detail-item strong {
    color: #495057;
}

.detail-item code {
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.875rem;
}

.result-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.25rem;
    text-decoration: none;
    font-weight: 500;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary { background: #007AFF; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-warning { background: #ffc107; color: #212529; }

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.debug-info {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.debug-info h3 {
    color: #495057;
    margin-bottom: 1rem;
}

.debug-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.debug-item {
    background: white;
    padding: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
}

.debug-item strong {
    color: #495057;
}

@media (max-width: 768px) {
    .result-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
