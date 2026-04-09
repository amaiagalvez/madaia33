<?php

namespace App\Livewire;

use App\Models\Image;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class ImageGallery extends Component
{
    public string $activeTag = '';

    public function setTagFilter(string $tag): void
    {
        if (! in_array($tag, Image::allowedTags(), true)) {
            $this->activeTag = '';

            return;
        }

        $this->activeTag = $this->activeTag === $tag ? '' : $tag;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImagesProperty(): Collection
    {
        return Image::query()
            ->when($this->activeTag !== '', fn ($query) => $query->where('tag', $this->activeTag))
            ->orderByDesc('created_at')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.image-gallery', [
            'images' => $this->getImagesProperty(),
        ]);
    }
}
