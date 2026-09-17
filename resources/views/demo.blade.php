<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Integration Test - Demo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tabler Icons (for icons used in UI) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

    <!-- Plyr CSS -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <style>
        body { 
            background: #f4f5f7; 
            min-height: 100vh; 
            padding-bottom: 50px; 
            padding-top: 2rem; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .channel-header {
            display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;
        }
        .channel-logo {
            width: 60px; height: 60px; border-radius: 50%; object-fit: cover;
        }
        
        /* Swiper for Shorts */
        .shorts-swiper { padding-bottom: 30px; }
        .short-card {
            border-radius: 12px; overflow: hidden; background: #000; position: relative; aspect-ratio: 9/16;
            display: block; transition: transform 0.3s;
        }
        .short-card:hover { transform: translateY(-5px); }
        .short-thumb { width: 100%; height: 100%; object-fit: cover; opacity: 0.8; }
        .short-overlay {
            position: absolute; bottom: 0; left: 0; right: 0; padding: 15px 10px;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); color: #fff;
        }
        .short-title { font-size: 0.85rem; font-weight: 600; margin-bottom: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-decoration: none; }
        .short-views { font-size: 0.7rem; color: #ccc; }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
            border-radius: 0.375rem;
            border: none;
        }
        .bg-lighter { background-color: #f8f9fa !important; }
        .play-video-btn { text-decoration: none; color: inherit; }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="channel-header">
        @if($channel)
            <img src="{{ $channel['snippet']['thumbnails']['default']['url'] ?? '' }}" class="channel-logo" alt="Channel Logo" loading="lazy">
            <div>
                <h3 class="mb-0 fw-bold">{{ $channel['snippet']['title'] ?? 'YouTube Channel' }}</h3>
                <p class="mb-0 text-muted">{{ number_format($channel['statistics']['subscriberCount'] ?? 0) }} Subscribers • {{ number_format($channel['statistics']['videoCount'] ?? 0) }} Videos</p>
            </div>
        @endif
    </div>

    @php
        // Fallback to shorts if latest is empty
        $sourceList = !empty($latest) ? collect($latest) : collect($shorts);
        $featuredVideo = $sourceList->first();
        $otherVideos = $sourceList->skip(1);
    @endphp

    <div class="row g-4">
        <!-- Main Content Column -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    @if($featuredVideo)
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-2">
                        <div class="me-1">
                            <h5 class="mb-0" id="main-video-title">{{ $featuredVideo['snippet']['title'] }}</h5>
                            <p class="mb-0 text-muted" id="main-video-date"><i class="ti ti-calendar"></i> {{ \Carbon\Carbon::parse($featuredVideo['snippet']['publishedAt'])->diffForHumans() }}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-danger text-white px-2 py-1 rounded"><i class="ti ti-brand-youtube me-1"></i> Latest</span>
                        </div>
                    </div>
                    
                    <div class="card shadow-none border">
                        <div class="p-2">
                            <div class="cursor-pointer">
                                <!-- Plyr YouTube Embed -->
                                <div class="plyr__video-embed" id="plyr-video-player" data-poster="https://i.ytimg.com/vi/{{ $featuredVideo['id'] }}/hqdefault.jpg">
                                    <iframe
                                        src="https://www.youtube.com/embed/{{ $featuredVideo['id'] }}?origin={{ urlencode(request()->getSchemeAndHttpHost()) }}&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                                        allowfullscreen
                                        allowtransparency
                                        allow="autoplay"
                                        loading="lazy">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-4">
                            <h5 class="fw-bold">Description</h5>
                            <p class="mb-4" id="main-video-desc" style="white-space: pre-wrap; font-size: 0.95rem; max-height: 200px; overflow-y: auto;">{{ \Illuminate\Support\Str::limit($featuredVideo['snippet']['description'] ?? '', 500) }}</p>
                            
                            <hr class="my-4" />
                            <h5 class="fw-bold">Statistics</h5>
                            <div class="d-flex flex-wrap gap-4">
                                <p class="mb-0" id="main-video-views"><i class="ti ti-eye me-1"></i> {{ number_format($featuredVideo['statistics']['viewCount'] ?? 0) }} Views</p>
                                <p class="mb-0" id="main-video-likes"><i class="ti ti-thumb-up me-1"></i> {{ number_format($featuredVideo['statistics']['likeCount'] ?? 0) }} Likes</p>
                                <p class="mb-0" id="main-video-comments"><i class="ti ti-message me-1"></i> {{ number_format($featuredVideo['statistics']['commentCount'] ?? 0) }} Comments</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            
            <!-- Shorts Swiper Section -->
            @if($shorts && count($shorts) > 0)
            <div class="card mb-4">
                <div class="card-header pb-2 pt-3 border-bottom-0">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-device-mobile text-danger"></i> Latest Shorts</h5>
                </div>
                <div class="card-body px-3 pt-3">
                    <div class="swiper shorts-swiper">
                        <div class="swiper-wrapper">
                            @foreach($shorts as $short)
                                <div class="swiper-slide" style="width: 120px;">
                                    <a href="#" class="short-card play-video-btn text-decoration-none"
                                       data-id="{{ $short['id'] }}"
                                       data-title="{{ $short['snippet']['title'] }}"
                                       data-desc="{{ \Illuminate\Support\Str::limit($short['snippet']['description'] ?? '', 500) }}"
                                       data-views="{{ number_format($short['statistics']['viewCount'] ?? 0) }}"
                                       data-likes="{{ number_format($short['statistics']['likeCount'] ?? 0) }}"
                                       data-comments="{{ number_format($short['statistics']['commentCount'] ?? 0) }}"
                                       data-date="{{ \Carbon\Carbon::parse($short['snippet']['publishedAt'])->diffForHumans() }}">
                                        <img src="{{ $short['snippet']['thumbnails']['medium']['url'] ?? '' }}" class="short-thumb" alt="Short Thumbnail" loading="lazy">
                                        <div class="short-overlay">
                                            <div class="short-title text-white">{{ $short['snippet']['title'] }}</div>
                                            <div class="short-views">{{ number_format($short['statistics']['viewCount'] ?? 0) }} Views</div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Other Recent Videos List -->
            <div class="card h-100">
                <div class="card-header border-bottom py-3">
                    <h5 class="mb-0 fw-bold">More Recent Uploads</h5>
                    <small class="text-muted">{{ count($otherVideos) }} videos</small>
                </div>
                <div class="card-body p-3" style="max-height: 600px; overflow-y: auto;">
                    @foreach($otherVideos as $video)
                        <a href="#" 
                           class="play-video-btn d-flex align-items-center p-2 mb-2 rounded text-decoration-none text-dark"
                           style="transition: background-color 0.2s;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'"
                           onmouseout="this.style.backgroundColor='transparent'"
                           data-id="{{ $video['id'] }}"
                           data-title="{{ $video['snippet']['title'] }}"
                           data-desc="{{ \Illuminate\Support\Str::limit($video['snippet']['description'] ?? '', 500) }}"
                           data-views="{{ number_format($video['statistics']['viewCount'] ?? 0) }}"
                           data-likes="{{ number_format($video['statistics']['likeCount'] ?? 0) }}"
                           data-comments="{{ number_format($video['statistics']['commentCount'] ?? 0) }}"
                           data-date="{{ \Carbon\Carbon::parse($video['snippet']['publishedAt'])->diffForHumans() }}">
                            
                            <div class="me-3 position-relative" style="width: 120px; flex-shrink: 0;">
                                <img src="{{ $video['snippet']['thumbnails']['default']['url'] ?? '' }}" class="w-100 rounded shadow-sm" style="aspect-ratio: 16/9; object-fit: cover;" loading="lazy">
                                <div class="position-absolute bottom-0 end-0 bg-dark text-white px-1 m-1 rounded" style="font-size: 0.65rem; opacity: 0.9;">
                                    <i class="ti ti-player-play-filled" style="font-size: 0.6rem;"></i> Play
                                </div>
                            </div>
                            <div style="flex-grow: 1; overflow: hidden;">
                                <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.85rem; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $video['snippet']['title'] }}</h6>
                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($video['snippet']['publishedAt'])->diffForHumans() }}</small>
                                <small class="text-muted" style="font-size: 0.75rem;"><i class="ti ti-eye"></i> {{ number_format($video['statistics']['viewCount'] ?? 0) }} views</small>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Plyr JS -->
