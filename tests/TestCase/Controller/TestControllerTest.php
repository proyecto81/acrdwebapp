<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\TestController Test Case
 */
class TestControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [];

    /**
     * Test index method
     */
    public function testIndex(): void
    {
        $this->get('/test');
        $this->assertResponseOk();
        $this->assertResponseContains('Test de Conexión API');
    }

    /**
     * Test connectivity method
     */
    public function testConnectivity(): void
    {
        $this->get('/test/connectivity');
        $this->assertResponseOk();
        
        // Verificar que la respuesta sea JSON válida
        $response = $this->_response->getBody()->getContents();
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
    }

    /**
     * Test login method
     */
    public function testLogin(): void
    {
        $this->post('/test/login', [
            'dni' => '12345678',
            'password' => 'password123'
        ]);
        
        $this->assertResponseOk();
        
        // Verificar que la respuesta sea JSON válida
        $response = $this->_response->getBody()->getContents();
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
    }

    /**
     * Test endpoints method without token
     */
    public function testEndpointsWithoutToken(): void
    {
        $this->get('/test/endpoints');
        $this->assertResponseOk();
        
        $response = $this->_response->getBody()->getContents();
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertStringContains('No hay token', $data['message']);
    }

    /**
     * Test api method
     */
    public function testApi(): void
    {
        $this->get('/test/api');
        $this->assertResponseOk();
        
        $response = $this->_response->getBody()->getContents();
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('tests', $data);
        $this->assertIsArray($data['tests']);
    }

    /**
     * Test clear method
     */
    public function testClear(): void
    {
        // Simular que hay un token en la sesión
        $this->session(['test_token' => 'fake_token']);
        
        $this->get('/test/clear');
        $this->assertRedirect(['controller' => 'Test', 'action' => 'index']);
        
        // Verificar que el token fue eliminado
        $this->assertSession(null, 'test_token');
    }
}
