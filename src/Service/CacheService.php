<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;
use Cake\Log\Log;

class CacheService
{
    private string $defaultCacheConfig = 'default';
    private int $defaultExpiration = 3600; // 1 hour

    /**
     * Set cache value
     */
    public function set(string $key, $value, ?int $expiration = null): bool
    {
        try {
            $expiration = $expiration ?? $this->defaultExpiration;
            return Cache::write($key, $value, $this->defaultCacheConfig, $expiration);
        } catch (\Exception $e) {
            Log::error('Cache set failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get cache value
     */
    public function get(string $key, $default = null)
    {
        try {
            $value = Cache::read($key, $this->defaultCacheConfig);
            return $value !== false ? $value : $default;
        } catch (\Exception $e) {
            Log::error('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return $default;
        }
    }

    /**
     * Delete cache value
     */
    public function delete(string $key): bool
    {
        try {
            return Cache::delete($key, $this->defaultCacheConfig);
        } catch (\Exception $e) {
            Log::error('Cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if cache key exists
     */
    public function exists(string $key): bool
    {
        try {
            return Cache::read($key, $this->defaultCacheConfig) !== false;
        } catch (\Exception $e) {
            Log::error('Cache exists check failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        try {
            return Cache::clear(false, $this->defaultCacheConfig);
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Store offline data
     */
    public function storeOfflineData(string $key, array $data, ?int $expiration = null): bool
    {
        $offlineData = [
            'data' => $data,
            'timestamp' => time(),
            'expiration' => $expiration ?? $this->defaultExpiration,
        ];

        return $this->set("offline_{$key}", $offlineData, $expiration);
    }

    /**
     * Get offline data
     */
    public function getOfflineData(string $key): ?array
    {
        $offlineData = $this->get("offline_{$key}");
        
        if (!$offlineData) {
            return null;
        }

        // Check if data is still valid
        if (isset($offlineData['timestamp']) && isset($offlineData['expiration'])) {
            $age = time() - $offlineData['timestamp'];
            if ($age > $offlineData['expiration']) {
                $this->delete("offline_{$key}");
                return null;
            }
        }

        return $offlineData['data'] ?? null;
    }

    /**
     * Cache user data
     */
    public function cacheUserData(string $userId, array $userData, ?int $expiration = null): bool
    {
        return $this->set("user_{$userId}", $userData, $expiration);
    }

    /**
     * Get cached user data
     */
    public function getCachedUserData(string $userId): ?array
    {
        return $this->get("user_{$userId}");
    }

    /**
     * Cache team data
     */
    public function cacheTeamData(string $teamId, array $teamData, ?int $expiration = null): bool
    {
        return $this->set("team_{$teamId}", $teamData, $expiration);
    }

    /**
     * Get cached team data
     */
    public function getCachedTeamData(string $teamId): ?array
    {
        return $this->get("team_{$teamId}");
    }

    /**
     * Cache history data
     */
    public function cacheHistoryData(string $userId, array $historyData, ?int $expiration = null): bool
    {
        return $this->set("history_{$userId}", $historyData, $expiration);
    }

    /**
     * Get cached history data
     */
    public function getCachedHistoryData(string $userId): ?array
    {
        return $this->get("history_{$userId}");
    }

    /**
     * Cache promotions data
     */
    public function cachePromotionsData(array $promotionsData, ?int $expiration = null): bool
    {
        return $this->set('promotions', $promotionsData, $expiration);
    }

    /**
     * Get cached promotions data
     */
    public function getCachedPromotionsData(): ?array
    {
        return $this->get('promotions');
    }

    /**
     * Cache QR data
     */
    public function cacheQrData(string $userId, array $qrData, ?int $expiration = null): bool
    {
        return $this->set("qr_{$userId}", $qrData, $expiration);
    }

    /**
     * Get cached QR data
     */
    public function getCachedQrData(string $userId): ?array
    {
        return $this->get("qr_{$userId}");
    }

    /**
     * Invalidate user-related cache
     */
    public function invalidateUserCache(string $userId): void
    {
        $this->delete("user_{$userId}");
        $this->delete("team_{$userId}");
        $this->delete("history_{$userId}");
        $this->delete("qr_{$userId}");
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        try {
            $stats = Cache::getStats($this->defaultCacheConfig);
            return $stats ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
