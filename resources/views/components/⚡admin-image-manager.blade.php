<?php

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    // ── Upload form state ────────────────────────────────────────────────────
    public mixed $photo = null;

    public string $altEu = '';

    public string $altEs = '';

    // ── Delete confirmation ──────────────────────────────────────────────────
    public ?int $confirmingDeleteId = null;

    // ── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function images(): \Illuminate\Database\Eloquent\Collection
    {
        return Image::orderByDesc('created_at')->get();
    }

    // ── Upload ───────────────────────────────────────────────────────────────

    public function uploadImage(): void
    {
        $this->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'altEu' => 'nullable|string|max:255',
            'altEs' => 'nullable|string|max:255',
        ]);

        $filename = $this->photo->hashName();
        $path = $this->photo->storeAs('images', $filename, 'public');

        Image::create([
            'filename' => $filename,
            'path' => $path,
            'alt_text_eu' => filled($this->altEu) ? $this->altEu : null,
            'alt_text_es' => filled($this->altEs) ? $this->altEs : null,
        ]);

        $this->resetUploadForm();
        unset($this->images);
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function deleteImage(): void
    {
        if (!$this->confirmingDeleteId) {
            return;
        }

        $image = Image::findOrFail($this->confirmingDeleteId);
        Storage::disk('public')->delete($image->path);
        $image->delete();

        $this->confirmingDeleteId = null;
        unset($this->images);
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function resetUploadForm(): void
    {
        $this->photo = null;
        $this->altEu = '';
        $this->altEs = '';
        $this->resetValidation();
    }
};
?>

<div>
    {{-- ── Upload form ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('gallery.admin.upload') }}</h2>

        <form wire:submit="uploadImage" class="space-y-4">
            {{-- File input --}}
            <div>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('gallery.admin.upload') }}
                </label>
                <input id="photo" type="file" wire:model="photo" accept=".jpg,.jpeg,.png,.webp"
                    class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200" />
                <p class="mt-1 text-xs text-gray-500">{{ __('gallery.admin.formats') }}</p>
                @error('photo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Preview --}}
            @if ($photo)
                <div class="mt-2">
                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview"
                        class="h-32 w-32 rounded-lg object-cover border border-gray-200" />
                </div>
            @endif

            {{-- Bilingual alt text --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="altEu" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('gallery.admin.alt_eu') }}
                    </label>
                    <input id="altEu" type="text" wire:model="altEu"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500" />
                    @error('altEu')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="altEs" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('gallery.admin.alt_es') }}
                    </label>
                    <input id="altEs" type="text" wire:model="altEs"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500" />
                    @error('altEs')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    {{ __('gallery.admin.upload') }}
                </button>
            </div>
        </form>
    </div>

    {{-- ── Delete confirmation modal ───────────────────────────────────────── --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" role="dialog"
            aria-modal="true">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4">
                <p class="text-sm text-gray-700 mb-6">{{ __('gallery.admin.confirm_delete') }}</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button wire:click="deleteImage"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Image grid ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('gallery.admin.list') }}</h2>
        </div>

        @if ($this->images->isEmpty())
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-500">{{ __('gallery.empty') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-6">
                @foreach ($this->images as $image)
                    <div
                        class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100">
                        <img src="{{ $image->public_url }}" alt="{{ $image->alt_text }}"
                            loading="lazy" class="h-full w-full object-cover" />

                        {{-- Action overlay --}}
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/40 transition-colors duration-200">
                            <button wire:click="confirmDelete({{ $image->id }})"
                                class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-full bg-red-600 p-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                aria-label="{{ __('general.buttons.delete') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
