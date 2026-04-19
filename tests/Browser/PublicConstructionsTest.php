<?php

use App\Models\User;
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
      ->assertSee(__('constructions.inquiry.title'));
  });
});
