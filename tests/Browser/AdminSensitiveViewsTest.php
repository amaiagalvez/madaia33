<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use Tests\DuskTestCase;
use App\Models\Location;
use App\Models\Property;
use Laravel\Dusk\Browser;
use App\Models\PropertyAssignment;

/**
 * Validates sensitive admin views:
 * - Owner index inline panel (assignment controls)
 * - Location detail (editable property/validation state)
 */
test('admin can access owner sensitive inline controls from index', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Owner',
    ]);

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $owner) {
        $browser->loginAs($admin)
            ->visit('/admin/jabeak')
            ->waitFor('[data-owner-id="' . $owner->id . '"]', 5)
            ->assertPresent('[data-owner-id="' . $owner->id . '"]')
            ->click('[data-action="toggle-owner-inline-' . $owner->id . '"]')
            ->waitFor('[data-owner-inline-panel="' . $owner->id . '"]', 5)
            ->assertPresent('[data-owner-inline-panel="' . $owner->id . '"]')
            ->assertPresent('[data-owner-inline-create="' . $owner->id . '"]')
            ->assertMissing('[data-action="deactivate-owner"]');
    });
});

test('admin sees warning when resending owner welcome without email', function () {
    $admin = User::factory()->create([
        'email' => 'dusk-owner-warning-admin@example.com',
        'name' => 'Dusk Owner Warning Admin',
    ]);
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => '000 Owner Without Email',
        'coprop1_email' => '',
    ]);

    $owner->user?->forceFill(['email' => ''])->save();

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '3C',
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $owner) {
        $browser->loginAs($admin)
            ->visit('/admin/jabeak')
            ->waitFor('[data-section="filters"]', 10)
            ->waitFor('#owners-search', 10)
            ->type('#owners-search', '000 Owner Without Email')
            ->pause(700)
            ->waitFor('[data-action="resend-owner-welcome-' . $owner->id . '"]', 10)
            ->click('[data-action="resend-owner-welcome-' . $owner->id . '"]')
            ->waitFor('[data-action="confirm-resend-owner-welcome"]', 5)
            ->click('[data-action="confirm-resend-owner-welcome"]')
            ->waitFor('[data-owner-warning-banner]', 5)
            ->assertPresent('[data-owner-warning-banner]');
    });
});

test('admin owners list uses compact bidalketak-style actions with titles', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Action Titles Owner',
    ]);

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '4A',
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $owner) {
        $script = strtr(<<<'JS'
            (() => {
                const table = document.querySelector('[data-owner-table]');
                const row = document.querySelector('[data-owner-id="OWNER_ID"]');

                if (!table || !row) {
                    return false;
                }

                const actions = row.querySelector('[data-owner-row-actions="OWNER_ID"]');
                const editButton = row.querySelector('[data-action="edit-owner-OWNER_ID"]');
                const detailsButton = row.querySelector('[data-action="toggle-owner-inline-OWNER_ID"]');
                const resendButton = row.querySelector('[data-action="resend-owner-welcome-OWNER_ID"]');

                if (!actions || !editButton || !detailsButton || !resendButton) {
                    return false;
                }

                const hasCompactActions = actions.classList.contains('gap-0');
                const hasTitles = [editButton, detailsButton, resendButton]
                    .every((button) => (button.getAttribute('title') || '').trim().length > 0);

                return table.classList.contains('overflow-x-auto')
                    && hasCompactActions
                    && hasTitles;
            })();
        JS, [
            'OWNER_ID' => (string) $owner->id,
        ]);

        $browser->loginAs($admin)
            ->visit('/admin/jabeak')
            ->waitFor('[data-owner-table]', 10)
            ->waitFor('[data-owner-id="' . $owner->id . '"]', 10)
            ->assertScript($script, true);
    });
});

