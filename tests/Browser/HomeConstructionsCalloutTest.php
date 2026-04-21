<?php

use App\Models\Owner;
use App\Models\Voting;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Construction;

test('authenticated owner sees active constructions callout and links on home', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $firstConstruction = Construction::factory()->create([
        'title' => 'Lehen obra aktiboa',
        'slug' => 'lehen-obra-aktiboa',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(3),
        'is_active' => true,
    ]);

    $secondConstruction = Construction::factory()->create([
        'title' => 'Bigarren obra aktiboa',
        'slug' => 'bigarren-obra-aktiboa',
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner, $firstConstruction, $secondConstruction): void {
        $browser->loginAs($owner->user)
            ->visit('/eu')
            ->assertPresent('[data-home-callouts]')
            ->assertPresent('[data-home-votings-callout]')
            ->assertPresent('[data-home-profile-callout]')
            ->assertPresent('[data-home-constructions-callout]')
            ->assertPresent('[data-nav-construction-link="' . $firstConstruction->slug . '"]')
            ->assertPresent('[data-nav-construction-link="' . $secondConstruction->slug . '"]')
            ->assertScript(
                'return document.querySelector("[data-home-callouts]")?.classList.contains("lg:grid-cols-3");',
                true,
            )
            ->assertScript(
                '(function () {'
                    . 'const copy = document.querySelector("[data-home-votings-copy]");'
                    . 'const cta = document.querySelector("[data-home-votings-cta]");'
                    . 'if (!copy || !cta) return false;'
                    . 'const copyRect = copy.getBoundingClientRect();'
                    . 'const ctaRect = cta.getBoundingClientRect();'
                    . 'return ctaRect.top >= copyRect.bottom && Math.round(ctaRect.left) === Math.round(copyRect.left) && Math.round(ctaRect.right) === Math.round(copyRect.right);'
                    . '})()',
                true,
            )
            ->assertScript(
                '(function () {'
                    . 'const copy = document.querySelector("[data-home-profile-copy]");'
                    . 'const cta = document.querySelector("[data-home-profile-cta]");'
                    . 'if (!copy || !cta) return false;'
                    . 'const copyRect = copy.getBoundingClientRect();'
                    . 'const ctaRect = cta.getBoundingClientRect();'
                    . 'return ctaRect.top >= copyRect.bottom && Math.round(ctaRect.left) === Math.round(copyRect.left) && Math.round(ctaRect.right) === Math.round(copyRect.right);'
                    . '})()',
                true,
            )
            ->assertPresent('[data-home-construction-link="' . $firstConstruction->slug . '"]')
            ->assertPresent('[data-home-construction-link="' . $secondConstruction->slug . '"]')
            ->assertScript(
                '(function () {'
                    . 'const profileButton = document.querySelector("[data-home-profile-cta]");'
                    . 'const constructionButton = document.querySelector("[data-home-construction-link=\"' . $firstConstruction->slug . '\"]");'
                    . 'if (!profileButton || !constructionButton) return false;'
                    . 'return profileButton.classList.contains("btn-brand") && constructionButton.classList.contains("btn-brand") && constructionButton.classList.contains("w-full");'
                    . '})()',
                true,
            )
            ->assertScript(
                '(function () {'
                    . 'const copy = document.querySelector("[data-home-constructions-copy]");'
                    . 'const button = document.querySelector("[data-home-construction-link=\"' . $firstConstruction->slug . '\"]");'
                    . 'if (!copy || !button) return false;'
                    . 'const copyRect = copy.getBoundingClientRect();'
                    . 'const buttonRect = button.getBoundingClientRect();'
                    . 'return Math.round(buttonRect.left) === Math.round(copyRect.left) && Math.round(buttonRect.right) === Math.round(copyRect.right);'
                    . '})()',
                true,
            )
            ->click('[data-home-construction-link="' . $firstConstruction->slug . '"]')
            ->waitFor('[data-page="construction-show"]')
            ->assertSee($firstConstruction->title);
    });
});
