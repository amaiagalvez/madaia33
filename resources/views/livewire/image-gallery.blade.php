<div x-data="imageGallery()" @keydown.escape.window="close()" @keydown.tab="trapLightboxFocus($event)"
    x-effect="if (open) { focusCloseButton() }">
    {{-- Image grid --}}
    @if ($images->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
            <p class="text-gray-500 text-sm">{{ __('gallery.empty') }}</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" data-gallery-grid>
            @foreach ($images as $image)
                <button type="button" data-gallery-open
                    class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    @click="show('{{ Storage::url($image->path) }}', '{{ addslashes($image->alt_text) }}', $event)"
                    aria-label="{{ $image->alt_text }}">
                    <img src="{{ Storage::url($image->path) }}" alt="{{ $image->alt_text }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105">
                </button>
            @endforeach
        </div>
    @endif

    {{-- Lightbox --}}
    <div x-show="open" x-cloak style="display: none;" data-lightbox
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
        @click.self="close()" @touchstart="handleTouchStart($event)"
        @touchmove="handleTouchMove($event)" role="dialog" aria-modal="true"
        :aria-label="current.alt">
        <div class="relative max-h-full max-w-5xl w-full flex items-center justify-center"
            x-ref="lightboxPanel">
            <img :src="current.src" :alt="current.alt"
                :class="isLandscape ? 'max-h-[85vh]' : 'max-h-[90vh]'"
                class="max-w-full rounded-lg object-contain shadow-2xl">
            <button type="button" @click="close()" x-ref="lightboxClose" data-lightbox-close
                class="absolute -top-3 -right-3 flex min-h-11 min-w-11 items-center justify-center rounded-full bg-white text-gray-800 shadow-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white"
                aria-label="{{ __('general.close') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imageGallery', () => ({
            open: false,
            current: { src: '', alt: '' },
            isLandscape: false,
            touchStartY: null,
            lastActiveElement: null,

            init() {
                this.updateOrientation();
                window.addEventListener('resize', () => this.updateOrientation());
            },

            updateOrientation() {
                this.isLandscape = window.innerHeight < window.innerWidth;
            },

            show(src, alt, event) {
                this.lastActiveElement = event?.currentTarget ?? document.activeElement;
                this.current = { src, alt };
                this.updateOrientation();
                this.open = true;
                document.body.style.overflow = 'hidden';
                setTimeout(() => document.querySelector('[data-lightbox-close]')?.focus(), 180);
            },

            focusCloseButton() {
                this.$nextTick(() => setTimeout(() => this.$refs.lightboxClose?.focus(), 300));
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
                    this.$refs.lightboxPanel.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])')
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
    });
</script>
