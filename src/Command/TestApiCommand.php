<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Http\Client;

/**
 * TestApi command
 */
class TestApiCommand extends Command
{
    private $apiBaseUrl = 'http://localhost/acreditacionestn2025/api/v1';
    private $results = [];

    /**
     * Hook method for defining this command's option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Test de conectividad con la API de Acreditaciones TN')
            ->addOption('url', [
                'short' => 'u',
                'help' => 'URL base de la API',
                'default' => $this->apiBaseUrl
            ])
            ->addOption('verbose', [
                'short' => 'v',
                'help' => 'Mostrar información detallada',
                'boolean' => true
            ])
            ->addOption('timeout', [
                'short' => 't',
                'help' => 'Timeout en segundos',
                'default' => 30
            ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->apiBaseUrl = $args->getOption('url');
        $timeout = (int)$args->getOption('timeout');
        $verbose = $args->getOption('verbose');

        $io->out('<info>🔗 Test de Conexión API - Acreditaciones TN</info>');
        $io->out('<info>URL Base: ' . $this->apiBaseUrl . '</info>');
        $io->out('');

        // Test 1: Conectividad básica
        $this->testConnectivity($io, $timeout, $verbose);

        // Test 2: Login
        $token = $this->testLogin($io, $timeout, $verbose);

        // Test 3: Endpoints protegidos (si tenemos token)
        if ($token) {
            $this->testProtectedEndpoints($io, $token, $timeout, $verbose);
        }

        // Resumen final
        $this->showSummary($io);

        return $this->getExitCode();
    }

    /**
     * Test de conectividad básica
     */
    private function testConnectivity(ConsoleIo $io, int $timeout, bool $verbose): void
    {
        $io->out('<comment>📡 Test 1: Conectividad Básica</comment>');
        
        try {
            $client = new Client();
            $startTime = microtime(true);
            
            $response = $client->get($this->apiBaseUrl . '/promotions/active', [], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'timeout' => $timeout
            ]);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            if ($response->isOk()) {
                $io->out('<success>✅ Conectividad OK (HTTP ' . $response->getStatusCode() . ') - ' . $responseTime . 'ms</success>');
                $this->results[] = ['test' => 'Conectividad', 'success' => true, 'time' => $responseTime];
                
                if ($verbose) {
                    $data = $response->getJson();
                    $io->out('<info>Respuesta: ' . json_encode($data, JSON_PRETTY_PRINT) . '</info>');
                }
            } else {
                $io->out('<error>❌ Error de conectividad (HTTP ' . $response->getStatusCode() . ')</error>');
                $this->results[] = ['test' => 'Conectividad', 'success' => false, 'time' => $responseTime];
            }
            
        } catch (\Exception $e) {
            $io->out('<error>❌ Error de conexión: ' . $e->getMessage() . '</error>');
            $this->results[] = ['test' => 'Conectividad', 'success' => false, 'time' => 0];
        }
        
