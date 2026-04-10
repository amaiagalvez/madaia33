<?php

use App\Models\Notice;
use Illuminate\Support\Str;

const DEFAULT_NOTICE_CARD_TEMPLATE = '<x-notice-card :notice="$notice" />';

it('renders notice card component with all content', function () {
    $notice = Notice::factory()->public()->create([
        'title_eu' => 'Preba iragarkia',
        'title_es' => 'Aviso de prueba',
        'content_eu' => 'Hau da preba baten edukia.',
        'content_es' => 'Este es el contenido de una prueba.',
    ]);

    $view = test()->blade(DEFAULT_NOTICE_CARD_TEMPLATE, ['notice' => $notice]);

    $view->assertSee('Preba iragarkia');
    $view->assertSee(Str::limit($notice->content, 120, '...'));
});

it('renders placeholder image when showImage is true but no image provided', function () {
    $notice = Notice::factory()->public()->create();

    $view = test()->blade('<x-notice-card :notice="$notice" show-image />', ['notice' => $notice]);

    // Check for brand-tinted placeholder with aspect-video
    $view->assertSee('from-[#edd2c7]/20');
    $view->assertSee('aspect-video');
});

it('hides image when showImage is false', function () {
    $notice = Notice::factory()->public()->create();

    $view = test()->blade('<x-notice-card :notice="$notice" :show-image="false" />', ['notice' => $notice]);

    $view->assertDontSee('aspect-video');
});

it('renders location badges when locations exist', function () {
    $notice = Notice::factory()->public()->create();
    attachNoticeToLocationCode($notice, '33-A');

    $notice->refresh();
    $view = test()->blade(DEFAULT_NOTICE_CARD_TEMPLATE, ['notice' => $notice]);

    $view->assertSee('33-A');
});

it('renders with image when provided', function () {
    $notice = Notice::factory()->public()->create();

    $mockImage = (object) [
        'path' => 'images/test.jpg',
        'alt_text' => 'Test image',
    ];

    $view = test()->blade('<x-notice-card :notice="$notice" :image="$image" />', [
        'notice' => $notice,
        'image' => $mockImage,
    ]);

    $view->assertSee('images/test.jpg');
    $view->assertSee('Test');
});

it('renders nothing when notice is null', function () {
    $view = test()->blade(DEFAULT_NOTICE_CARD_TEMPLATE, ['notice' => null]);

    // When null is passed, the component should render nothing (empty div)
    $view->assertDontSee('bg-white');
});
