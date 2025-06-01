<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key, callable $callback, int $expiresAfter = 3600): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $expiresAfter) {
            $item->expiresAfter($expiresAfter);
            return $callback();
        });
    }

    public function set(string $key, mixed $value, int $expiresAfter = 3600): void
    {
        $this->cache->get($key, function (ItemInterface $item) use ($value, $expiresAfter) {
            $item->expiresAfter($expiresAfter);
            return $value;
        });
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function getStatistics(): array
    {
        return $this->get('app_statistics', function () {
            return [
                'articles_count' => 0,
                'podcasts_count' => 0,
                'archives_count' => 0,
                'enseignements_count' => 0,
                'users_count' => 0,
                'last_updated' => new \DateTime()
            ];
        }, 1800); // Cache pour 30 minutes
    }

    public function cacheArticles(array $articles): void
    {
        $this->set('latest_articles', $articles, 900); // Cache pour 15 minutes
    }

    public function getCachedArticles(): ?array
    {
        try {
            return $this->get('latest_articles', function () {
                return null;
            });
        } catch (\Exception $e) {
            return null;
        }
    }

    public function cachePodcasts(array $podcasts): void
    {
        $this->set('latest_podcasts', $podcasts, 900);
    }

    public function getCachedPodcasts(): ?array
    {
        try {
            return $this->get('latest_podcasts', function () {
                return null;
            });
        } catch (\Exception $e) {
            return null;
        }
    }

    public function cacheSearchResults(string $query, array $results): void
    {
        $cacheKey = 'search_' . md5($query);
        $this->set($cacheKey, $results, 300); // Cache pour 5 minutes
    }

    public function getCachedSearchResults(string $query): ?array
    {
        $cacheKey = 'search_' . md5($query);
        try {
            return $this->get($cacheKey, function () {
                return null;
            });
        } catch (\Exception $e) {
            return null;
        }
    }

    public function invalidateContentCache(): void
    {
        $this->delete('latest_articles');
        $this->delete('latest_podcasts');
        $this->delete('app_statistics');
    }

    public function warmUpCache(): void
    {
        // Pré-charger le cache avec des données couramment utilisées
        $this->getStatistics();
    }

    public function getCacheKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(serialize($params));
        }
        return $key;
    }
}
