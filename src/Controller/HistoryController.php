<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\ApiService;
use App\Service\CacheService;
use Cake\Http\Response;

class HistoryController extends AppController
{
    private AuthService $authService;
    private ApiService $apiService;
    private CacheService $cacheService;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiService = new ApiService();
        $this->authService = new AuthService($this->apiService);
        $this->cacheService = new CacheService();
    }

    /**
     * History page
     */
    public function index(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar datos del usuario');
            return;
        }

        $historyData = $this->getHistoryData();
        $statistics = $this->getStatistics();

        $this->set([
            'title' => 'Mi Historial',
            'user' => $user,
            'history' => $historyData,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Statistics page
     */
    public function statistics(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar datos del usuario');
            return;
        }

        $statistics = $this->getStatistics();

        $this->set([
            'title' => 'Estadísticas',
            'user' => $user,
            'statistics' => $statistics,
        ]);
    }

    /**
     * API endpoint for history data
     */
    public function apiHistory(): Response
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        try {
            $historyData = $this->getHistoryData();
            $statistics = $this->getStatistics();
            
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => [
                        'history' => $historyData,
                        'statistics' => $statistics,
                    ],
                ]));
        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error al obtener historial',
                ]));
        }
    }

    /**
     * Get history data from API
     */
    private function getHistoryData(): array
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            return [];
        }

        try {
            // Try cache first
            $cachedHistoryData = $this->cacheService->getCachedHistoryData($user['id'] ?? '');
            
            if ($cachedHistoryData) {
                return $cachedHistoryData;
            }

            $token = $this->authService->getStoredToken();
            $response = $this->apiService->authenticatedRequest('get', '/user/history', [], $token);
            
            $historyData = $response['history'] ?? [];
            
            // Cache for 2 hours
            $this->cacheService->cacheHistoryData($user['id'] ?? '', $historyData, 7200);
            
            return $historyData;
        } catch (\Exception $e) {
            // Return cached or empty history data
            $cachedHistoryData = $this->cacheService->getCachedHistoryData($user['id'] ?? '');
            
            if ($cachedHistoryData) {
                return $cachedHistoryData;
            }

            return [];
        }
    }

    /**
     * Get statistics from API
     */
    private function getStatistics(): array
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            return [
                'total_races' => 0,
                'attendance_percentage' => 0,
                'current_year_races' => 0,
                'current_year_attendance' => 0,
            ];
        }

        try {
            $token = $this->authService->getStoredToken();
            $response = $this->apiService->authenticatedRequest('get', '/user/statistics', [], $token);
            
            return $response['statistics'] ?? [
                'total_races' => 0,
                'attendance_percentage' => 0,
                'current_year_races' => 0,
                'current_year_attendance' => 0,
            ];
        } catch (\Exception $e) {
            // Return default statistics
            return [
                'total_races' => 0,
                'attendance_percentage' => 0,
                'current_year_races' => 0,
                'current_year_attendance' => 0,
            ];
        }
    }
}
