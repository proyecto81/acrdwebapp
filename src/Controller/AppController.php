<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use App\Service\AuthService;
use App\Service\ApiService;

class AppController extends Controller
{
    protected AuthService $authService;
    protected ApiService $apiService;

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

        $this->apiService = new ApiService();
        $this->authService = new AuthService($this->apiService);

        // Set default layout
        $this->viewBuilder()->setLayout('app');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Check authentication for protected routes
        $publicActions = ['login', 'validateEmail', 'recoverPassword'];
        $currentAction = $this->request->getParam('action');
        $currentController = $this->request->getParam('controller');

        if ($currentController === 'Auth' && in_array($currentAction, $publicActions)) {
            return; // Allow access to auth pages
        }

        if (!$this->authService->isAuthenticated()) {
            $this->Flash->error('Debe iniciar sesión para acceder a esta página');
            return $this->redirect('/login');
        }

        // Set user data for all views
        $user = $this->authService->getUser();
        $this->set('currentUser', $user);
    }

    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        // Set common view variables
        $this->set('isAuthenticated', $this->authService->isAuthenticated());
        
        if ($this->authService->isAuthenticated()) {
            $this->set('currentUser', $this->authService->getUser());
        }
    }
}
