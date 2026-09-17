<?php

namespace Manishnlet77\LaravelYouTube\Cache;

use Illuminate\Support\Facades\Cache;

class YouTubeCacheManager
{
    protected string $prefix = 'youtube:v1';
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('youtube.cache.enabled', true);
    }

    protected function getKey(string $suffix): string
    {
        return $this->prefix . ':' . $suffix;
    }

    protected function remember(string $keySuffix, int $ttl, \Closure $callback)
    {
        if (!$this->enabled) {
            return $callback();
        }

        $key = $this->getKey($keySuffix);
        
        // Implementing a simple lock to prevent cache stampedes
        $lock = Cache::lock($key . ':lock', 10);

        try {
            if ($lock->get()) {
                return Cache::remember($key, $ttl, $callback);
            }
            
            // If we can't get the lock, someone else is updating it.
            // Wait a moment and try to fetch from cache, otherwise just execute.
            sleep(1);
            if (Cache::has($key)) {
                return Cache::get($key);
            }
            
            return $callback();
        } finally {
            $lock->release();
        }
    }

    public function getChannel(string $channelId)
    {
        if (!$this->enabled) return null;
        return Cache::get($this->getKey("channel:{$channelId}"));
    }

    public function cacheChannel(string $channelId, array $data)
    {
        $ttl = config('youtube.cache.channel_ttl', 86400);
        Cache::put($this->getKey("channel:{$channelId}"), $data, $ttl);
    }

    public function getVideo(string $videoId)
    {
        if (!$this->enabled) return null;
        return Cache::get($this->getKey("video:{$videoId}"));
    }

    public function cacheVideo(string $videoId, array $data)
    {
        $ttl = config('youtube.cache.video_ttl', 3600);
        Cache::put($this->getKey("video:{$videoId}"), $data, $ttl);
    }

    public function cacheLatest(string $channelId, array $data)
    {
        $ttl = config('youtube.cache.latest_ttl', 900);
        Cache::put($this->getKey("channel:{$channelId}:latest"), $data, $ttl);
    }

    public function getLatest(string $channelId)
    {
        if (!$this->enabled) return null;
        return Cache::get($this->getKey("channel:{$channelId}:latest"));
    }

    public function cacheShorts(string $channelId, array $data)
    {
        $ttl = config('youtube.cache.shorts_ttl', 1800);
        Cache::put($this->getKey("channel:{$channelId}:shorts"), $data, $ttl);
    }

    public function getShorts(string $channelId)
    {
        if (!$this->enabled) return null;
        return Cache::get($this->getKey("channel:{$channelId}:shorts"));
    }

    public function cachePopular(string $channelId, array $data)
    {
        $ttl = config('youtube.cache.popular_ttl', 1800);
        Cache::put($this->getKey("channel:{$channelId}:popular"), $data, $ttl);
    }

    public function getPopular(string $channelId)
    {
        if (!$this->enabled) return null;
        return Cache::get($this->getKey("channel:{$channelId}:popular"));
    }

    public function cacheTrending(string $channelId, array $data)
    {
        $ttl = config('youtube.cache.trending_ttl', 900);
        Cache::put($this->getKey("channel:{$channelId}:trending"), $data, $ttl);
    }

    public function getTrending(string $channelId)
    {
        if (!$this->enabled) return null;
        return Cache::get($this->getKey("channel:{$channelId}:trending"));
    }

    public function forgetChannel(string $channelId): void
    {
        Cache::forget($this->getKey("channel:{$channelId}"));
    }

    public function forgetVideo(string $videoId): void
    {
        Cache::forget($this->getKey("video:{$videoId}"));
    }

    public function flushAll(): void
    {
        // Actually clearing all keys starting with youtube:v1: would require Redis.
        // For file cache, we can't easily iterate keys.
        // We will just document this limitation or use tagging if supported.
        // For now, if the driver supports tags:
        // Cache::tags(['youtube'])->flush();
    }
}
