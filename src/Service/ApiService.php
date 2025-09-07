<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Exception\InternalErrorException;
use Cake\Log\Log;

class ApiService
{
    private Client $httpClient;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->baseUrl = Configure::read('Api.baseUrl');
        $this->timeout = Configure::read('Api.timeout');
    }

    /**
     * Make HTTP request to API
     */
    public function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        try {
            $options = [
                'timeout' => $this->timeout,
                'headers' => $headers,
            ];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            $response = $this->httpClient->{$method}($url, $data, $options);
            
            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('API Request failed: ' . $e->getMessage(), [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data,
            ]);
            
            throw new InternalErrorException('Error communicating with API: ' . $e->getMessage());
        }
    }

    /**
     * GET request
     */
    public function get(string $endpoint, array $headers = []): array
    {
        return $this->makeRequest('get', $endpoint, [], $headers);
    }

    /**
     * POST request
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('post', $endpoint, $data, $headers);
    }

    /**
     * PUT request
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('put', $endpoint, $data, $headers);
    }

    /**
     * DELETE request
     */
    public function delete(string $endpoint, array $headers = []): array
    {
        return $this->makeRequest('delete', $endpoint, [], $headers);
    }

    /**
     * Handle API response
     */
    private function handleResponse($response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getStringBody();
        
        if ($statusCode >= 400) {
            $this->handleErrors($statusCode, $body);
        }

        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InternalErrorException('Invalid JSON response from API');
        }

        return $data ?? [];
    }

    /**
     * Handle API errors
     */
    private function handleErrors(int $statusCode, string $body): void
    {
        $errorData = json_decode($body, true);
        $message = $errorData['message'] ?? 'Unknown API error';
        
        Log::error('API Error', [
            'status_code' => $statusCode,
            'body' => $body,
        ]);

        switch ($statusCode) {
            case 401:
                throw new \Cake\Http\Exception\UnauthorizedException($message);
            case 403:
                throw new \Cake\Http\Exception\ForbiddenException($message);
            case 404:
                throw new \Cake\Http\Exception\NotFoundException($message);
            case 422:
                throw new \Cake\Http\Exception\UnprocessableEntityException($message);
            default:
                throw new InternalErrorException($message);
        }
    }

    /**
     * Make authenticated request with JWT token
     */
    public function authenticatedRequest(string $method, string $endpoint, array $data = [], ?string $token = null): array
    {
        $headers = [];
        
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $this->makeRequest($method, $endpoint, $data, $headers);
    }
}
