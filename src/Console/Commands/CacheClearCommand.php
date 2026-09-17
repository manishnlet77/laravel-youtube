<?php

namespace Manishnlet77\LaravelYouTube\Console\Commands;

use Illuminate\Console\Command;
use Manishnlet77\LaravelYouTube\Cache\YouTubeCacheManager;
use Manishnlet77\LaravelYouTube\Repositories\ChannelStateRepository;

class CacheClearCommand extends Command
{
    protected $signature = 'youtube:cache:clear {--channel} {--video=} {--all}';
    protected $description = 'Clear package-owned cache keys for YouTube integration';

    public function handle(YouTubeCacheManager $cache, ChannelStateRepository $state)
    {
        $channelId = $state->getChannelId();

        if ($this->option('all')) {
            $this->info('Clearing all YouTube cache (tags not fully supported on file driver, clearing known channel key)...');
            if ($channelId) {
                $cache->forgetChannel($channelId);
                // Note: Clearing collections (latest, popular, etc.) requires specific keys
            }
            $this->info('Cache cleared.');
            return self::SUCCESS;
        }

        if ($this->option('channel')) {
            if (!$channelId) {
                $this->error('No channel configured.');
                return self::FAILURE;
            }
            $cache->forgetChannel($channelId);
            $this->info('Channel cache cleared.');
        }

        if ($videoId = $this->option('video')) {
            $cache->forgetVideo($videoId);
            $this->info("Video {$videoId} cache cleared.");
        }

        return self::SUCCESS;
    }
}
