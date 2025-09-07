<?php
declare(strict_types=1);

/**
 * Configuración para pruebas de API
 * 
 * Este archivo contiene la configuración específica para las pruebas
 * de conectividad con la API externa
 */

return [
    'ApiTest' => [
        // URL base de la API para pruebas
        'baseUrl' => env('API_TEST_BASE_URL', 'http://localhost/acreditacionestn2025/api/v1'),
        
        // Credenciales de prueba
        'testCredentials' => [
            'dni' => env('API_TEST_DNI', '12345678'),
            'password' => env('API_TEST_PASSWORD', 'password123')
        ],
        
        // Configuración de timeouts
        'timeouts' => [
            'connectivity' => 30,  // segundos
            'login' => 30,         // segundos
            'endpoints' => 30      // segundos
        ],
        
        // Endpoints a probar
        'endpoints' => [
            'public' => [
                '/promotions/active' => 'Promociones Activas'
            ],
            'auth' => [
                '/auth/login' => 'Login de Usuario'
            ],
            'protected' => [
                '/user/profile' => 'Perfil de Usuario',
                '/user/status' => 'Estado de Acreditación',
                '/user/team' => 'Información del Equipo',
                '/user/history' => 'Historial de Participaciones'
            ]
        ],
        
        // Configuración de headers
        'headers' => [
            'default' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Acreditaciones-TN-WebApp/1.0'
            ]
        ],
        
        // Configuración de logging
        'logging' => [
            'enabled' => true,
            'level' => 'info',
            'file' => 'api_test.log'
        ],
        
        // Configuración de cache para pruebas
        'cache' => [
            'enabled' => false,  // Deshabilitado para pruebas
            'duration' => '+1 hour'
        ],
        
        // Configuración de reintentos
        'retry' => [
            'enabled' => true,
            'maxAttempts' => 3,
            'delay' => 1000  // milisegundos
        ],
        
        // Configuración de validación de respuestas
        'validation' => [
            'requireJson' => true,
            'requireSuccessField' => true,
            'timeoutWarning' => 5000  // milisegundos
        ]
    ]
];
