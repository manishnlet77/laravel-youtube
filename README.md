<p align="center">
  <img src="https://upload.wikimedia.org/wikipedia/commons/b/b8/YouTube_Logo_2017.svg" width="200" alt="YouTube Logo">
</p>

# Laravel YouTube Public Content Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/manishnlet77/laravel-youtube.svg?style=flat-square)](https://packagist.org/packages/manishnlet77/laravel-youtube)
[![Total Downloads](https://img.shields.io/packagist/dt/manishnlet77/laravel-youtube.svg?style=flat-square)](https://packagist.org/packages/manishnlet77/laravel-youtube)
[![License](https://img.shields.io/packagist/l/manishnlet77/laravel-youtube.svg?style=flat-square)](https://packagist.org/packages/manishnlet77/laravel-youtube)

A production-ready, highly optimized Laravel package for effortlessly syncing and displaying public YouTube channels. 

Tired of dealing with complex YouTube APIs, database migrations, and rate limits? This package handles it all. Just provide a YouTube handle (e.g., `@astroacharyapawan`), and this package will automatically fetch, classify (Shorts vs Videos), and cache the channel's entire library seamlessly.

## 🚀 Key Features

- **Zero Database Required**: Uses a highly optimized JSON file-based repository to store channel state.
- **Smart Short Classification**: Automatically detects and separates YouTube Shorts from standard videos.
- **Auto-Caching**: Integrates with Laravel's Cache to ensure your API limits are protected and your app loads instantly.
- **Built-in Demo UI**: Comes with a gorgeous, standalone `youtube/demo` route pre-configured with Plyr and Swiper to showcase your videos.
- **Developer-Friendly Facade**: Fetch the latest videos, trending content, or shorts with a single line of code `YouTube::latest(10)`.

## 📦 Installation

Install the package via Composer:

```bash
composer require manishnlet77/laravel-youtube
```

Add your Google/YouTube Data API Key to your `.env` file:

```env
YOUTUBE_API_KEY=your_google_api_key_here
```

## ⚙️ Quick Setup

Setup your desired YouTube channel by running the setup command with the channel's handle:

```bash
php artisan youtube:setup "@GateSmashers"
```

The command will automatically fetch the channel ID, run an initial sync of all videos, and build the cache!

## 🎨 Built-in Demo

Want to see it in action instantly? Navigate to the built-in demo route on your application:

**http://your-app.com/youtube/demo**

This route provides a stunning, fully-responsive UI powered by `Plyr.io` and `Swiper.js` showcasing the channel's latest videos and shorts.

## 💻 Usage (The Facade)

You can access the channel data anywhere in your application using the `YouTube` facade:

```php
use Manishnlet77\LaravelYouTube\Facades\YouTube;

// Get the channel metadata (subscribers, views, thumbnails)
$channel = YouTube::channel();

// Get the latest 10 standard videos
$latestVideos = YouTube::latest(10);

// Get the latest 15 Shorts
$latestShorts = YouTube::shorts(15);

// Get popular videos sorted by views
$popularVideos = YouTube::popular(10);
```

## 🛠️ Artisan Commands

The package comes with powerful CLI tools to manage your integration:

- `php artisan youtube:setup {handle}`: Setup or switch the integrated YouTube channel.
- `php artisan youtube:sync [--full]`: Force a manual synchronization of the channel's videos.
- `php artisan youtube:refresh`: Perform a lightweight cache refresh (Great for a scheduled cron job).
- `php artisan youtube:status`: View the current health, cache status, and video count of the integration.
- `php artisan youtube:cache:clear`: Flush all YouTube cached data.

## 🕒 Automating Updates

To keep your application perfectly in sync with YouTube without hitting API limits, add the refresh command to your Laravel Scheduler (`routes/console.php` or `app/Console/Kernel.php`):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('youtube:refresh')->hourly();
```

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
