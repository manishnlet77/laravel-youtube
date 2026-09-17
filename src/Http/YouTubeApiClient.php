<?php

namespace Manishnlet77\LaravelYouTube\Http;

use Illuminate\Support\Facades\Http;
use Manishnlet77\LaravelYouTube\Exceptions\YouTubeApiException;

class YouTubeApiClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('youtube.base_url', 'https://www.googleapis.com/youtube/v3');
        $this->apiKey = config('youtube.api_key');
        $this->timeout = config('youtube.timeout', 15);

        if (empty($this->apiKey)) {
            throw new YouTubeApiException('YouTube API Key is not configured.');
        }
    }

    protected function get(string $endpoint, array $query = []): array
    {
        $query['key'] = $this->apiKey;

        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/' . ltrim($endpoint, '/'), $query);

        if ($response->failed()) {
            throw new YouTubeApiException('YouTube API request failed: ' . $response->body(), $response->status());
        }

        return $response->json();
    }

    public function searchByHandle(string $handle): ?array
    {
        // First try to search for the handle to find the channel ID
        $response = $this->get('search', [
            'q' => $handle,
            'type' => 'channel',
            'part' => 'snippet',
            'maxResults' => 1,
        ]);

        return $response['items'][0] ?? null;
    }

    public function getChannelDetails(string $channelId): ?array
    {
        $response = $this->get('channels', [
            'id' => $channelId,
            'part' => 'snippet,contentDetails,statistics',
        ]);

        return $response['items'][0] ?? null;
    }

    public function getPlaylistItems(string $playlistId, string $pageToken = null, int $maxResults = 50): array
    {
        $query = [
            'playlistId' => $playlistId,
            'part' => 'snippet,contentDetails',
            'maxResults' => $maxResults,
        ];

        if ($pageToken) {
            $query['pageToken'] = $pageToken;
        }

        return $this->get('playlistItems', $query);
    }

    public function getVideos(array $videoIds): array
    {
        if (empty($videoIds)) return [];

        $response = $this->get('videos', [
            'id' => implode(',', $videoIds),
            'part' => 'snippet,contentDetails,statistics',
        ]);

        return $response['items'] ?? [];
    }
}
