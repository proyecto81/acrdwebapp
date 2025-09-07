<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\ApiService;
use App\Service\QrService;
use App\Service\CacheService;
use Cake\Http\Response;

class DashboardController extends AppController
{
    private AuthService $authService;
    private ApiService $apiService;
    private QrService $qrService;
    private CacheService $cacheService;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiService = new ApiService();
        $this->authService = new AuthService($this->apiService);
        $this->qrService = new QrService($this->apiService);
        $this->cacheService = new CacheService();
    }

    /**
     * Dashboard main page
     */
    public function index(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar datos del usuario');
            return;
        }

        // Get user status
        $status = $this->getUserStatus();
        
        // Get promotions
        $promotions = $this->getPromotions();

        $this->set([
            'title' => 'Dashboard',
            'user' => $user,
            'status' => $status,
            'promotions' => $promotions,
        ]);
    }

    /**
     * QR code page
     */
    public function qr(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar datos del usuario');
            return;
        }

        try {
            // Try to get QR from cache first
            $qrData = $this->cacheService->getCachedQrData($user['id'] ?? '');
            
            if (!$qrData) {
                // Generate new QR data
                $qrData = $this->qrService->generateUserQr($user);
                
                // Cache for 1 hour
                $this->cacheService->cacheQrData($user['id'] ?? '', $qrData, 3600);
            }

            $this->set([
                'title' => 'Mi QR',
                'user' => $user,
                'qrData' => $qrData,
            ]);
        } catch (\Exception $e) {
            $this->Flash->error('Error al generar código QR');
            $this->set([
                'title' => 'Mi QR',
                'user' => $user,
                'qrData' => null,
            ]);
        }
    }

    /**
     * API endpoint for user status
     */
    public function apiStatus(): Response
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        try {
            $status = $this->getUserStatus();
            
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $status,
                ]));
        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error al obtener estado del usuario',
                ]));
        }
    }

    /**
     * API endpoint for QR data
     */
    public function apiQr(): Response
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        try {
            $user = $this->authService->getUser();
            
            if (!$user) {
                return $this->response->withStatus(401)
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Usuario no autenticado',
                    ]));
            }

            $qrData = $this->qrService->generateUserQr($user);
            
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $qrData,
                ]));
        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error al generar código QR',
                ]));
        }
    }

    /**
     * Get user status from API
     */
    private function getUserStatus(): array
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            return [
                'status' => 'unknown',
                'message' => 'Usuario no encontrado',
                'next_event' => null,
            ];
        }

        try {
            $token = $this->authService->getStoredToken();
            $response = $this->apiService->authenticatedRequest('get', '/user/status', [], $token);
            
            return $response['status'] ?? [
                'status' => 'pending',
                'message' => 'Estado pendiente',
                'next_event' => null,
            ];
        } catch (\Exception $e) {
            // Return cached or default status
            $cachedStatus = $this->cacheService->get("status_{$user['id']}");
            
            if ($cachedStatus) {
                return $cachedStatus;
            }

            return [
                'status' => 'pending',
                'message' => 'No se pudo obtener el estado actual',
                'next_event' => null,
            ];
        }
    }

    /**
     * Get promotions from API
     */
    private function getPromotions(): array
    {
        try {
            // Try cache first
            $cachedPromotions = $this->cacheService->getCachedPromotionsData();
            
            if ($cachedPromotions) {
                return $cachedPromotions;
            }

            $token = $this->authService->getStoredToken();
            $response = $this->apiService->authenticatedRequest('get', '/user/promotions', [], $token);
            
            $promotions = $response['promotions'] ?? [];
            
            // Cache for 30 minutes
            $this->cacheService->cachePromotionsData($promotions, 1800);
            
            return $promotions;
        } catch (\Exception $e) {
            // Return empty array if API fails
            return [];
        }
    }
}
