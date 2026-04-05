<div x-data="{
    open: false,
    current: { src: '', alt: '' },
    show(src, alt) {
        this.current = { src, alt };
        this.open = true;
    },
    close() { this.open = false; }
}" @keydown.escape.window="close()">
    {{-- Image grid --}}
    @if ($images->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
            <p class="text-gray-500 text-sm">{{ __('gallery.empty') }}</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($images as $image)
                <button type="button"
                    class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    @click="show('{{ Storage::url($image->path) }}', '{{ addslashes($image->alt_text) }}')"
                    aria-label="{{ $image->alt_text }}">
                    <img src="{{ Storage::url($image->path) }}" alt="{{ $image->alt_text }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105">
                </button>
            @endforeach
        </div>
    @endif

    {{-- Lightbox --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
        @click.self="close()" role="dialog" aria-modal="true" :aria-label="current.alt">
        <div class="relative max-h-full max-w-5xl w-full flex items-center justify-center">
            <img :src="current.src" :alt="current.alt"
                class="max-h-[90vh] max-w-full rounded-lg object-contain shadow-2xl">
            <button type="button" @click="close()"
                class="absolute -top-3 -right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-800 shadow-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white"
                aria-label="{{ __('general.close') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
