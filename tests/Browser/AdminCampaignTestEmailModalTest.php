<?php

use App\Models\Campaign;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

test('campaign create form does not show test email button', function () {
  $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

  /** @var DuskTestCase $this */
  $this->browse(function (Browser $browser) use ($admin) {
    $browser->loginAs($admin)
      ->visit(route('admin.campaigns'))
      ->waitFor('[data-campaign-create-button]', 10)
      ->click('[data-campaign-create-button]')
      ->waitFor('[data-admin-form-footer-actions]', 10)
      ->assertMissing('[data-campaign-test-email-button]');
  });
});

test('campaign edit test email modal appears above side panel', function () {
  $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

  $campaign = Campaign::factory()->create([
    'status' => 'draft',
    'channel' => 'email',
  ]);

  /** @var DuskTestCase $this */
  $this->browse(function (Browser $browser) use ($admin, $campaign) {
    $browser->loginAs($admin)
      ->visit(route('admin.campaigns', ['editCampaign' => $campaign->id]))
      ->waitFor('[data-campaign-test-email-button]', 10)
      ->click('[data-campaign-test-email-button]')
      ->waitFor('[data-campaign-test-email-modal]', 10)
      ->assertScript(<<<'JS'
                (() => {
                    const modal = document.querySelector('[data-campaign-test-email-modal]');
                    const panel = document.querySelector('[data-admin-side-panel-form]');

                    if (!modal || !panel) {
                        return false;
                    }

                    const modalZ = parseInt(window.getComputedStyle(modal).zIndex || '0', 10);
                    const panelZ = parseInt(window.getComputedStyle(panel).zIndex || '0', 10);

                    return modalZ > panelZ;
                })();
            JS, true);
  });
});
