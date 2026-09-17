<?php

namespace Manishnlet77\LaravelYouTube\Services;

class ShortsClassifier
{
    protected bool $enabled;
    protected int $maxDurationSeconds;

    public function __construct()
    {
        $this->enabled = config('youtube.shorts.enabled', true);
        $this->maxDurationSeconds = config('youtube.shorts.max_duration_seconds', 180);
    }

    public function isShort(array $videoData): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // Check duration (Format is usually ISO 8601 like PT1M30S)
        $durationIso = $videoData['contentDetails']['duration'] ?? 'PT0S';
        $durationSeconds = $this->parseDuration($durationIso);

        if ($durationSeconds > $this->maxDurationSeconds) {
            return false;
        }

        // Additional heuristics: Check for #shorts in title or tags
        $title = strtolower($videoData['snippet']['title'] ?? '');
        $description = strtolower($videoData['snippet']['description'] ?? '');
        $tags = array_map('strtolower', $videoData['snippet']['tags'] ?? []);

        if (str_contains($title, '#shorts') || str_contains($description, '#shorts') || in_array('shorts', $tags)) {
            return true;
        }

        // If duration is <= 60 seconds, YouTube heavily biases it as a short
        if ($durationSeconds <= 61) {
            return true;
        }

        return false;
    }

    protected function parseDuration(string $duration): int
    {
        try {
            $interval = new \DateInterval($duration);
            return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
