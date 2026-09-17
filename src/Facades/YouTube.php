<?php

namespace Manishnlet77\LaravelYouTube\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|null channel()
 * @method static array latest(int $limit = 10)
 * @method static array shorts(int $limit = 10)
 * @method static array popular(int $limit = 10)
 * @method static array trending(int $limit = 10)
 * @method static array playlists()
 *
 * @see \Manishnlet77\LaravelYouTube\YouTubeManager
 */
class YouTube extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'youtube';
    }
}
