<?php

namespace Manishnlet77\LaravelYouTube\Console\Commands;

use Illuminate\Console\Command;
use Manishnlet77\LaravelYouTube\Repositories\ChannelStateRepository;
use Manishnlet77\LaravelYouTube\YouTubeManager;

class StatusCommand extends Command
{
    protected $signature = 'youtube:status';
    protected $description = 'Show the status of the YouTube channel integration';

    public function handle(ChannelStateRepository $state, YouTubeManager $youtube)
    {
        $channelId = $state->getChannelId();
        
        if (!$channelId) {
            $this->error('YouTube channel is not configured. Run `php artisan youtube:setup` first.');
            return self::FAILURE;
        }

        $s = $state->getState();

        $this->info("YouTube Integration");
        $this->info("-------------------");
        $this->info("Handle: " . ($s['handle'] ?? 'N/A'));
        $this->info("Channel: " . ($s['channel_name'] ?? 'N/A'));
        $this->info("Channel ID: " . $channelId);
        $this->info("Uploads Playlist: " . ($s['uploads_playlist_id'] ?? 'N/A'));
        $this->info("Videos Processed: " . ($s['video_count'] ?? 0));
        
        $this->info("\nCache:");
        $this->info("Enabled: " . (config('youtube.cache.enabled') ? 'Yes' : 'No'));
        $this->info("Latest Sync: " . ($s['last_sync_at'] ?? 'Never'));
        $this->info("Last Successful Sync: " . ($s['last_successful_sync_at'] ?? 'Never'));

        return self::SUCCESS;
    }
}
