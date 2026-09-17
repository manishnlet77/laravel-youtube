<?php

namespace Manishnlet77\LaravelYouTube\Console\Commands;

use Illuminate\Console\Command;
use Manishnlet77\LaravelYouTube\Http\YouTubeApiClient;
use Manishnlet77\LaravelYouTube\Repositories\ChannelStateRepository;
use Manishnlet77\LaravelYouTube\Services\SyncService;

class SetupCommand extends Command
{
    protected $signature = 'youtube:setup {handle} {--force}';
    protected $description = 'Setup the YouTube channel integration using a handle (e.g., @astroacharyapawan)';

    public function handle(
        YouTubeApiClient $client,
        ChannelStateRepository $state,
        SyncService $syncService
    ) {
        $handle = $this->argument('handle');
        $force = $this->option('force');

        $existingHandle = $state->getHandle();

        if ($existingHandle && $existingHandle !== $handle && !$force) {
            if (!$this->confirm("Channel is currently set to {$existingHandle}. Replace with {$handle}?")) {
                $this->info('Setup cancelled.');
                return self::SUCCESS;
            }
            $state->clear();
        }

        $this->info("Resolving handle: {$handle}...");

        try {
            $searchResult = $client->searchByHandle($handle);

            if (!$searchResult) {
                $this->error("Channel not found for handle: {$handle}");
                return self::FAILURE;
            }

            $channelId = $searchResult['snippet']['channelId'] ?? null;
            $this->info("Found Channel ID: {$channelId}");

            // Update initial state
            $state->updateState([
                'handle' => $handle,
                'channel_id' => $channelId,
                'configured_at' => now()->toDateTimeString(),
            ]);

            $this->info("Running initial synchronization...");
            
            // Note: In a production scenario, you might dispatch a job here.
            // For now, we run synchronously.
            $result = $syncService->sync(true);

            $this->info("Synchronization complete!");
            $this->info("Processed Videos: " . $result['videos_processed']);
            $this->info("Shorts Discovered: " . $result['shorts_found']);

        } catch (\Exception $e) {
            $this->error("API Error: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
