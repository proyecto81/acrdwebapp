<?php
declare(strict_types=1);

namespace App\Test\TestCase\Integration;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Http\Client;

/**
 * API Connection Integration Test
 * 
 * Este test verifica la conectividad real con la API externa
 * Solo se ejecuta si la API está disponible
 */
class ApiConnectionTest extends TestCase
{
    use IntegrationTestTrait;

    private $apiBaseUrl = 'http://localhost/acreditacionestn2025/api/v1';
    private $isApiAvailable = false;

    /**
     * setUp method
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // Verificar si la API está disponible
        $this->checkApiAvailability();
    }

    /**
     * tearDown method
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Verificar si la API está disponible
     */
    private function checkApiAvailability(): void
    {
        try {
            $client = new Client();
            $response = $client->get($this->apiBaseUrl . '/promotions/active', [], [
                'timeout' => 5,
                'headers' => ['Accept' => 'application/json']
            ]);
            
            $this->isApiAvailable = $response->isOk();
        } catch (\Exception $e) {
            $this->isApiAvailable = false;
        }
    }

    /**
     * Skip test si la API no está disponible
     */
    private function skipIfApiNotAvailable(): void
    {
        if (!$this->isApiAvailable) {
            $this->markTestSkipped('API no está disponible en ' . $this->apiBaseUrl);
        }
    }

    /**
     * Test de conectividad básica con la API
     */
    public function testApiConnectivity(): void
    {
        $this->skipIfApiNotAvailable();
        
        $client = new Client();
        $response = $client->get($this->apiBaseUrl . '/promotions/active', [], [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'timeout' => 30
        ]);

        $this->assertTrue($response->isOk(), 'La API debe responder correctamente');
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        $data = $response->getJson();
        $this->assertIsArray($data, 'La respuesta debe ser un array JSON válido');
    }

    /**
     * Test de login con la API
     */
    public function testApiLogin(): void
    {
        $this->skipIfApiNotAvailable();
        
        $client = new Client();
        $response = $client->post($this->apiBaseUrl . '/auth/login', [
            'dni' => '12345678',
            'password' => 'password123'
        ], [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'timeout' => 30
        ]);

        $this->assertTrue($response->isOk(), 'El login debe funcionar correctamente');
        
        $data = $response->getJson();
        $this->assertIsArray($data, 'La respuesta debe ser un array JSON válido');
        
        if (isset($data['success']) && $data['success']) {
            $this->assertArrayHasKey('data', $data);
            $this->assertArrayHasKey('token', $data['data']);
            $this->assertArrayHasKey('user', $data['data']);
            
            return $data['data']['token'];
        } else {
            $this->markTestSkipped('Login falló - verificar credenciales de prueba');
        }
    }

    /**
     * Test de endpoints protegidos
     */
    public function testProtectedEndpoints(): void
    {
        $this->skipIfApiNotAvailable();
        
        // Primero obtener un token
        $token = $this->testApiLogin();
        
        if (!$token) {
            $this->markTestSkipped('No se pudo obtener token de autenticación');
        }

        $client = new Client();
        $endpoints = [
            '/user/profile' => 'Perfil de Usuario',
            '/user/status' => 'Estado de Acreditación',
            '/user/team' => 'Información del Equipo',
            '/user/history' => 'Historial de Participaciones'
        ];

        foreach ($endpoints as $endpoint => $description) {
            $response = $client->get($this->apiBaseUrl . $endpoint, [], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ],
                'timeout' => 30
            ]);

            $this->assertTrue(
                $response->isOk(), 
                "El endpoint {$description} ({$endpoint}) debe responder correctamente"
            );
            
            $data = $response->getJson();
            $this->assertIsArray($data, "La respuesta de {$description} debe ser un array JSON válido");
        }
    }

    /**
     * Test de manejo de errores
     */
    public function testErrorHandling(): void
    {
        $this->skipIfApiNotAvailable();
        
        $client = new Client();
        
        // Test de endpoint inexistente
        $response = $client->get($this->apiBaseUrl . '/nonexistent', [], [
            'headers' => ['Accept' => 'application/json'],
            'timeout' => 30
        ]);
        
        $this->assertEquals(404, $response->getStatusCode(), 'Endpoint inexistente debe devolver 404');
        
        // Test de login con credenciales incorrectas
        $response = $client->post($this->apiBaseUrl . '/auth/login', [
            'dni' => 'invalid',
            'password' => 'invalid'
        ], [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'timeout' => 30
        ]);
        
        $this->assertTrue(
            in_array($response->getStatusCode(), [400, 401, 422]), 
            'Login con credenciales incorrectas debe devolver error'
        );
    }

    /**
     * Test de performance básico
     */
    public function testApiPerformance(): void
    {
        $this->skipIfApiNotAvailable();
        
        $client = new Client();
        $startTime = microtime(true);
        
        $response = $client->get($this->apiBaseUrl . '/promotions/active', [], [
            'headers' => ['Accept' => 'application/json'],
            'timeout' => 30
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // en milisegundos
        
        $this->assertTrue($response->isOk(), 'La API debe responder correctamente');
        $this->assertLessThan(5000, $responseTime, 'La respuesta debe ser menor a 5 segundos');
        
        echo "\nTiempo de respuesta: " . round($responseTime, 2) . "ms";
    }

    /**
     * Test de estructura de respuesta
     */
    public function testResponseStructure(): void
    {
        $this->skipIfApiNotAvailable();
        
        $client = new Client();
        $response = $client->get($this->apiBaseUrl . '/promotions/active', [], [
            'headers' => ['Accept' => 'application/json'],
            'timeout' => 30
        ]);

        $this->assertTrue($response->isOk());
        
        $data = $response->getJson();
        $this->assertIsArray($data);
        
        // Verificar headers importantes
        $this->assertNotEmpty($response->getHeaderLine('Content-Type'));
        $this->assertStringContains('application/json', $response->getHeaderLine('Content-Type'));
    }
}
