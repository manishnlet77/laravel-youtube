<?php

namespace Manishnlet77\LaravelYouTube;

use Manishnlet77\LaravelYouTube\Cache\YouTubeCacheManager;
use Manishnlet77\LaravelYouTube\Repositories\ChannelStateRepository;

class YouTubeManager
{
    public function __construct(
        protected YouTubeCacheManager $cache,
        protected ChannelStateRepository $state
    ) {}

    public function channel(): ?array
    {
        $channelId = $this->state->getChannelId();
        if (!$channelId) return null;

        return $this->cache->getChannel($channelId);
    }

    public function latest(int $limit = 10): array
    {
        $channelId = $this->state->getChannelId();
        if (!$channelId) return [];

        $data = $this->cache->getLatest($channelId) ?: [];
        return array_slice($data, 0, $limit);
    }

    public function shorts(int $limit = 10): array
    {
        $channelId = $this->state->getChannelId();
        if (!$channelId) return [];

        $data = $this->cache->getShorts($channelId) ?: [];
        return array_slice($data, 0, $limit);
    }

    public function popular(int $limit = 10): array
    {
        $channelId = $this->state->getChannelId();
        if (!$channelId) return [];

        $data = $this->cache->getPopular($channelId) ?: [];
        return array_slice($data, 0, $limit);
    }

    public function trending(int $limit = 10): array
    {
        $channelId = $this->state->getChannelId();
        if (!$channelId) return [];

        $data = $this->cache->getTrending($channelId) ?: [];
        return array_slice($data, 0, $limit);
    }

    public function playlists(): array
    {
        return []; // Future implementation
    }
}
