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

    public string $tag = '';

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
            'tag' => 'required|string|in:history,comunity',
        ]);

        $filename = $this->photo->hashName();
        $path = $this->photo->storeAs('images', $filename, 'public');

        Image::create([
            'filename' => $filename,
            'path' => $path,
            'alt_text_eu' => filled($this->altEu) ? $this->altEu : null,
            'alt_text_es' => filled($this->altEs) ? $this->altEs : null,
            'tag' => $this->tag,
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
        $this->tag = '';
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
            <x-admin.form-file-input id="photo" model="photo" :label="__('gallery.admin.file')"
                accept=".jpg,.jpeg,.png,.webp" :hint="__('gallery.admin.formats')" />

            {{-- Preview --}}
            @if ($photo)
                <div class="mt-2">
                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview"
                        class="h-32 w-32 rounded-lg object-cover border border-gray-200" />
                </div>
            @endif

            {{-- Bilingual alt text --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-admin.form-input name="altEu" model="altEu" :label="__('gallery.admin.alt_eu')" />
                <x-admin.form-input name="altEs" model="altEs" :label="__('gallery.admin.alt_es')" />
            </div>

            <x-admin.form-single-radio-pills :legend="__('gallery.admin.tag')" model="tag"
                data-gallery-tag-selector :options="[
                    [
                        'value' => \App\Models\Image::TAG_HISTORY,
                        'label' => __('gallery.filter.history'),
                    ],
                    [
                        'value' => \App\Models\Image::TAG_COMUNITY,
                        'label' => __('gallery.filter.comunity'),
                    ],
                ]" />

            <x-admin.form-footer-actions class="pt-2 mt-0" show-default-buttons :show-cancel-button="false"
                :save-label="__('gallery.admin.upload')" />
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
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button wire:click="deleteImage"
                        class="rounded-md bg-[#793d3d] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#5f2f2f] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
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
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
                        data-admin-image-card="{{ $image->id }}">
                        <div class="group relative aspect-square bg-gray-100">
                            <img src="{{ $image->public_url }}" alt="{{ $image->alt_text }}"
                                loading="lazy" class="h-full w-full object-cover" />

                            {{-- Action overlay --}}
                            <div
                                class="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors duration-200 group-hover:bg-black/40">
                                <x-admin.icon-button-delete
                                    wire:click="confirmDelete({{ $image->id }})"
                                    :title="__('general.buttons.delete')"
                                    aria-label="{{ __('general.buttons.delete') }}"
                                    class="bg-[#d9755b] text-white opacity-0! transition-all duration-200 group-hover:opacity-100 hover:bg-[#793d3d] focus:opacity-100" />
                            </div>
                        </div>

                        <div
                            class="space-y-1 border-t border-gray-200 px-3 py-2 text-xs text-gray-700">
                            <p class="truncate" data-image-alt-eu="{{ $image->id }}">
                                <span class="font-semibold">EU:</span>
                                {{ $image->alt_text_eu ?? '-' }}
                            </p>
                            <p class="truncate" data-image-alt-es="{{ $image->id }}">
                                <span class="font-semibold">ES:</span>
                                {{ $image->alt_text_es ?? '-' }}
                            </p>
                            <p class="truncate" data-image-tag="{{ $image->id }}">
                                <span class="font-semibold">{{ __('gallery.admin.tag') }}:</span>
                                {{ $image->tag === \App\Models\Image::TAG_HISTORY ? __('gallery.filter.history') : ($image->tag === \App\Models\Image::TAG_COMUNITY ? __('gallery.filter.comunity') : '-') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
