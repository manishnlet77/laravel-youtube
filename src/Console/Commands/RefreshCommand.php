<?php

namespace Manishnlet77\LaravelYouTube\Console\Commands;

use Illuminate\Console\Command;
use Manishnlet77\LaravelYouTube\Services\SyncService;

class RefreshCommand extends Command
{
    protected $signature = 'youtube:refresh';
    protected $description = 'Lightweight refresh of YouTube channel content (prioritizes new/recent videos, stats)';

    public function handle(SyncService $syncService)
    {
        $this->info('Refreshing YouTube channel (lightweight sync)...');
        
        try {
            // Refresh is just a non-full sync
            $result = $syncService->sync(false);
            $this->info("Refresh completed successfully!");
            $this->info("Videos checked: " . $result['videos_processed']);
        } catch (\Exception $e) {
            $this->error("Refresh failed: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
