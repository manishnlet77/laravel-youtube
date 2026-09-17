<?php

namespace Manishnlet77\LaravelYouTube;

use Illuminate\Support\ServiceProvider;
use Manishnlet77\LaravelYouTube\Console\Commands\SetupCommand;
use Manishnlet77\LaravelYouTube\Console\Commands\SyncCommand;
use Manishnlet77\LaravelYouTube\Console\Commands\RefreshCommand;
use Manishnlet77\LaravelYouTube\Console\Commands\StatusCommand;
use Manishnlet77\LaravelYouTube\Console\Commands\CacheClearCommand;

class LaravelYouTubeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/youtube.php', 'youtube');

        $this->app->singleton('youtube', function ($app) {
            return new \Manishnlet77\LaravelYouTube\YouTubeManager(
                $app->make(\Manishnlet77\LaravelYouTube\Cache\YouTubeCacheManager::class),
                $app->make(\Manishnlet77\LaravelYouTube\Repositories\ChannelStateRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'youtube');

        // Register routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/youtube.php' => config_path('youtube.php'),
            ], 'youtube-config');

            $this->commands([
                SetupCommand::class,
                SyncCommand::class,
                RefreshCommand::class,
                StatusCommand::class,
                CacheClearCommand::class,
            ]);
        }
    }
}
