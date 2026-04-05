<div x-data="{
    currentIndex: @entangle('currentIndex'),
    images: @js($images),
    autoplayEnabled: @entangle('autoplayEnabled'),
    autoplayInterval: @js($autoplayInterval),
    autoplayTimer: null,

    startAutoplay() {
        if (!this.autoplayEnabled || this.images.length === 0) return;
        this.scheduleNext();
    },

    scheduleNext() {
        this.autoplayTimer = setTimeout(() => {
            if (this.autoplayEnabled) {
                @this.call('nextImage');
                this.scheduleNext();
            }
        }, this.autoplayInterval);
    },

    resetAutoplay() {
        if (this.autoplayTimer) clearTimeout(this.autoplayTimer);
        this.startAutoplay();
    },

    handleKeydown(e) {
        if (e.key === 'ArrowLeft') @this.call('previousImage');
        if (e.key === 'ArrowRight') @this.call('nextImage');
    }
}" @keydown="handleKeydown" @autoplay-reset.window="resetAutoplay()"
    @start-autoplay.window="startAutoplay()" data-hero-slider
    class="relative w-screen left-1/2 right-1/2 -mx-[50vw] h-64 sm:h-80 md:h-96 lg:h-[500px] bg-gray-900 overflow-hidden">
    @if (empty($images))
        <div
            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900">
            <p class="text-gray-400 text-center">{{ __('hero_slider.no_images') }}</p>
        </div>
    @else
        <!-- Image Container -->
        <div class="relative w-full h-full overflow-hidden">
            @foreach ($images as $index => $image)
                <div class="absolute inset-0 transition-opacity duration-500 ease-in-out"
                    :class="{
                        'opacity-100': currentIndex ===
                            {{ $index }},
                        'opacity-0 pointer-events-none': currentIndex !==
                            {{ $index }}
                    }">
                    <img src="{{ Storage::disk('public')->exists($image['path']) ? Storage::url($image['path']) : asset('favicon.svg') }}"
                        alt="{{ $image['alt_text'] ?? 'Hero slide' }}"
                        class="w-full h-full object-cover" loading="lazy" />
                </div>
            @endforeach

            <!-- Dark Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

            <!-- Text Overlay (Optional) -->
            <div
                class="absolute inset-0 flex flex-col items-center justify-center text-center px-4 sm:px-6">
                <h2
                    class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-2 sm:mb-4 line-clamp-3">
                    {{ __('hero_slider.gallery_title') }}
                </h2>
                <p class="text-sm sm:text-base text-gray-200 mb-6 sm:mb-8 hidden sm:block">
                    {{ __('hero_slider.gallery_subtitle') }}
                </p>

                <!-- CTA Button -->
                <a href="{{ route('gallery') }}"
                    class="inline-flex items-center px-6 sm:px-8 py-2 sm:py-3 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                    {{ __('hero_slider.view_more_images') }}
                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Navigation Buttons (Hidden on mobile, visible on md+) -->
        <button @click="resetAutoplay()" wire:click="previousImage"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-10 hidden md:flex items-center justify-center h-12 w-12 rounded-full bg-white/20 hover:bg-white/40 transition-colors backdrop-blur-sm"
            aria-label="{{ __('hero_slider.previous') }}">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>

        <button @click="resetAutoplay()" wire:click="nextImage"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-10 hidden md:flex items-center justify-center h-12 w-12 rounded-full bg-white/20 hover:bg-white/40 transition-colors backdrop-blur-sm"
            aria-label="{{ __('hero_slider.next') }}">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.25 4.5L15.75 12l-7.5 7.5" />
            </svg>
        </button>

        <!-- Pagination Dots (Always Visible) -->
        <div class="absolute bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-10 flex gap-2">
            @foreach ($images as $index => $image)
                <button @click="resetAutoplay()" wire:click="goToImage({{ $index }})"
                    class="h-2 sm:h-3 rounded-full transition-all duration-300"
                    :class="{
                        'bg-white w-6 sm:w-8': currentIndex === {{ $index }},
                        'bg-white/50 w-2 sm:w-3 hover:bg-white/75': currentIndex !==
                            {{ $index }}
                    }"
                    :aria-label="`{{ __('hero_slider.go_to_slide') }} {{ $index + 1 }}`"
                    aria-current="page"></button>
            @endforeach
        </div>

        <!-- Autoplay Toggle (Mobile-friendly) -->
        <button wire:click="toggleAutoplay"
            class="absolute top-4 sm:top-6 right-4 sm:right-6 z-10 flex items-center justify-center h-10 w-10 rounded-full bg-white/20 hover:bg-white/40 transition-colors backdrop-blur-sm"
            :aria-label="`${autoplayEnabled ? '{{ __('hero_slider.pause') }}' : '{{ __('hero_slider.play') }}'}`">
            <svg x-show="autoplayEnabled" class="h-5 w-5 text-white" fill="currentColor"
                viewBox="0 0 24 24">
                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
            </svg>
            <svg x-show="!autoplayEnabled" class="h-5 w-5 text-white" fill="currentColor"
                viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z" />
            </svg>
        </button>
    @endif
</div>