<script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Plyr for YouTube
        const player = new Plyr('#plyr-video-player', {
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen']
        });
        
        // Dynamic Video Player logic
        const videoLinks = document.querySelectorAll('.play-video-btn');
        const mainTitle = document.getElementById('main-video-title');
        const mainDesc = document.getElementById('main-video-desc');
        const mainViews = document.getElementById('main-video-views');
        const mainLikes = document.getElementById('main-video-likes');
        const mainComments = document.getElementById('main-video-comments');
        const mainDate = document.getElementById('main-video-date');

        videoLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Highlight active item
                videoLinks.forEach(l => l.classList.remove('bg-lighter'));
                this.classList.add('bg-lighter');

                // Get metadata
                const videoId = this.dataset.id;
                const title = this.dataset.title;
                const desc = this.dataset.desc;
                const views = this.dataset.views;
                const likes = this.dataset.likes;
                const comments = this.dataset.comments;
                const date = this.dataset.date;

                // Update Plyr source using its API and provide metadata to avoid noembed.com 503 errors
                player.source = {
                    type: 'video',
                    title: title,
                    poster: `https://i.ytimg.com/vi/${videoId}/hqdefault.jpg`,
                    sources: [
                        {
                            src: videoId,
                            provider: 'youtube',
                        },
                    ],
                };
                
                // Auto-play the newly loaded video (must wait for ready event)
                player.once('ready', () => {
                    player.play();
                });
                
                // Update DOM elements safely
                if (mainTitle) mainTitle.textContent = title;
                if (mainDesc) mainDesc.textContent = desc;
                if (mainViews) mainViews.innerHTML = `<i class="ti ti-eye me-1"></i> ${views} Views`;
                if (mainLikes) mainLikes.innerHTML = `<i class="ti ti-thumb-up me-1"></i> ${likes} Likes`;
                if (mainComments) mainComments.innerHTML = `<i class="ti ti-message me-1"></i> ${comments} Comments`;
                if (mainDate) mainDate.innerHTML = `<i class="ti ti-calendar"></i> ${date}`;
                
                // Scroll to top of player on mobile
                if (window.innerWidth < 992 && mainTitle) {
                    mainTitle.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Initialize Swiper for Shorts
        const swiper = new Swiper('.shorts-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 15,
            pagination: { el: '.swiper-pagination', clickable: true },
            breakpoints: {
                320: { slidesPerView: 2.5 },
                576: { slidesPerView: 3.5 },
                768: { slidesPerView: 4.5 },
                1024: { slidesPerView: 3.5 },
            }
        });
    });
</script>
</body>
</html>
