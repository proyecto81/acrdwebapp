<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\ApiService;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Response;

class AuthController extends AppController
{
    private AuthService $authService;
    private ApiService $apiService;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiService = new ApiService();
        $this->authService = new AuthService($this->apiService);
    }

    /**
     * Login page
     */
    public function login(): ?Response
    {
        // Redirect if already authenticated
        if ($this->authService->isAuthenticated()) {
            return $this->redirect('/');
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            try {
                $result = $this->authService->authenticate(
                    $data['dni'] ?? '',
                    $data['password'] ?? ''
                );

                $this->Flash->success('Login exitoso');
                return $this->redirect('/');
            } catch (UnauthorizedException $e) {
                $this->Flash->error('DNI o contraseña incorrectos');
            } catch (\Exception $e) {
                $this->Flash->error('Error al iniciar sesión. Intente nuevamente.');
            }
        }

        $this->set('title', 'Iniciar Sesión');
    }

    /**
     * Email validation for first-time users
     */
    public function validateEmail(): ?Response
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            try {
                $result = $this->authService->validateEmail(
                    $data['dni'] ?? '',
                    $data['email'] ?? '',
                    $data['password'] ?? ''
                );

                $this->Flash->success('Email validado correctamente');
                return $this->redirect('/');
            } catch (UnauthorizedException $e) {
                $this->Flash->error('Error en la validación del email');
            } catch (\Exception $e) {
                $this->Flash->error('Error al validar email. Intente nuevamente.');
            }
        }

        $this->set('title', 'Validar Email');
    }

    /**
     * Password recovery
     */
    public function recoverPassword(): ?Response
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            try {
                $result = $this->authService->recoverPassword($data['email'] ?? '');
                $this->Flash->success('Se ha enviado un enlace de recuperación a su email');
                return $this->redirect('/login');
            } catch (\Exception $e) {
                $this->Flash->error('Error al enviar email de recuperación');
            }
        }

        $this->set('title', 'Recuperar Contraseña');
    }

    /**
     * Logout
     */
    public function logout(): Response
    {
        $this->authService->logout();
        $this->Flash->success('Sesión cerrada correctamente');
        return $this->redirect('/login');
    }

    /**
     * API Login endpoint
     */
    public function apiLogin(): Response
    {
        $this->viewBuilder()->setOption('serialize', []);
        $this->request->allowMethod(['post']);

        $data = $this->request->getData();
        
        try {
            $result = $this->authService->authenticate(
                $data['dni'] ?? '',
                $data['password'] ?? ''
            );

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $result,
                ]));
        } catch (UnauthorizedException $e) {
            return $this->response->withStatus(401)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'DNI o contraseña incorrectos',
                ]));
        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error interno del servidor',
                ]));
        }
    }
}
