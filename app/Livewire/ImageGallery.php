<?php

namespace App\Livewire;

use App\Models\Image;
use App\Models\Setting;
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
        return Image::when($this->activeTag !== '', fn ($query) => $query->where('tag', $this->activeTag))
            ->orderByDesc('created_at')
            ->get();
    }

    public function render(): View
    {
        $frontPrimaryEmail = Setting::stringValue('front_primary_email', '');
        $photoRequestText = Setting::localizedString('front_photo_request_text', __('home.history_photos_summary', ['email' => $frontPrimaryEmail]));

        return view('livewire.front.image-gallery', [
            'images' => $this->getImagesProperty(),
            'frontPrimaryEmail' => $frontPrimaryEmail,
            'photoRequestText' => $photoRequestText,
        ]);
    }
}
