<?php

return [

    'api_key' => env('YOUTUBE_API_KEY'),

    'base_url' => 'https://www.googleapis.com/youtube/v3',

    'timeout' => env('YOUTUBE_HTTP_TIMEOUT', 15),

    'cache' => [
        'enabled' => env('YOUTUBE_CACHE_ENABLED', true),
        'ttl' => env('YOUTUBE_CACHE_TTL', 3600),
        'prefix' => 'youtube',

        // Specific TTLs
        'channel_ttl' => 86400,
        'video_ttl' => 3600,
        'statistics_ttl' => 900,
        'latest_ttl' => 900,
        'shorts_ttl' => 1800,
        'popular_ttl' => 1800,
        'trending_ttl' => 900,
        'playlist_ttl' => 3600,
    ],

    'sync' => [
        'max_pages' => env('YOUTUBE_SYNC_MAX_PAGES', null),
        'batch_size' => 50,
    ],

    'shorts' => [
        'enabled' => true,
        'max_duration_seconds' => 180,
    ],

    'trending' => [
        'weights' => [
            'views' => 0.50,
            'likes' => 0.20,
            'comments' => 0.10,
            'recency' => 0.20,
        ],
    ],

];
