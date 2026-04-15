<?php

use App\Models\Notice;

const DEFAULT_NOTICE_CARD_TEMPLATE = '<x-notice-card :notice="$notice" />';

it('renders notice card component with all content', function () {
    $notice = Notice::factory()->public()->create([
        'title_eu' => '<strong>Preba</strong> iragarkia',
        'title_es' => '<strong>Aviso</strong> de prueba',
        'content_eu' => '<p>Hau da <em>preba</em> baten edukia.</p>',
        'content_es' => '<p>Este es el contenido de una <em>prueba</em>.</p>',
    ]);

    $view = test()->blade(DEFAULT_NOTICE_CARD_TEMPLATE, ['notice' => $notice]);

    $view->assertSee('<strong>Preba</strong> iragarkia', false);
    $view->assertSee('<p>Hau da <em>preba</em> baten edukia.</p>', false);
    $view->assertDontSee('&lt;strong&gt;Preba&lt;/strong&gt; iragarkia', false);
    $view->assertDontSee('&lt;p&gt;Hau da &lt;em&gt;preba&lt;/em&gt; baten edukia.&lt;/p&gt;', false);
});

it('renders placeholder image when showImage is true but no image provided', function () {
    $notice = Notice::factory()->public()->create();

    $view = test()->blade('<x-notice-card :notice="$notice" show-image />', ['notice' => $notice]);

    // Check for brand-tinted placeholder
    $view->assertSee('from-[#edd2c7]/20');
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
