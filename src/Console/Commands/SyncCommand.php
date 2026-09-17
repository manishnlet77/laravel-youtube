<?php

namespace Manishnlet77\LaravelYouTube\Console\Commands;

use Illuminate\Console\Command;
use Manishnlet77\LaravelYouTube\Services\SyncService;

class SyncCommand extends Command
{
    protected $signature = 'youtube:sync {--full}';
    protected $description = 'Synchronize YouTube channel content';

    public function handle(SyncService $syncService)
    {
        $full = $this->option('full');
        $this->info($full ? 'Running FULL YouTube sync...' : 'Running incremental YouTube sync...');

        try {
            $result = $syncService->sync($full);
            $this->info("Sync completed successfully!");
            $this->info("Videos processed: " . $result['videos_processed']);
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
