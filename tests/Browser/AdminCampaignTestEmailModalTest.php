<?php

use App\Models\User;
use Tests\DuskTestCase;
use App\Models\Campaign;
use Laravel\Dusk\Browser;

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

test('campaign edit form disables test email button while there are unsaved changes', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $campaign) {
        $browser->loginAs($admin)
            ->visit(route('admin.campaigns', ['editCampaign' => $campaign->id]))
            ->waitFor('#selectedTemplateId', 10)
            ->waitFor('[data-campaign-test-email-button]', 10)
            ->assertScript("document.querySelector('[data-campaign-test-email-button]')?.disabled === false", true)
            ->script(<<<'JS'
                const select = document.getElementById('selectedTemplateId');

                if (select) {
                    const option = Array.from(select.options).find((item) => item.value !== '');

                    if (option) {
                        select.value = option.value;
                        select.dispatchEvent(new Event('input', { bubbles: true }));
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            JS);

        $browser->pause(1000)
            ->waitFor('[data-campaign-test-email-help]', 10)
            ->assertScript("document.querySelector('[data-campaign-test-email-button]')?.disabled === true", true)
            ->assertPresent('[data-campaign-test-email-help]');
    });
});

test('campaign list opens schedule modal from clock action', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $campaign) {
        $browser->loginAs($admin)
            ->visit(route('admin.campaigns'))
            ->waitFor('[data-campaign-schedule-action="' . $campaign->id . '"]', 10)
            ->click('[data-campaign-schedule-action="' . $campaign->id . '"]')
            ->waitFor('[data-campaign-schedule-modal]', 10)
            ->assertPresent('[data-campaign-schedule-input]')
            ->click('[data-campaign-schedule-cancel]')
            ->waitUntilMissing('[data-campaign-schedule-modal]', 10);
    });
});
