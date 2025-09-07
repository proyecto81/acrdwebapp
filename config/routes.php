<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder) {
        // Auth routes
        $builder->connect('/login', ['controller' => 'Auth', 'action' => 'login']);
        $builder->connect('/validate-email', ['controller' => 'Auth', 'action' => 'validateEmail']);
        $builder->connect('/recover-password', ['controller' => 'Auth', 'action' => 'recoverPassword']);
        $builder->connect('/logout', ['controller' => 'Auth', 'action' => 'logout']);
        
        // Protected routes
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        $builder->connect('/qr', ['controller' => 'Dashboard', 'action' => 'qr']);
        $builder->connect('/profile', ['controller' => 'Profile', 'action' => 'index']);
        $builder->connect('/profile/edit', ['controller' => 'Profile', 'action' => 'edit']);
        $builder->connect('/profile/change-password', ['controller' => 'Profile', 'action' => 'changePassword']);
        $builder->connect('/team', ['controller' => 'Team', 'action' => 'index']);
        $builder->connect('/team/members', ['controller' => 'Team', 'action' => 'members']);
        $builder->connect('/history', ['controller' => 'History', 'action' => 'index']);
        $builder->connect('/history/statistics', ['controller' => 'History', 'action' => 'statistics']);
        $builder->connect('/promotions', ['controller' => 'Promotions', 'action' => 'index']);
        
        // Test routes (for development)
        $builder->connect('/test', ['controller' => 'Test', 'action' => 'index']);
        $builder->connect('/test/connectivity', ['controller' => 'Test', 'action' => 'connectivity']);
        $builder->connect('/test/login', ['controller' => 'Test', 'action' => 'login']);
        $builder->connect('/test/endpoints', ['controller' => 'Test', 'action' => 'endpoints']);
        $builder->connect('/test/api', ['controller' => 'Test', 'action' => 'api']);
        $builder->connect('/test/clear', ['controller' => 'Test', 'action' => 'clear']);
        
        // API routes for AJAX calls
        $builder->connect('/api/auth/login', ['controller' => 'Auth', 'action' => 'apiLogin']);
        $builder->connect('/api/user/status', ['controller' => 'Dashboard', 'action' => 'apiStatus']);
        $builder->connect('/api/user/qr', ['controller' => 'Dashboard', 'action' => 'apiQr']);
        $builder->connect('/api/user/team', ['controller' => 'Team', 'action' => 'apiTeam']);
        $builder->connect('/api/user/history', ['controller' => 'History', 'action' => 'apiHistory']);
        $builder->connect('/api/user/promotions', ['controller' => 'Promotions', 'action' => 'apiPromotions']);
    });

    $routes->fallbacks();
};
