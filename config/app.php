<?php

declare(strict_types=1);

use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

return [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'App' => [
        'namespace' => 'App',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'es_ES'),
        'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'America/Argentina/Buenos_Aires'),
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        'fullBaseUrl' => false,
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ],
    ],

    'Security' => [
        'salt' => env('SECURITY_SALT', 'your-security-salt-here'),
    ],

    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'encoding' => 'utf8mb4',
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,
            'quoteIdentifiers' => false,
        ],
    ],

    'Cache' => [
        'default' => [
            'className' => FileEngine::class,
            'path' => CACHE,
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        '_cake_core_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_core_',
            'path' => CACHE . 'persistent/',
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKECORE_URL', null),
        ],
        '_cake_model_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_model_',
            'path' => CACHE . 'models/',
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKEMODEL_URL', null),
        ],
        '_cake_routes_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_routes_',
            'path' => CACHE,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKEROUTES_URL', null),
        ],
    ],

    'Error' => [
        'errorLevel' => E_ALL,
        'exceptionRenderer' => 'Cake\Error\ExceptionRenderer',
        'skipLog' => [],
        'log' => true,
        'trace' => true,
    ],

    'EmailTransport' => [
        'default' => [
            'host' => 'localhost',
            'port' => 587,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    'Log' => [
        'debug' => [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => LOGS,
            'file' => 'debug',
            'url' => env('LOG_DEBUG_URL', null),
            'scopes' => false,
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error' => [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => LOGS,
            'file' => 'error',
            'url' => env('LOG_ERROR_URL', null),
            'scopes' => false,
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
    ],

    'Session' => [
        'defaults' => 'php',
    ],

    // API Configuration
    'Api' => [
        'baseUrl' => env('API_BASE_URL', 'http://localhost/acreditacionestn2025/api/v1'),
        'timeout' => 30,
        'jwt' => [
            'secret' => env('JWT_SECRET', 'your-jwt-secret-here'),
            'algorithm' => 'HS256',
            'expiration' => 3600, // 1 hour
        ],
    ],

    // PWA Configuration
    'PWA' => [
        'name' => 'Acreditaciones TN',
        'shortName' => 'Acreditaciones',
        'description' => 'Aplicación de acreditaciones para Turismo Nacional',
        'themeColor' => '#007AFF',
        'backgroundColor' => '#ffffff',
        'display' => 'standalone',
        'orientation' => 'portrait',
        'startUrl' => '/',
        'scope' => '/',
    ],
];