test('admin owners list shows language next to ko-jabea1 and highlights invalid contacts with whatsapp markers', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Contacts Owner',
        'language' => 'es',
        'coprop1_phone' => '600111222',
        'coprop1_has_whatsapp' => true,
        'coprop1_phone_invalid' => true,
        'coprop2_name' => 'Second Contact',
        'coprop2_phone' => '600333444',
        'coprop2_has_whatsapp' => true,
        'coprop2_email' => 'bad-email@example.com',
        'coprop2_email_invalid' => true,
    ]);

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '5A',
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $owner) {
        $script = strtr(<<<'JS'
            (() => {
                const row = document.querySelector('[data-owner-id="OWNER_ID"]');

                if (!row) {
                    return false;
                }

                const idCell = row.querySelector('[data-owner-id-cell]');
                const ownerLanguage = row.querySelector('[data-owner-language]');
                const coprop1 = row.querySelector('[data-owner-coprop1]');
                const coprop1Phone = row.querySelector('[data-owner-coprop1-phone]');
                const coprop1Whatsapp = row.querySelector('[data-owner-coprop1-whatsapp]');
                const coprop2Email = row.querySelector('[data-owner-coprop2-email]');
                const coprop2Phone = row.querySelector('[data-owner-coprop2-phone]');
                const coprop2Whatsapp = row.querySelector('[data-owner-coprop2-whatsapp]');

                if (!idCell || ownerLanguage || !coprop1 || !coprop1Phone || !coprop1Whatsapp || !coprop2Email || !coprop2Phone || !coprop2Whatsapp) {
                    return false;
                }

                const languageMovedToCoprop1 = coprop1.textContent.includes('[es]')
                    && !idCell.textContent.includes('[es]');

                return idCell.textContent.trim() === ''
                    && languageMovedToCoprop1
                    && coprop1Phone.classList.contains('text-red-600')
                    && coprop1Phone.classList.contains('line-through')
                    && coprop2Email.classList.contains('text-red-600')
                    && coprop2Email.classList.contains('line-through');
            })();
        JS, [
            'OWNER_ID' => (string) $owner->id,
        ]);

        $browser->loginAs($admin)
            ->visit('/admin/jabeak')
            ->waitFor('[data-owner-id="' . $owner->id . '"]', 10)
            ->assertScript($script, true);
    });
});

test('admin owners list shows assignment percentages with validation colors by property type', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Percentages Owner',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'PT-01']);
    $local = Location::factory()->local()->create(['name' => 'LC-01']);
    $garage = Location::factory()->garage()->create(['name' => 'GR-01']);
    $storage = Location::factory()->storage()->create(['name' => 'ST-01']);

    $portalProperty = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'P-1',
        'community_pct' => 10.5,
        'location_pct' => 20.75,
    ]);

    $localProperty = Property::factory()->create([
        'location_id' => $local->id,
        'name' => 'L-1',
        'community_pct' => 30,
        'location_pct' => 40,
    ]);

    $garageProperty = Property::factory()->create([
        'location_id' => $garage->id,
        'name' => 'G-1',
        'community_pct' => 5.25,
        'location_pct' => 6.5,
    ]);

    $storageProperty = Property::factory()->create([
        'location_id' => $storage->id,
        'name' => 'S-1',
        'community_pct' => 1,
        'location_pct' => 2,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $portalProperty->id,
        'end_date' => null,
        'admin_validated' => true,
        'owner_validated' => true,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $localProperty->id,
        'end_date' => null,
        'admin_validated' => false,
        'owner_validated' => false,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $garageProperty->id,
        'end_date' => null,
        'admin_validated' => true,
        'owner_validated' => true,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $storageProperty->id,
        'end_date' => null,
        'admin_validated' => false,
        'owner_validated' => false,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $owner) {
        $script = strtr(<<<'JS'
            (() => {
                const row = document.querySelector('[data-owner-id="OWNER_ID"]');

                if (!row) {
                    return false;
                }

                const checks = [
                    {
                        selector: '[data-owner-assignment-type="portal"]',
                        locationAndProperty: '[P-1] PT-01 P-1',
                        percentages: '20,75% | 10,50%',
                        colorClass: 'text-green-600',
                    },
                    {
                        selector: '[data-owner-assignment-type="garage"]',
                        locationAndProperty: '[G-1] GR-01 G-1',
                        percentages: '6,50% | 5,25%',
                        colorClass: 'text-green-600',
                    },
                    {
                        selector: '[data-owner-assignment-type="storage"]',
                        locationAndProperty: '[S-1] ST-01 S-1',
                        percentages: '2,00% | 1,00%',
                        colorClass: 'text-red-500',
                    },
                    {
                        selector: '[data-owner-assignment-type="local"]',
                        locationAndProperty: '[L-1] LC-01 L-1',
                        percentages: '40,00% | 30,00%',
                        colorClass: 'text-red-500',
                    },
                ];

                return checks.every((check) => {
                    const cell = row.querySelector(check.selector);

                    if (!cell) {
                        return false;
                    }

                    const assignment = Array.from(cell.querySelectorAll('[data-owner-assignment-line]'))
                        .find((line) => {
                            const normalized = line.textContent.replace(/\s+/g, ' ').trim();

                            return normalized.includes(check.locationAndProperty)
                                && normalized.includes(check.percentages);
                        });

                    return assignment && assignment.classList.contains(check.colorClass);
                });
            })();
        JS, [
            'OWNER_ID' => (string) $owner->id,
        ]);

        $browser->loginAs($admin)
            ->visit('/admin/jabeak')
            ->waitFor('[data-owner-id="' . $owner->id . '"]', 10)
            ->assertScript($script, true);
    });
});

