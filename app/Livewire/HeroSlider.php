<?php

namespace App\Livewire;

use App\Models\Image;
use Livewire\Component;
use Livewire\Attributes\Computed;

class HeroSlider extends Component
{
    public array $images = [];

    public int $currentIndex = 0;

    public bool $autoplayEnabled = true;

    public int $autoplayInterval = 5000;

    #[Computed]
    public function currentImage()
    {
        return $this->images[$this->currentIndex] ?? null;
    }

    public function mount(): void
    {
        $this->loadImages();
        $this->startAutoplay();
    }

    private function loadImages(): void
    {
        $this->images = Image::query()
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();

        if (empty($this->images)) {
            $this->autoplayEnabled = false;
        }
    }

    public function nextImage(): void
    {
        if (empty($this->images)) {
            return;
        }

        $this->currentIndex = ($this->currentIndex + 1) % count($this->images);
        $this->dispatch('autoplay-reset');
    }

    public function previousImage(): void
    {
        if (empty($this->images)) {
            return;
        }

        $this->currentIndex = ($this->currentIndex - 1 + count($this->images)) % count($this->images);
        $this->dispatch('autoplay-reset');
    }

    public function goToImage(int $index): void
    {
        if ($index < 0 || $index >= count($this->images)) {
            return;
        }

        $this->currentIndex = $index;
        $this->dispatch('autoplay-reset');
    }

    public function toggleAutoplay(): void
    {
        $this->autoplayEnabled = ! $this->autoplayEnabled;
    }

    public function startAutoplay(): void
    {
        if (! $this->autoplayEnabled || empty($this->images)) {
            return;
        }

        $this->dispatch('start-autoplay', interval: $this->autoplayInterval);
    }

    public function render()
    {
        return view('livewire.hero-slider');
    }
}
