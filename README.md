# Laravel YouTube Public Content Package

A production-ready, open-source Laravel Composer package for integrating a public YouTube channel using only its YouTube handle.

## Quick Start

```bash
composer require manishnlet77/laravel-youtube
```

Add your YouTube Data API Key to your `.env`:

```env
YOUTUBE_API_KEY=your_google_api_key_here
```

Run the setup command:

```bash
php artisan youtube:setup @astroacharyapawan
```

And then the package automatically handles channel discovery, uploads playlist extraction, pagination, caching, and building collections like Shorts, Latest, Popular, and Trending!

## Usage

Use the provided Facade anywhere in your application:

```php
use Manishnlet77\LaravelYouTube\Facades\YouTube;

$channel = YouTube::channel();
$latest = YouTube::latest(10);
$shorts = YouTube::shorts(10);
$popular = YouTube::popular(10);
$trending = YouTube::trending(10);
```

## Artisan Commands

- `php artisan youtube:setup {handle}`: Setup your integration.
- `php artisan youtube:sync [--full]`: Re-synchronize the channel manually.
- `php artisan youtube:refresh`: Lightweight data refresh.
- `php artisan youtube:status`: View integration health.
- `php artisan youtube:cache:clear`: Clear the YouTube cache.
