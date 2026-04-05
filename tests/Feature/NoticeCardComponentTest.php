<?php

use App\Models\Notice;
use Illuminate\Support\Str;
use App\Models\NoticeLocation;

it('renders notice card component with all content', function () {
    $notice = Notice::factory()->public()->create([
        'title_eu' => 'Preba iragarkia',
        'title_es' => 'Aviso de prueba',
        'content_eu' => 'Hau da preba baten edukia.',
        'content_es' => 'Este es el contenido de una prueba.',
    ]);

    $view = $this->blade('<x-notice-card :notice="$notice" />', ['notice' => $notice]);

    $view->assertSee('Preba iragarkia');
    $view->assertSee(Str::limit($notice->content, 120, '...'));
});

it('renders placeholder image when showImage is true but no image provided', function () {
    $notice = Notice::factory()->public()->create();

    $view = $this->blade('<x-notice-card :notice="$notice" show-image />', ['notice' => $notice]);

    // Check for indigo-tinted placeholder with aspect-video
    $view->assertSee('from-indigo-50');
    $view->assertSee('aspect-video');
});

it('hides image when showImage is false', function () {
    $notice = Notice::factory()->public()->create();

    $view = $this->blade('<x-notice-card :notice="$notice" :show-image="false" />', ['notice' => $notice]);

    $view->assertDontSee('aspect-video');
});

it('renders location badges when locations exist', function () {
    $notice = Notice::factory()->public()->create();
    NoticeLocation::create([
        'notice_id' => $notice->id,
        'location_type' => 'portal',
        'location_code' => '33-A',
    ]);

    $notice->refresh();
    $view = $this->blade('<x-notice-card :notice="$notice" />', ['notice' => $notice]);

    $view->assertSee('33-A');
});

it('renders with image when provided', function () {
    $notice = Notice::factory()->public()->create();

    $mockImage = (object) [
        'path' => 'images/test.jpg',
        'alt_text' => 'Test image',
    ];

    $view = $this->blade('<x-notice-card :notice="$notice" :image="$image" />', [
        'notice' => $notice,
        'image' => $mockImage,
    ]);

    $view->assertSee('images/test.jpg');
    $view->assertSee('Test image');
});

it('renders nothing when notice is null', function () {
    $view = $this->blade('<x-notice-card :notice="$notice" />', ['notice' => null]);

    // When null is passed, the component should render nothing (empty div)
    $view->assertDontSee('bg-white');
});