        $io->out('');
    }

    /**
     * Test de login
     */
    private function testLogin(ConsoleIo $io, int $timeout, bool $verbose): ?string
    {
        $io->out('<comment>🔐 Test 2: Login</comment>');
        
        try {
            $client = new Client();
            $startTime = microtime(true);
            
            $response = $client->post($this->apiBaseUrl . '/auth/login', [
                'dni' => '12345678',
                'password' => 'password123'
            ], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'timeout' => $timeout
            ]);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            $data = $response->getJson();
            
            if ($response->isOk() && isset($data['success']) && $data['success']) {
                $token = $data['data']['token'] ?? null;
                $io->out('<success>✅ Login exitoso (HTTP ' . $response->getStatusCode() . ') - ' . $responseTime . 'ms</success>');
                $io->out('<info>Token: ' . substr($token, 0, 50) . '...</info>');
                $this->results[] = ['test' => 'Login', 'success' => true, 'time' => $responseTime];
                
                if ($verbose && isset($data['data']['user'])) {
                    $io->out('<info>Usuario: ' . json_encode($data['data']['user'], JSON_PRETTY_PRINT) . '</info>');
                }
                
                return $token;
            } else {
                $io->out('<error>❌ Error en login (HTTP ' . $response->getStatusCode() . ')</error>');
                if ($verbose) {
                    $io->out('<error>Respuesta: ' . json_encode($data, JSON_PRETTY_PRINT) . '</error>');
                }
                $this->results[] = ['test' => 'Login', 'success' => false, 'time' => $responseTime];
            }
            
        } catch (\Exception $e) {
            $io->out('<error>❌ Error de conexión en login: ' . $e->getMessage() . '</error>');
            $this->results[] = ['test' => 'Login', 'success' => false, 'time' => 0];
        }
        
        $io->out('');
        return null;
    }

    /**
     * Test de endpoints protegidos
     */
    private function testProtectedEndpoints(ConsoleIo $io, string $token, int $timeout, bool $verbose): void
    {
        $io->out('<comment>🔒 Test 3: Endpoints Protegidos</comment>');
        
        $endpoints = [
            '/user/profile' => 'Perfil de Usuario',
            '/user/status' => 'Estado de Acreditación',
            '/user/team' => 'Información del Equipo',
            '/user/history' => 'Historial de Participaciones'
        ];

        $client = new Client();
        $successCount = 0;

        foreach ($endpoints as $endpoint => $description) {
            try {
                $startTime = microtime(true);
                
                $response = $client->get($this->apiBaseUrl . $endpoint, [], [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token
                    ],
                    'timeout' => $timeout
                ]);
                
                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime) * 1000, 2);
                
                if ($response->isOk()) {
                    $io->out('<success>✅ ' . $description . ' OK (HTTP ' . $response->getStatusCode() . ') - ' . $responseTime . 'ms</success>');
                    $this->results[] = ['test' => $description, 'success' => true, 'time' => $responseTime];
                    $successCount++;
                    
                    if ($verbose) {
                        $data = $response->getJson();
                        $io->out('<info>Respuesta: ' . json_encode($data, JSON_PRETTY_PRINT) . '</info>');
                    }
                } else {
                    $io->out('<error>❌ ' . $description . ' Error (HTTP ' . $response->getStatusCode() . ')</error>');
                    $this->results[] = ['test' => $description, 'success' => false, 'time' => $responseTime];
                }
                
            } catch (\Exception $e) {
                $io->out('<error>❌ ' . $description . ' Error: ' . $e->getMessage() . '</error>');
                $this->results[] = ['test' => $description, 'success' => false, 'time' => 0];
            }
        }
        
        $io->out('<info>Endpoints protegidos: ' . $successCount . '/' . count($endpoints) . ' exitosos</info>');
        $io->out('');
    }

    /**
     * Mostrar resumen final
     */
    private function showSummary(ConsoleIo $io): void
    {
        $io->out('<comment>📊 Resumen de Resultados</comment>');
        
        $successCount = 0;
        $totalTime = 0;
        
        foreach ($this->results as $result) {
            $status = $result['success'] ? '<success>✅</success>' : '<error>❌</error>';
            $time = $result['time'] > 0 ? ' (' . $result['time'] . 'ms)' : '';
            $io->out($status . ' ' . $result['test'] . $time);
            
            if ($result['success']) {
                $successCount++;
            }
            $totalTime += $result['time'];
        }
        
        $totalCount = count($this->results);
        $io->out('');
        $io->out('<info>Resultado Final: ' . $successCount . '/' . $totalCount . ' pruebas exitosas</info>');
        $io->out('<info>Tiempo total: ' . round($totalTime, 2) . 'ms</info>');
        
        if ($successCount === $totalCount) {
            $io->out('<success>🎉 ¡Todas las pruebas pasaron! La API está funcionando correctamente.</success>');
        } else {
            $io->out('<error>⚠️ Algunas pruebas fallaron. Revisa la configuración de la API.</error>');
        }
    }

    /**
     * Obtener código de salida basado en los resultados
     */
    private function getExitCode(): int
    {
        $successCount = count(array_filter($this->results, function($r) { return $r['success']; }));
        $totalCount = count($this->results);
        
        return $successCount === $totalCount ? 0 : 1;
    }
}
