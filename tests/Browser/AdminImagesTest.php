<?php

/**
 * Validates: Requirements 6.2, 3.1
 */

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('admin can upload an image and it appears in the public gallery', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();
    $altText = 'Dusk test irudia ' . time();

    // Create a temporary test image inside the container's temp dir
    $tmpImage = '/tmp/dusk_test_' . time() . '.jpg';
    $img = imagecreatetruecolor(100, 100);
    $color = imagecolorallocate($img, 100, 150, 200);
    imagefill($img, 0, 0, $color);
    imagejpeg($img, $tmpImage);
    imagedestroy($img);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $tmpImage, $altText) {
        $browser->loginAs($admin)
            ->visit('/admin/argazkiak')
            ->assertSee('Argazki-bilduma');

        // Attach image file and continue with final upload assertion to avoid flaky preview timing
        $browser->attach('#photo', $tmpImage)
            ->pause(1500);

        // Fill alt text and submit
        $browser->type('#altEu', $altText)
            ->waitFor('[data-admin-pill-option="comunity"]', 5)
            ->click('[data-admin-pill-option="comunity"]')
            ->press('Argazkia igo')
            ->waitUntil("document.body.innerText.includes('" . addslashes($altText) . "')", 30)
            ->assertScript("return document.body.innerText.includes('" . addslashes($altText) . "');", true);

        // Verify in public gallery
        $browser->visit('/eu/argazki-bilduma')
            ->waitUntil("document.body.innerText.includes('" . addslashes($altText) . "')", 30)
            ->assertScript("return document.body.innerText.includes('" . addslashes($altText) . "');", true);
    });

    @unlink($tmpImage);
});
