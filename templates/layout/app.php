<!DOCTYPE html>
<html lang="es">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->fetch('title') ?> - Acreditaciones TN</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Aplicación de acreditaciones para Turismo Nacional">
    <meta name="theme-color" content="#007AFF">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Acreditaciones TN">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/img/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/icons/icon-16x16.png">
    <link rel="apple-touch-icon" href="/img/icons/icon-192x192.png">
    <link rel="manifest" href="/manifest.json">
    
    <!-- CSS -->
    <?= $this->Html->css('app.css') ?>
    <?= $this->Html->css('components.css') ?>
    <?= $this->Html->css('pwa.css') ?>
    
    <!-- JavaScript -->
    <?= $this->Html->script('app.js') ?>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <div id="app">
        <!-- Header -->
        <?php if ($isAuthenticated ?? false): ?>
            <header class="app-header">
                <div class="header-content">
                    <h1><?= $this->fetch('title') ?></h1>
                    <?php if (isset($currentUser)): ?>
                        <div class="user-info">
                            <span class="user-name"><?= h($currentUser['name'] ?? 'Usuario') ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </header>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="app-main">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </main>

        <!-- Bottom Navigation -->
        <?php if ($isAuthenticated ?? false): ?>
            <?= $this->element('navigation') ?>
        <?php endif; ?>
    </div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>

    <?= $this->fetch('scriptBottom') ?>
</body>
</html>
