<?php

namespace Manishnlet77\LaravelYouTube\Services;

use Manishnlet77\LaravelYouTube\Http\YouTubeApiClient;
use Manishnlet77\LaravelYouTube\Cache\YouTubeCacheManager;
use Manishnlet77\LaravelYouTube\Repositories\ChannelStateRepository;
use Manishnlet77\LaravelYouTube\Exceptions\YouTubeApiException;

class SyncService
{
    public function __construct(
        protected YouTubeApiClient $client,
        protected YouTubeCacheManager $cache,
        protected ChannelStateRepository $state,
        protected ShortsClassifier $shortsClassifier
    ) {}

    public function sync(bool $full = false): array
    {
        $channelId = $this->state->getChannelId();
        if (!$channelId) {
            throw new YouTubeApiException('Channel not configured. Run setup first.');
        }

        // 1. Refresh Channel Details
        $channelData = $this->client->getChannelDetails($channelId);
        if (!$channelData) {
            throw new YouTubeApiException('Could not fetch channel details.');
        }

        $uploadsPlaylistId = $channelData['contentDetails']['relatedPlaylists']['uploads'] ?? null;
        if (!$uploadsPlaylistId) {
            throw new YouTubeApiException('No uploads playlist found for channel.');
        }

        $this->state->updateState([
            'channel_name' => $channelData['snippet']['title'] ?? '',
            'channel_description' => $channelData['snippet']['description'] ?? '',
            'channel_thumbnail' => $channelData['snippet']['thumbnails']['high']['url'] ?? '',
            'channel_statistics' => $channelData['statistics'] ?? [],
            'uploads_playlist_id' => $uploadsPlaylistId,
            'last_sync_at' => now()->toDateTimeString(),
        ]);

        $this->cache->cacheChannel($channelId, $channelData);

        // 2. Fetch Uploads Playlist Items
        $maxPages = config('youtube.sync.max_pages', $full ? null : 2);
        $pageToken = null;
        $pagesFetched = 0;
        
        $allVideoIds = [];
        $newVideos = [];

        do {
            $playlistResponse = $this->client->getPlaylistItems($uploadsPlaylistId, $pageToken);
            $items = $playlistResponse['items'] ?? [];
            
            foreach ($items as $item) {
                $videoId = $item['contentDetails']['videoId'] ?? null;
                if ($videoId) {
                    $allVideoIds[] = $videoId;
                }
            }

            $pageToken = $playlistResponse['nextPageToken'] ?? null;
            $pagesFetched++;

            if ($maxPages && $pagesFetched >= $maxPages) {
                break;
            }

        } while ($pageToken);

        // 3. Batch Fetch Video Details
        $batchSize = config('youtube.sync.batch_size', 50);
        $videoBatches = array_chunk($allVideoIds, $batchSize);

        $videosData = [];
        $shortsData = [];

        foreach ($videoBatches as $batch) {
            $videosResponse = $this->client->getVideos($batch);
            
            foreach ($videosResponse as $video) {
                $videoId = $video['id'];
                
                // Cache individual video
                $this->cache->cacheVideo($videoId, $video);
                
                // Keep for collections
                $videosData[] = $video;

                // Check for short
                if ($this->shortsClassifier->isShort($video)) {
                    $shortsData[] = $video;
                }
            }
        }

        // 4. Build Collections
        
        // Latest (sort by publishedAt descending)
        usort($videosData, function($a, $b) {
            return strtotime($b['snippet']['publishedAt']) <=> strtotime($a['snippet']['publishedAt']);
        });
        $this->cache->cacheLatest($channelId, array_slice($videosData, 0, 50));

        // Shorts
        usort($shortsData, function($a, $b) {
            return strtotime($b['snippet']['publishedAt']) <=> strtotime($a['snippet']['publishedAt']);
        });
        $this->cache->cacheShorts($channelId, array_slice($shortsData, 0, 50));

        // Popular (sort by viewCount descending)
        $popularData = $videosData;
        usort($popularData, function($a, $b) {
            $viewA = (int)($a['statistics']['viewCount'] ?? 0);
            $viewB = (int)($b['statistics']['viewCount'] ?? 0);
            return $viewB <=> $viewA;
        });
        $this->cache->cachePopular($channelId, array_slice($popularData, 0, 50));

        // Trending (basic implementation)
        $this->cache->cacheTrending($channelId, array_slice($popularData, 0, 50));

        $this->state->updateState([
            'video_count' => count($videosData),
            'last_successful_sync_at' => now()->toDateTimeString(),
        ]);

        return [
            'channel_id' => $channelId,
            'videos_processed' => count($videosData),
            'shorts_found' => count($shortsData)
        ];
    }
}