test('admin owners list shows download pdf action button', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin/jabeak')
            ->waitFor('[data-action="download-owners-pdf"]', 10)
            ->assertPresent('[data-action="download-owners-pdf"]')
            ->assertScript(<<<'JS'
                (() => {
                    const button = document.querySelector('[data-action="download-owners-pdf"]');

                    if (!button) {
                        return false;
                    }

                    const href = button.getAttribute('href') || '';

                    return href.includes('/admin/jabeak/pdf')
                        && href.includes('filter_status=active');
                })();
            JS, true);
    });
});

test('admin top user menu dropdown opens from the desktop header', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin')
            ->waitFor('[data-test="sidebar-menu-button"]', 5)
            ->click('[data-test="sidebar-menu-button"]')
            ->waitUntil("(() => { const el = document.querySelector('[data-test=\"logout-button\"]'); return !!el && el.offsetParent !== null; })()", 5)
            ->assertPresent('[data-test="logout-button"]')
            ->assertSee($admin->name);
    });
});

test('admin sidebar shows communications block below web with moved items', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin')
            ->waitFor('[data-sidebar-group-web]', 10)
            ->waitFor('[data-sidebar-group-communications]', 10)
            ->waitFor('[data-sidebar-link-messages]', 10)
            ->waitFor('[data-sidebar-link-campaigns]', 10)
            ->waitFor('[data-sidebar-link-votings]', 10)
            ->assertScript(<<<'JS'
                (() => {
                    const webGroup = document.querySelector('[data-sidebar-group-web]');
                    const communicationsGroup = document.querySelector('[data-sidebar-group-communications]');
                    const messages = document.querySelector('[data-sidebar-link-messages]');
                    const campaigns = document.querySelector('[data-sidebar-link-campaigns]');
                    const votings = document.querySelector('[data-sidebar-link-votings]');

                    if (!webGroup || !communicationsGroup || !messages || !campaigns || !votings) {
                        return false;
                    }

                    return webGroup.getBoundingClientRect().top < communicationsGroup.getBoundingClientRect().top
                        && communicationsGroup.getBoundingClientRect().top < messages.getBoundingClientRect().top
                        && messages.getBoundingClientRect().top < campaigns.getBoundingClientRect().top
                        && campaigns.getBoundingClientRect().top < votings.getBoundingClientRect().top;
                })();
            JS, true);
    });
});

test('admin can access location detail sensitive view and sees editable property rows', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'code' => '2B',
        'name' => '2B',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $location, $property) {
        $browser->loginAs($admin)
            ->visit('/admin/finkak/' . $location->id)
            ->waitFor('[data-property-id="' . $property->id . '"]', 5)
            ->assertPresent('[data-property-id="' . $property->id . '"]')
            ->assertSee('2B')
            ->assertPresent('[data-assigned]')
            ->assertMissing('[data-admin-validated]')
            ->assertMissing('[data-owner-validated]');
    });
});

test('guest cannot access sensitive owner and location admin views', function () {
    $location = Location::factory()->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($location) {
        $browser->visit('/_dusk/logout')
            ->visit('/admin/jabeak')
            ->waitFor('[data-test="login-button"]', 10)
            ->assertPresent('[data-test="login-button"]')
            ->assertPathBeginsWith('/eu/pribatua')
            ->visit('/admin/finkak/' . $location->id)
            ->waitFor('[data-test="login-button"]', 10)
            ->assertPresent('[data-test="login-button"]')
            ->assertPathBeginsWith('/eu/pribatua');
    });
});
