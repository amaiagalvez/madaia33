<?php

use App\Models\Image;
use Livewire\Livewire;
use App\Livewire\HeroSlider;

describe('HeroSlider image loading', function () {
    test('loads latest images on mount', function () {
        Image::factory()->count(5)->create();

        Livewire::test(HeroSlider::class)
            ->assertViewHas('images');
    });

    test('loads maximum 10 images', function () {
        Image::factory()->count(15)->create();

        Livewire::test(HeroSlider::class)
            ->assertCount('images', 10);
    });

    test('handles empty images gracefully', function () {
        Livewire::test(HeroSlider::class)
            ->assertSet('autoplayEnabled', false);
    });

    test('current index starts at zero', function () {
        Image::factory()->count(3)->create();

        Livewire::test(HeroSlider::class)
            ->assertSet('currentIndex', 0);
    });
});

describe('HeroSlider navigation', function () {
    test('next image increments current index', function () {
        Image::factory()->count(5)->create();

        Livewire::test(HeroSlider::class)
            ->call('nextImage')
            ->assertSet('currentIndex', 1)
            ->call('nextImage')
            ->assertSet('currentIndex', 2);
    });

    test('next image wraps to zero at end', function () {
        Image::factory()->count(3)->create();

        Livewire::test(HeroSlider::class)
            ->call('nextImage')
            ->call('nextImage')
            ->call('nextImage')
            ->assertSet('currentIndex', 0);
    });

    test('previous image decrements current index', function () {
        Image::factory()->count(5)->create();

        Livewire::test(HeroSlider::class)
            ->call('nextImage')
            ->call('nextImage')
            ->call('previousImage')
            ->assertSet('currentIndex', 1);
    });

    test('previous image wraps to end', function () {
        Image::factory()->count(3)->create();

        Livewire::test(HeroSlider::class)
            ->call('previousImage')
            ->assertSet('currentIndex', 2);
    });

    test('go to image sets specific index', function () {
        Image::factory()->count(5)->create();

        Livewire::test(HeroSlider::class)
            ->call('goToImage', 2)
            ->assertSet('currentIndex', 2)
            ->call('goToImage', 4)
            ->assertSet('currentIndex', 4);
    });

    test('go to image validates bounds', function () {
        Image::factory()->count(5)->create();

        Livewire::test(HeroSlider::class)
            ->call('goToImage', -1)
            ->assertSet('currentIndex', 0)
            ->call('goToImage', 100)
            ->assertSet('currentIndex', 0);
    });

    test('toggle autoplay works', function () {
        Image::factory()->count(5)->create();

        Livewire::test(HeroSlider::class)
            ->assertSet('autoplayEnabled', true)
            ->call('toggleAutoplay')
            ->assertSet('autoplayEnabled', false)
            ->call('toggleAutoplay')
            ->assertSet('autoplayEnabled', true);
    });

    test('handles empty images in navigation', function () {
        Livewire::test(HeroSlider::class)
            ->call('nextImage')
            ->assertSet('currentIndex', 0)
            ->call('previousImage')
            ->assertSet('currentIndex', 0);
    });

    test('renders hero slider component', function () {
        Image::factory()->count(3)->create();

        Livewire::test(HeroSlider::class)
            ->assertViewIs('livewire.hero-slider')
            ->assertSee('absolute inset-0');
    });
});
