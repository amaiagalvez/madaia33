<div x-data="imageGallery()" @keydown.escape.window="close()" @keydown.tab="trapLightboxFocus($event)"
    x-effect="if (open) { focusCloseButton() }">
    <x-front.public-page-header hero="gallery" :title="__('gallery.title')" :subtitle="__('gallery.subtitle')">
        <x-slot:actions>
            <div class="flex flex-wrap items-center justify-start gap-2 lg:justify-end"
                data-gallery-filters>
                <button type="button" wire:click="setTagFilter('')" data-gallery-filter="all"
                    @class([
                        'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                        'border-[#d9755b] bg-[#d9755b] text-white' => $activeTag === '',
                        'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                            $activeTag !== '',
                    ])>
                    {{ __('gallery.filter.all') }}
                </button>
                <button type="button" wire:click="setTagFilter('history')"
                    data-gallery-filter="history" @class([
                        'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                        'border-[#d9755b] bg-[#d9755b] text-white' => $activeTag === 'history',
                        'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                            $activeTag !== 'history',
                    ])>
                    {{ __('gallery.filter.history') }}
                </button>
                <button type="button" wire:click="setTagFilter('comunity')"
                    data-gallery-filter="comunity" @class([
                        'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                        'border-[#d9755b] bg-[#d9755b] text-white' => $activeTag === 'comunity',
                        'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                            $activeTag !== 'comunity',
                    ])>
                    {{ __('gallery.filter.comunity') }}
                </button>
            </div>
        </x-slot:actions>
    </x-front.public-page-header>

    {{-- Image grid --}}
    @if ($images->isEmpty())
        <div
            class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center">
            <div
                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                </svg>
            </div>
            <p class="text-gray-500 text-sm">{{ __('gallery.empty') }}</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" data-gallery-grid>
            @foreach ($images as $image)
                @php
                    $cleanAltText = trim(
                        (string) preg_replace(
                            '/\b(image|imagen|irudia)\b/iu',
                            '',
                            $image->alt_text,
                        ),
                    );
                    $accessibleAltText = $cleanAltText !== '' ? $cleanAltText : $image->alt_text;
                @endphp
                <button type="button" data-gallery-open
                    wire:key="gallery-image-{{ $image->id }}"
                    class="group relative overflow-hidden rounded-xl bg-gray-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $loop->first ? 'col-span-2 min-h-56 sm:row-span-2 sm:min-h-72' : 'aspect-square' }}"
                    @click="show(@js($image->public_url), @js($accessibleAltText), $event)"
                    aria-label="{{ $accessibleAltText }}">
                    <img src="{{ $image->public_url }}" alt="{{ $accessibleAltText }}"
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                        class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105">
                    <span
                        class="absolute left-3 top-3 rounded-full bg-white/85 px-2.5 py-1 text-[11px] font-semibold text-gray-700 shadow-sm">
                        {{ $image->alt_text_eu }}
                    </span>
                    <span aria-hidden="true"
                        class="pointer-events-none absolute inset-x-0 bottom-0 bg-linear-to-t from-black/60 to-transparent px-2 py-2 text-[11px] font-medium text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                        {{ Str::limit($image->alt_text, 40) }}
                    </span>
                </button>
            @endforeach
        </div>
    @endif

    <a href="mailto:{{ $frontPrimaryEmail }}"
        class="elevated-card mt-6 group flex items-start gap-3 bg-linear-to-br from-white to-[#edd2c7]/30 p-4 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2"
        data-gallery-photos-callout>
        <div class="page-icon-emerald shrink-0 h-10 w-10 rounded-lg">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
        </div>
        <div>
            <p
                class="text-sm font-semibold text-gray-900 transition-colors group-hover:text-[#793d3d]">
                {{ __('home.history_photos_title') }}
            </p>
            <p class="mt-0.5 text-xs leading-relaxed text-gray-500">
                {{ $photoRequestText }}
            </p>
        </div>
        <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-[#d9755b]"
            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
    </a>

    {{-- Lightbox --}}
    <template x-teleport="body">
        <div x-cloak x-show="open" x-transition.opacity data-lightbox class="fixed inset-0 z-50"
            @click="close()" @touchstart="handleTouchStart($event)"
            @touchmove="handleTouchMove($event)" aria-modal="true" role="dialog"
            :aria-label="current.alt" data-gallery-lightbox>
            <div class="absolute inset-0 bg-black/80"></div>
            <div class="relative flex h-full w-full items-center justify-center p-4">
                <div class="relative flex max-h-full w-full max-w-5xl items-center justify-center"
                    x-ref="lightboxPanel" @click.stop>
                    <img :src="current.src" :alt="current.alt"
                        :class="isLandscape ? 'max-h-[85vh]' : 'max-h-[90vh]'"
                        class="max-w-full rounded-lg object-contain shadow-2xl">
                    <button type="button" @click="close()" x-ref="lightboxClose"
                        data-lightbox-close
                        class="absolute right-3 top-3 flex min-h-11 min-w-11 items-center justify-center rounded-full bg-white text-gray-800 shadow-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white"
                        aria-label="{{ __('general.close') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    const registerImageGallery = () => {
        if (!window.Alpine) {
            return;
        }

        if (window.__imageGalleryRegistered === true) {
            return;
        }

        window.__imageGalleryRegistered = true;

        Alpine.data('imageGallery', () => ({
            open: false,
            current: {
                src: '',
                alt: ''
            },
            isLandscape: false,
            touchStartY: null,
            lastActiveElement: null,

            init() {
                this.updateOrientation();
                window.addEventListener('resize', () => this
                    .updateOrientation());
            },

            updateOrientation() {
                this.isLandscape = window.innerHeight < window.innerWidth;
            },

            show(src, alt, event) {
                this.lastActiveElement = event?.currentTarget ?? document
                    .activeElement;
                this.current = {
                    src,
                    alt
                };
                this.updateOrientation();
                this.open = true;
                document.body.style.overflow = 'hidden';
                setTimeout(() => document.querySelector('[data-lightbox-close]')
                    ?.focus(), 180);
            },

            focusCloseButton() {
                this.$nextTick(() => setTimeout(() => this.$refs.lightboxClose
                    ?.focus(), 300));
            },

            close() {
                this.open = false;
                this.touchStartY = null;
                document.body.style.overflow = '';
                if (this.lastActiveElement) {
                    this.$nextTick(() => this.lastActiveElement.focus());
                }
            },

            handleTouchStart(event) {
                this.touchStartY = event.touches[0]?.clientY ?? null;
            },

            handleTouchMove(event) {
                if (this.touchStartY === null) {
                    return;
                }

                const currentY = event.touches[0]?.clientY ?? this.touchStartY;
                const deltaY = currentY - this.touchStartY;

                if (deltaY > 90) {
                    this.close();
                }
            },

            trapLightboxFocus(event) {
                if (!this.open || event.key !== 'Tab') {
                    return;
                }

                const focusableElements = Array.from(
                    this.$refs.lightboxPanel.querySelectorAll(
                        'button, [href], [tabindex]:not([tabindex="-1"])')
                );

                if (focusableElements.length === 0) {
                    return;
                }

                const first = focusableElements[0];
                const last = focusableElements[focusableElements.length - 1];
                const active = document.activeElement;

                if (!event.shiftKey && active === last) {
                    event.preventDefault();
                    first.focus();
                    return;
                }

                if (event.shiftKey && active === first) {
                    event.preventDefault();
                    last.focus();
                }
            },
        }));
    };

    if (window.Alpine) {
        registerImageGallery();
    } else {
        document.addEventListener('alpine:init', registerImageGallery, {
            once: true,
        });
    }
</script>
