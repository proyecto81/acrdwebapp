<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;

class QrService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Generate QR code for user
     */
    public function generateQr(?string $token = null): array
    {
        try {
            $response = $this->apiService->authenticatedRequest('get', '/user/qr', [], $token);
            return $response;
        } catch (\Exception $e) {
            Log::error('QR generation failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get user data for QR code
     */
    public function getUserQrData(?string $token = null): array
    {
        try {
            $response = $this->apiService->authenticatedRequest('get', '/user/qr-data', [], $token);
            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to get QR data', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate QR code data string
     */
    public function generateQrDataString(array $userData): string
    {
        $qrData = [
            'dni' => $userData['dni'] ?? '',
            'name' => $userData['name'] ?? '',
            'team' => $userData['team'] ?? '',
            'status' => $userData['status'] ?? '',
            'event' => $userData['event'] ?? '',
            'timestamp' => time(),
        ];

        return json_encode($qrData);
    }

    /**
     * Generate QR code image URL (using external service)
     */
    public function generateQrImageUrl(string $data, int $size = 200): string
    {
        $encodedData = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
    }

    /**
     * Generate QR code with user data
     */
    public function generateUserQr(array $userData, int $size = 200): array
    {
        $qrDataString = $this->generateQrDataString($userData);
        $qrImageUrl = $this->generateQrImageUrl($qrDataString, $size);

        return [
            'data' => $qrDataString,
            'image_url' => $qrImageUrl,
            'user_data' => $userData,
        ];
    }

    /**
     * Validate QR code data
     */
    public function validateQrData(string $qrData): array
    {
        $decoded = json_decode($qrData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid QR code data format');
        }

        $requiredFields = ['dni', 'name', 'timestamp'];
        foreach ($requiredFields as $field) {
            if (!isset($decoded[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Check if QR code is not too old (24 hours)
        $maxAge = 24 * 60 * 60; // 24 hours in seconds
        if ((time() - $decoded['timestamp']) > $maxAge) {
            throw new \InvalidArgumentException('QR code has expired');
        }

        return $decoded;
    }

    /**
     * Generate test QR data for development
     */
    public function generateTestQr(): array
    {
        $testUserData = [
            'dni' => '12345678',
            'name' => 'Juan Pérez',
            'team' => 'Racing Team',
            'status' => 'acreditado',
            'event' => 'San Nicolás',
        ];

        return $this->generateUserQr($testUserData);
    }
}
