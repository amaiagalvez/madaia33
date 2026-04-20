<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Notice;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Construction;

test('authenticated user can browse public constructions and open a detail page', function () {
    $user = User::factory()->create();
    $construction = Construction::factory()->create([
        'title' => 'Patio obra',
        'slug' => 'patio-obra',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
    ]);
    Notice::factory()->create([
        'title_eu' => 'Patioari buruzko iragarkia',
        'title_es' => 'Aviso sobre el patio',
        'notice_tag_id' => $construction->tag->id,
        'is_public' => true,
        'published_at' => now(),
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($user, $construction): void {
        app()->setLocale('eu');

        $browser->loginAs($user)
            ->visit(route('constructions.eu'))
            ->assertSee(__('constructions.front.title'))
            ->click('[data-construction-link="' . $construction->slug . '"]')
            ->waitFor('[data-page="construction-show"]')
            ->assertSee($construction->title)
            ->assertPresent('[data-construction-header-card]')
            ->assertPresent('[data-construction-contact-trigger]')
            ->assertMissing('[data-construction-timeline]')
            ->assertMissing('[data-construction-inquiry-form-inline]')
            ->click('[data-construction-contact-trigger]')
            ->waitFor('[data-construction-inquiry-modal]')
            ->assertSee(__('constructions.inquiry.title'))
            ->type('#construction-inquiry-message', 'Obrari buruzko kontsulta bat bidaltzen dut Dusk testetik.')
            ->click('[data-construction-inquiry-submit]')
            ->waitFor('[data-construction-inquiry-success]')
            ->assertScript('return getComputedStyle(document.querySelector("[data-construction-inquiry-modal]")).display === "none";')
            ->assertSee(__('constructions.inquiry.success'));
    });
});

test('owner can expand a construction notice and it becomes marked as read', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    $construction = Construction::factory()->create([
        'title' => 'Fatxada obraren jarraipena',
        'slug' => 'fatxada-obraren-jarraipena',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
    ]);

    $notice = Notice::factory()->create([
        'title_eu' => 'Aste honetako eguneraketa',
        'title_es' => 'Actualizacion de esta semana',
        'content_eu' => 'Obraren xehetasun hedagarria.',
        'content_es' => 'Detalle desplegable de la obra.',
        'notice_tag_id' => $construction->tag->id,
        'is_public' => true,
        'published_at' => now(),
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner, $construction, $notice): void {
        $browser->loginAs($owner->user)
            ->visit('/eu/obrak/' . $construction->slug)
            ->waitFor('[data-construction-notice-toggle="' . $notice->id . '"]')
            ->assertPresent('[data-construction-notice-unread="' . $notice->id . '"]')
            ->click('[data-construction-notice-toggle="' . $notice->id . '"]')
            ->waitForText('Obraren xehetasun hedagarria.', 5)
            ->waitUntil(
                '(() => { const icon = document.querySelector("[data-construction-notice-read=\\"' . $notice->id . '\\"]"); return !!icon && getComputedStyle(icon).display !== "none"; })()',
                5,
            )
            ->click('[data-construction-notice-toggle="' . $notice->id . '"]')
            ->pause(500)
            ->assertPresent('[data-construction-notice-read="' . $notice->id . '"]');
    });
});
