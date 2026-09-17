<?php

use Illuminate\Support\Facades\Route;
use Manishnlet77\LaravelYouTube\Facades\YouTube;

Route::get('/youtube/demo', function () {
    $channel = YouTube::channel();
    $latest = YouTube::latest(6);
    $shorts = YouTube::shorts(10);
    
    return view('youtube::demo', compact('channel', 'latest', 'shorts'));
})->name('youtube.demo');
