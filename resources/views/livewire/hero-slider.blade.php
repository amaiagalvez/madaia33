<div x-data="{
    currentIndex: @entangle('currentIndex'),
    images: @js($images),
    autoplayEnabled: @entangle('autoplayEnabled'),
    autoplayInterval: @js($autoplayInterval),
    autoplayTimer: null,
    touchStartX: null,

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
    },

    handleTouchStart(e) {
        this.touchStartX = e.touches?.[0]?.clientX ?? null;
    },

    handleTouchEnd(e) {
        if (this.touchStartX === null) {
            return;
        }

        const touchEndX = e.changedTouches?.[0]?.clientX ?? this.touchStartX;
        const deltaX = touchEndX - this.touchStartX;

        if (Math.abs(deltaX) > 50) {
            if (deltaX > 0) {
                @this.call('previousImage');
            } else {
                @this.call('nextImage');
            }

            this.resetAutoplay();
        }

        this.touchStartX = null;
    }
}" @keydown="handleKeydown" @touchstart.passive="handleTouchStart($event)"
    @touchend="handleTouchEnd($event)" @autoplay-reset.window="resetAutoplay()"
    @start-autoplay.window="startAutoplay()" data-hero-slider
    class="relative left-1/2 right-1/2 -mx-[50vw] h-56 w-screen overflow-hidden bg-gray-900 sm:h-72 md:h-[22rem] lg:h-[30rem]"
    role="region" aria-roledescription="carousel" aria-label="{{ __('hero_slider.gallery_title') }}"
    tabindex="0">
    @if (empty($images))
        <div
            class="flex h-full w-full items-center justify-center bg-linear-to-br from-gray-800 to-gray-900">
            <p class="text-gray-400 text-center">{{ __('hero_slider.no_images') }}</p>
        </div>
    @else
        <!-- Image Container -->
        <div class="relative w-full h-full overflow-hidden bg-stone-950">
            @foreach ($images as $index => $image)
                <div class="absolute inset-0 transition-opacity duration-500 ease-in-out"
                    :class="{
                        'opacity-100': currentIndex ===
                            {{ $index }},
                        'opacity-0 pointer-events-none': currentIndex !==
                            {{ $index }}
                    }">
                    <div class="flex h-full w-full items-center justify-center">
                        <img src="{{ Storage::disk('public')->exists($image['path']) ? Storage::url($image['path']) : asset('favicon.svg') }}"
                            alt="{{ $image['alt_text'] ?? 'Hero slide' }}"
                            class="h-full w-full object-contain"
                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                            fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}" />
                    </div>
                </div>
            @endforeach

            <!-- Dark Gradient Overlay -->
            <div
                class="absolute inset-0 bg-linear-to-r from-slate-950/80 via-slate-950/30 to-transparent">
            </div>
            <div
                class="absolute inset-0 bg-linear-to-t from-slate-950/70 via-transparent to-slate-950/20">
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

        <!-- Mobile Touch Controls -->
        <div class="absolute inset-x-0 bottom-18 z-10 flex justify-between px-4 md:hidden">
            <button @click="resetAutoplay()" wire:click="previousImage"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur-sm"
                aria-label="{{ __('hero_slider.previous') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button @click="resetAutoplay()" wire:click="nextImage"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur-sm"
                aria-label="{{ __('hero_slider.next') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.25 4.5L15.75 12l-7.5 7.5" />
                </svg>
            </button>
        </div>

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
                    :aria-current="currentIndex === {{ $index }} ? 'true' : 'false'"></button>
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
