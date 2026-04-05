<?php

/**
 * Validates: Requirements 6.2, 3.1
 */

use App\Models\User;
use Laravel\Dusk\Browser;

test('admin can upload an image and it appears in the public gallery', function () {
    $admin = User::where('email', 'admin@madaia33.eus')->firstOrFail();

    // Create a temporary test image inside the container's temp dir
    $tmpImage = '/tmp/dusk_test_'.time().'.jpg';
    $img = imagecreatetruecolor(100, 100);
    $color = imagecolorallocate($img, 100, 150, 200);
    imagefill($img, 0, 0, $color);
    imagejpeg($img, $tmpImage);
    imagedestroy($img);

    $this->browse(function (Browser $browser) use ($admin, $tmpImage) {
        $browser->loginAs($admin)
            ->visit('/admin/imagenes')
            ->assertSee('Argazki-bilduma');

        // Attach image file and wait for preview
        $browser->attach('#photo', $tmpImage)
            ->waitFor('img[alt="Preview"]', 30);

        // Fill alt text and submit
        $browser->type('#altEu', 'Dusk test irudia')
            ->press('Argazkia igo')
            ->pause(3000)
            ->assertPresent('img[alt="Dusk test irudia"]');

        // Verify in public gallery
        $browser->visit('/galeria')
            ->assertPresent('img[alt="Dusk test irudia"]');
    });

    @unlink($tmpImage);
});
