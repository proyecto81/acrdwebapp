<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\ApiService;
use App\Service\CacheService;
use Cake\Http\Response;

class PromotionsController extends AppController
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
     * Promotions page
     */
    public function index(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar datos del usuario');
            return;
        }

        $promotions = $this->getPromotions();

        $this->set([
            'title' => 'Promociones',
            'user' => $user,
            'promotions' => $promotions,
        ]);
    }

    /**
     * API endpoint for promotions
     */
    public function apiPromotions(): Response
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        try {
            $promotions = $this->getPromotions();
            
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $promotions,
                ]));
        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error al obtener promociones',
                ]));
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
            // Return cached or empty promotions
            $cachedPromotions = $this->cacheService->getCachedPromotionsData();
            
            if ($cachedPromotions) {
                return $cachedPromotions;
            }

            // Return sample promotions for demo
            return [
                [
                    'id' => 1,
                    'title' => 'Oferta Especial',
                    'description' => '2x1 en próxima fecha',
                    'discount' => '50%',
                    'valid_until' => '2025-03-31',
                    'type' => 'discount',
                    'active' => true,
                ],
                [
                    'id' => 2,
                    'title' => 'Descuento VIP',
                    'description' => 'Acceso preferencial al paddock',
                    'discount' => 'Gratis',
                    'valid_until' => '2025-04-15',
                    'type' => 'benefit',
                    'active' => true,
                ],
            ];
        }
    }
}
