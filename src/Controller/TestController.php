<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Client;
use Cake\Http\Exception\InternalErrorException;
use Cake\Log\Log;

class TestController extends AppController
{
    private $apiBaseUrl = 'http://localhost/acreditacionestn2025/api/v1';
    private $testResults = [];

    public function initialize(): void
    {
        parent::initialize();
        // No requerir autenticación para este controlador de pruebas
        $this->Authentication->addUnauthenticatedActions(['index', 'api', 'connectivity', 'login', 'endpoints']);
    }

    /**
     * Página principal de pruebas
     */
    public function index(): void
    {
        $this->set('title', 'Test de Conexión API');
        $this->set('apiBaseUrl', $this->apiBaseUrl);
    }

    /**
     * Test de conectividad básica
     */
    public function connectivity(): void
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        try {
            $http = new Client();
            $response = $http->get($this->apiBaseUrl . '/promotions/active', [], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'timeout' => 30
            ]);

            $result = [
                'success' => $response->isOk(),
                'status_code' => $response->getStatusCode(),
                'data' => $response->getJson(),
                'message' => $response->isOk() ? 'Conectividad OK' : 'Error de conectividad'
            ];

        } catch (\Exception $e) {
            Log::error('Test connectivity error: ' . $e->getMessage());
            $result = [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }

        $this->set('result', $result);
        $this->viewBuilder()->setTemplate('test_result');
    }

    /**
     * Test de login
     */
    public function login(): void
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['post']);

        $dni = $this->request->getData('dni', '12345678');
        $password = $this->request->getData('password', 'password123');

        try {
            $http = new Client();
            $response = $http->post($this->apiBaseUrl . '/auth/login', [
                'dni' => $dni,
                'password' => $password
            ], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'timeout' => 30
            ]);

            $data = $response->getJson();
            $result = [
                'success' => $response->isOk() && isset($data['success']) && $data['success'],
                'status_code' => $response->getStatusCode(),
                'data' => $data,
                'message' => $response->isOk() ? 'Login exitoso' : 'Error en login',
                'token' => $data['data']['token'] ?? null
            ];

            // Guardar token en sesión para otros tests
            if ($result['success'] && $result['token']) {
                $this->request->getSession()->write('test_token', $result['token']);
            }

        } catch (\Exception $e) {
            Log::error('Test login error: ' . $e->getMessage());
            $result = [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'token' => null
            ];
        }

        $this->set('result', $result);
        $this->viewBuilder()->setTemplate('test_result');
    }

    /**
     * Test de endpoints protegidos
     */
    public function endpoints(): void
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        $token = $this->request->getSession()->read('test_token');
        
        if (!$token) {
            $result = [
                'success' => false,
                'message' => 'No hay token de autenticación. Ejecuta primero el test de login.',
                'endpoints' => []
            ];
            $this->set('result', $result);
            $this->viewBuilder()->setTemplate('test_result');
            return;
        }

        $endpoints = [
            '/user/profile' => 'Perfil de Usuario',
            '/user/status' => 'Estado de Acreditación',
            '/user/team' => 'Información del Equipo',
            '/user/history' => 'Historial de Participaciones'
        ];

        $results = [];
        $http = new Client();

        foreach ($endpoints as $endpoint => $description) {
            try {
                $response = $http->get($this->apiBaseUrl . $endpoint, [], [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token
                    ],
                    'timeout' => 30
                ]);

                $results[] = [
                    'endpoint' => $endpoint,
                    'description' => $description,
                    'success' => $response->isOk(),
                    'status_code' => $response->getStatusCode(),
                    'data' => $response->getJson(),
                    'message' => $response->isOk() ? 'OK' : 'Error'
                ];

            } catch (\Exception $e) {
                Log::error("Test endpoint {$endpoint} error: " . $e->getMessage());
                $results[] = [
                    'endpoint' => $endpoint,
                    'description' => $description,
                    'success' => false,
                    'status_code' => 0,
                    'data' => null,
                    'message' => 'Error de conexión: ' . $e->getMessage()
                ];
            }
        }

        $successCount = count(array_filter($results, function($r) { return $r['success']; }));
        $totalCount = count($results);

        $result = [
            'success' => $successCount === $totalCount,
            'message' => "Endpoints protegidos: {$successCount}/{$totalCount} exitosos",
            'endpoints' => $results
        ];

        $this->set('result', $result);
        $this->viewBuilder()->setTemplate('test_result');
    }

    /**
     * Test completo de la API
     */
    public function api(): void
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        $tests = [];
        $http = new Client();

        // Test 1: Conectividad básica
        try {
            $response = $http->get($this->apiBaseUrl . '/promotions/active');
            $tests[] = [
                'name' => 'Conectividad Básica',
                'success' => $response->isOk(),
                'status_code' => $response->getStatusCode(),
                'message' => $response->isOk() ? 'OK' : 'Error de conectividad'
            ];
        } catch (\Exception $e) {
            $tests[] = [
                'name' => 'Conectividad Básica',
                'success' => false,
                'status_code' => 0,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }

        // Test 2: Login
        try {
            $response = $http->post($this->apiBaseUrl . '/auth/login', [
                'dni' => '12345678',
                'password' => 'password123'
            ]);
            
            $data = $response->getJson();
            $loginSuccess = $response->isOk() && isset($data['success']) && $data['success'];
            
            $tests[] = [
                'name' => 'Login',
                'success' => $loginSuccess,
                'status_code' => $response->getStatusCode(),
                'message' => $loginSuccess ? 'OK' : 'Error en login',
                'token' => $data['data']['token'] ?? null
            ];

            // Si login exitoso, probar endpoints protegidos
            if ($loginSuccess && isset($data['data']['token'])) {
                $token = $data['data']['token'];
                $protectedEndpoints = ['/user/profile', '/user/status', '/user/team', '/user/history'];
                
                foreach ($protectedEndpoints as $endpoint) {
                    try {
                        $response = $http->get($this->apiBaseUrl . $endpoint, [], [
                            'headers' => ['Authorization' => 'Bearer ' . $token]
                        ]);
                        
                        $tests[] = [
                            'name' => 'Endpoint ' . $endpoint,
                            'success' => $response->isOk(),
                            'status_code' => $response->getStatusCode(),
                            'message' => $response->isOk() ? 'OK' : 'Error'
                        ];
                    } catch (\Exception $e) {
                        $tests[] = [
                            'name' => 'Endpoint ' . $endpoint,
                            'success' => false,
                            'status_code' => 0,
                            'message' => 'Error: ' . $e->getMessage()
                        ];
                    }
                }
            }

        } catch (\Exception $e) {
            $tests[] = [
                'name' => 'Login',
                'success' => false,
                'status_code' => 0,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }

        $successCount = count(array_filter($tests, function($t) { return $t['success']; }));
        $totalCount = count($tests);

        $result = [
            'success' => $successCount === $totalCount,
            'message' => "Test completo: {$successCount}/{$totalCount} exitosos",
            'tests' => $tests,
            'summary' => [
                'total' => $totalCount,
                'successful' => $successCount,
                'failed' => $totalCount - $successCount
            ]
        ];

        $this->set('result', $result);
        $this->viewBuilder()->setTemplate('test_result');
    }

    /**
     * Limpiar sesión de pruebas
     */
    public function clear(): void
    {
        $this->request->getSession()->delete('test_token');
        $this->Flash->success('Sesión de pruebas limpiada');
        return $this->redirect(['action' => 'index']);
    }
}
