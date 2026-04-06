<?php

namespace App\Livewire;

use App\Models\Image;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class ImageGallery extends Component
{
    /**
     * @return Collection<int, Image>
     */
    public function getImagesProperty(): Collection
    {
        return Image::query()
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
