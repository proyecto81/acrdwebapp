<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\ApiService;
use App\Service\CacheService;
use Cake\Http\Response;

class ProfileController extends AppController
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
     * View profile
     */
    public function index(): void
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar perfil del usuario');
            return;
        }

        $this->set([
            'title' => 'Mi Perfil',
            'user' => $user,
        ]);
    }

    /**
     * Edit profile
     */
    public function edit(): ?Response
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar perfil del usuario');
            return $this->redirect('/profile');
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            try {
                $token = $this->authService->getStoredToken();
                $response = $this->apiService->authenticatedRequest('put', '/user/profile', $data, $token);
                
                $this->Flash->success('Perfil actualizado correctamente');
                
                // Clear user cache
                $this->cacheService->invalidateUserCache($user['id'] ?? '');
                
                return $this->redirect('/profile');
            } catch (\Exception $e) {
                $this->Flash->error('Error al actualizar perfil');
            }
        }

        $this->set([
            'title' => 'Editar Perfil',
            'user' => $user,
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(): ?Response
    {
        $user = $this->authService->getUser();
        
        if (!$user) {
            $this->Flash->error('Error al cargar perfil del usuario');
            return $this->redirect('/profile');
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Validate passwords match
            if ($data['new_password'] !== $data['confirm_password']) {
                $this->Flash->error('Las contraseñas no coinciden');
                return null;
            }

            try {
                $token = $this->authService->getStoredToken();
                $response = $this->apiService->authenticatedRequest('put', '/user/change-password', [
                    'current_password' => $data['current_password'] ?? '',
                    'new_password' => $data['new_password'] ?? '',
                ], $token);
                
                $this->Flash->success('Contraseña cambiada correctamente');
                return $this->redirect('/profile');
            } catch (\Exception $e) {
                $this->Flash->error('Error al cambiar contraseña');
            }
        }

        $this->set([
            'title' => 'Cambiar Contraseña',
            'user' => $user,
        ]);
    }
}
