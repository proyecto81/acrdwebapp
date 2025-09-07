<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\ApiService;
use App\Service\CacheService;
use Cake\Http\Response;

class TeamController extends AppController
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
     * Team information page
     */
    public function index(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar datos del usuario');
            return;
        }

        $teamData = $this->getTeamData();

        $this->set([
            'title' => 'Mi Equipo',
            'user' => $user,
            'team' => $teamData,
        ]);
    }

    /**
     * Team members page
     */
    public function members(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar datos del usuario');
            return;
        }

        $teamData = $this->getTeamData();

        $this->set([
            'title' => 'Miembros del Equipo',
            'user' => $user,
            'team' => $teamData,
        ]);
    }

    /**
     * API endpoint for team data
     */
    public function apiTeam(): Response
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['get']);

        try {
            $teamData = $this->getTeamData();
            
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $teamData,
                ]));
        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error al obtener datos del equipo',
                ]));
        }
    }

    /**
     * Get team data from API
     */
    private function getTeamData(): array
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            return [
                'name' => 'Equipo no encontrado',
                'leader' => null,
                'members' => [],
            ];
        }

        try {
            // Try cache first
            $cachedTeamData = $this->cacheService->getCachedTeamData($user['team_id'] ?? '');
            
            if ($cachedTeamData) {
                return $cachedTeamData;
            }

            $token = $this->authService->getStoredToken();
            $response = $this->apiService->authenticatedRequest('get', '/user/team', [], $token);
            
            $teamData = $response['team'] ?? [
                'name' => 'Equipo no encontrado',
                'leader' => null,
                'members' => [],
            ];
            
            // Cache for 1 hour
            $this->cacheService->cacheTeamData($user['team_id'] ?? '', $teamData, 3600);
            
            return $teamData;
        } catch (\Exception $e) {
            // Return cached or default team data
            $cachedTeamData = $this->cacheService->getCachedTeamData($user['team_id'] ?? '');
            
            if ($cachedTeamData) {
                return $cachedTeamData;
            }

            return [
                'name' => 'Error al cargar equipo',
                'leader' => null,
                'members' => [],
            ];
        }
    }
}
