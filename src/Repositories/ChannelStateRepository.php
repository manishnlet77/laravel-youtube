<?php

namespace Manishnlet77\LaravelYouTube\Repositories;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ChannelStateRepository
{
    protected string $storagePath = 'youtube';
    protected string $stateFile = 'channel_state.json';

    protected function getFilePath(): string
    {
        return storage_path('app/' . $this->storagePath . '/' . $this->stateFile);
    }

    protected function loadState(): array
    {
        $path = $this->getFilePath();
        if (File::exists($path)) {
            $content = File::get($path);
            return json_decode($content, true) ?? [];
        }
        return [];
    }

    protected function saveState(array $state): void
    {
        $path = $this->getFilePath();
        $directory = dirname($path);
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($path, json_encode($state, JSON_PRETTY_PRINT));
    }

    public function getState(): array
    {
        return $this->loadState();
    }

    public function updateState(array $data): void
    {
        $state = $this->loadState();
        $state = array_merge($state, $data);
        $this->saveState($state);
    }

    public function getChannelId(): ?string
    {
        $state = $this->loadState();
        return $state['channel_id'] ?? null;
    }

    public function getHandle(): ?string
    {
        $state = $this->loadState();
        return $state['handle'] ?? null;
    }

    public function clear(): void
    {
        $path = $this->getFilePath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
