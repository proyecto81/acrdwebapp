<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Log\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private ApiService $apiService;
    private string $jwtSecret;
    private string $jwtAlgorithm;
    private int $jwtExpiration;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
        $this->jwtSecret = Configure::read('Api.jwt.secret');
        $this->jwtAlgorithm = Configure::read('Api.jwt.algorithm');
        $this->jwtExpiration = Configure::read('Api.jwt.expiration');
    }

    /**
     * Authenticate user with DNI and password
     */
    public function authenticate(string $dni, string $password): array
    {
        try {
            $response = $this->apiService->post('/auth/login', [
                'dni' => $dni,
                'password' => $password,
            ]);

            if (isset($response['token'])) {
                $this->storeToken($response['token']);
                return $response;
            }

            throw new UnauthorizedException('Invalid credentials');
        } catch (\Exception $e) {
            Log::error('Authentication failed', [
                'dni' => $dni,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate JWT token
     */
    public function validateToken(?string $token = null): bool
    {
        if (!$token) {
            $token = $this->getStoredToken();
        }

        if (!$token) {
            return false;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
            return true;
        } catch (\Exception $e) {
            Log::warning('Token validation failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken(): ?string
    {
        $currentToken = $this->getStoredToken();
        
        if (!$currentToken) {
            return null;
        }

        try {
            $response = $this->apiService->authenticatedRequest('post', '/auth/refresh', [], $currentToken);
            
            if (isset($response['token'])) {
                $this->storeToken($response['token']);
                return $response['token'];
            }
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Get current user data
     */
    public function getUser(): ?array
    {
        $token = $this->getStoredToken();
        
        if (!$token || !$this->validateToken($token)) {
            return null;
        }

        try {
            $response = $this->apiService->authenticatedRequest('get', '/user/profile', [], $token);
            return $response['user'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get user data', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->validateToken();
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $token = $this->getStoredToken();
        
        if ($token) {
            try {
                $this->apiService->authenticatedRequest('post', '/auth/logout', [], $token);
            } catch (\Exception $e) {
                Log::warning('Logout API call failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->clearStoredToken();
    }

    /**
     * Store JWT token in session
     */
    private function storeToken(string $token): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['jwt_token'] = $token;
    }

    /**
     * Get stored JWT token
     */
    private function getStoredToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['jwt_token'] ?? null;
    }

    /**
     * Clear stored JWT token
     */
    private function clearStoredToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['jwt_token']);
    }

    /**
     * Generate JWT token for testing
     */
    public function generateTestToken(array $payload = []): string
    {
        $defaultPayload = [
            'sub' => 'test_user',
            'dni' => '12345678',
            'name' => 'Test User',
            'iat' => time(),
            'exp' => time() + $this->jwtExpiration,
        ];

        $payload = array_merge($defaultPayload, $payload);

        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }

    /**
     * Validate email for first-time users
     */
    public function validateEmail(string $dni, string $email, string $password): array
    {
        try {
            $response = $this->apiService->post('/auth/validate-email', [
                'dni' => $dni,
                'email' => $email,
                'password' => $password,
            ]);

            if (isset($response['token'])) {
                $this->storeToken($response['token']);
                return $response;
            }

            throw new UnauthorizedException('Email validation failed');
        } catch (\Exception $e) {
            Log::error('Email validation failed', [
                'dni' => $dni,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Recover password via email
     */
    public function recoverPassword(string $email): array
    {
        try {
            return $this->apiService->post('/auth/recover-password', [
                'email' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error('Password recovery failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
