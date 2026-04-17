<?php

use App\Models\Owner;
use App\Models\Voting;
use Tests\DuskTestCase;
use App\Models\Campaign;
use App\Models\Property;
use Laravel\Dusk\Browser;
use App\Models\VotingBallot;
use App\Models\OwnerAuditLog;
use App\Models\ContactMessage;
use App\Models\CampaignRecipient;
use App\Models\PropertyAssignment;

test('profile owner tab renders a single shared owner form block', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    OwnerAuditLog::query()->create([
        'owner_id' => $owner->id,
        'changed_by_user_id' => $owner->user_id,
        'field' => 'coprop1_phone',
        'old_value' => '600111222',
        'new_value' => '699999999',
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    $property = Property::factory()->create([
        'community_pct' => 1.25,
        'location_pct' => 2.50,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
        'owner_validated' => false,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner) {
        $browser->resize(1440, 1024)
            ->loginAs($owner->user)
            ->visit('/eu/profila?tab=owner')
            ->waitFor('[data-profile-panel="owner"]', 5)
            ->assertPresent('[data-profile-owner-edit-form]')
            ->assertPresent('[data-profile-owner-form-actions]')
            ->assertPresent('[data-profile-owner-save-button]')
            ->assertPresent('[data-profile-owner-cancel-button]')
            ->assertPresent('[data-owner-shared-form="true"]')
            ->assertPresent('[data-profile-owner-audit-log]')
            ->assertPresent('[data-profile-owner-audit-row]')
            ->assertPresent('[data-profile-owner-validation-help]')
            ->assertPresent('[data-profile-owner-properties-grid]')
            ->assertPresent('[data-profile-owner-property-percentages]')
            ->assertScript(
                'return document.querySelectorAll("[data-owner-shared-form=\"true\"]").length;',
                1,
            )
            ->assertScript(
                '(() => {'
                    . 'var checks = Array.from(document.querySelectorAll("[data-profile-owner-assignment-checkbox]"));'
                    . 'if (checks.length === 0) return false;'
                    . 'return checks.every(function (check) { return check.checked === false; });'
                    . '})()',
                true,
            )
            ->assertScript(
                '(() => {'
                    . 'var checks = Array.from(document.querySelectorAll("[data-profile-owner-assignment-checkbox]"));'
                    . 'if (checks.length === 0) return false;'
                    . 'return checks.every(function (check) {'
                    . 'return check.classList.contains("h-6") && check.classList.contains("w-6");'
                    . '});'
                    . '})()',
                true,
            )
            ->assertScript(
                '(() => {'
                    . 'var grid = document.querySelector("[data-profile-owner-properties-grid]");'
                    . 'if (!grid) return false;'
                    . 'var cols = window.getComputedStyle(grid).gridTemplateColumns.trim().split(/\s+/).length;'
                    . 'return cols === 3;'
                    . '})()',
                true,
            );
    });
});

test('profile terms modal is visible inside viewport when owner has pending acceptance', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => null,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner) {
        $browser->resize(1440, 1024)
            ->loginAs($owner->user)
            ->visit('/eu/profila')
            ->waitFor('[data-profile-terms-modal]', 5)
            ->assertPresent('[data-profile-terms-modal-card]')
            ->assertScript(
                '(() => {'
                    . 'const modal = document.querySelector("[data-profile-terms-modal-card]");'
                    . 'if (!modal) return false;'
                    . 'const rect = modal.getBoundingClientRect();'
                    . 'const viewportHeight = window.innerHeight || document.documentElement.clientHeight;'
                    . 'return rect.top < viewportHeight && rect.bottom > 0;'
                    . '})()',
                true,
            );
    });
});

test('profile votings tab renders pending active and missed closed lists', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    $property = Property::factory()->create();

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'start_date' => today()->subYears(2)->format('Y-m-d'),
        'end_date' => null,
    ]);

    $participatedVoting = Voting::factory()->create([
        'name_eu' => 'Parte hartutako bozketa browser',
        'name_es' => 'Votacion participada browser',
    ]);

    Voting::factory()->current()->create([
        'name_eu' => 'Aktibo pendiente browser',
        'name_es' => 'Activa pendiente browser',
    ]);

    Voting::factory()->create([
        'name_eu' => 'Itxitako galduta browser',
        'name_es' => 'Cerrada perdida browser',
        'starts_at' => today()->subDays(10),
        'ends_at' => today()->subDays(2),
    ]);

    VotingBallot::factory()->create([
        'voting_id' => $participatedVoting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $owner->user_id,
        'voted_at' => now()->subHour(),
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner) {
        $browser->loginAs($owner->user)
            ->visit('/eu/profila?tab=votings')
            ->waitFor('[data-profile-panel="votings"]', 5)
            ->assertPresent('[data-profile-votings-participated]')
            ->assertPresent('[data-profile-votings-pending-active]')
            ->assertPresent('[data-profile-votings-pending-link]')
            ->assertScript(
                'return document.querySelector("[data-profile-votings-pending-link]")?.getAttribute("href")?.endsWith("/eu/bozketak");',
                true,
            )
            ->assertPresent('[data-profile-votings-missed-closed]')
            ->assertSee('Aktibo pendiente browser')
            ->assertSee('Itxitako galduta browser');
    });
});

test('profile messages and received tabs allow expanding long message content', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    $longSentMessage = str_repeat('Mezu luzea profilatik bidalia. ', 12);
    $longReceivedMessage = str_repeat('Mezu jasoa oso luzea da eta osorik erakutsi behar da. ', 12);

    $sentMessage = ContactMessage::factory()->create([
        'user_id' => $owner->user_id,
        'subject' => 'Luzea bidalia',
        'message' => $longSentMessage,
    ]);

    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Luzea jasoa',
        'subject_es' => 'Recibido largo',
        'body_eu' => $longReceivedMessage,
        'body_es' => $longReceivedMessage,
        'channel' => 'email',
        'status' => 'sent',
        'sent_at' => now()->subMinute(),
    ]);

    $receivedRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => $owner->user->email,
        'status' => 'sent',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner, $sentMessage, $receivedRecipient) {
        $browser->loginAs($owner->user)
            ->visit('/eu/profila?tab=messages')
            ->waitFor('[data-profile-panel="messages"]', 5)
            ->assertPresent('[data-profile-message-expandable="' . $sentMessage->id . '"]')
            ->assertScript(
                'return document.querySelector("[data-profile-message-expandable=\"' . $sentMessage->id . '\"]")?.open === false;',
                true,
            )
            ->click('[data-profile-message-toggle="' . $sentMessage->id . '"]')
            ->assertScript(
                'return document.querySelector("[data-profile-message-expandable=\"' . $sentMessage->id . '\"]")?.open === true;',
                true,
            )
            ->visit('/eu/profila?tab=received')
            ->waitFor('[data-profile-panel="received"]', 5)
            ->assertPresent('[data-profile-received-expandable="' . $receivedRecipient->id . '"]')
            ->assertScript(
                'return document.querySelector("[data-profile-received-expandable=\"' . $receivedRecipient->id . '\"]")?.open === false;',
                true,
            )
            ->click('[data-profile-received-toggle="' . $receivedRecipient->id . '"]')
            ->assertScript(
                'return document.querySelector("[data-profile-received-expandable=\"' . $receivedRecipient->id . '\"]")?.open === true;',
                true,
            );
    });
});

test('profile sent and received tables apply clear row separation styles', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    ContactMessage::factory()->create([
        'user_id' => $owner->user_id,
        'subject' => 'Lehen mezua',
        'message' => 'Lehen edukia.',
    ]);

    ContactMessage::factory()->create([
        'user_id' => $owner->user_id,
        'subject' => 'Bigarren mezua',
        'message' => 'Bigarren edukia.',
    ]);

    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Jasotako lehen mezua',
        'subject_es' => 'Primer recibido',
        'body_eu' => 'Jasotako lehen edukia.',
        'body_es' => 'Primer contenido recibido.',
        'channel' => 'email',
        'status' => 'sent',
        'sent_at' => now()->subMinute(),
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => $owner->user->email,
        'status' => 'sent',
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => $owner->user->email,
        'status' => 'sent',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner) {
        $browser->loginAs($owner->user)
            ->visit('/eu/profila?tab=messages')
            ->waitFor('[data-profile-panel="messages"]', 5)
            ->assertScript(
                '(() => {'
                    . 'const rows = Array.from(document.querySelectorAll("[data-profile-message-row]"));'
                    . 'if (rows.length < 2) return false;'
                    . 'return rows.every((row) => row.classList.contains("border-b") && row.classList.contains("border-gray-200") && row.classList.contains("even:bg-gray-50/40"));'
                    . '})()',
                true,
            )
            ->visit('/eu/profila?tab=received')
            ->waitFor('[data-profile-panel="received"]', 5)
            ->assertScript(
                '(() => {'
                    . 'const rows = Array.from(document.querySelectorAll("[data-profile-received-row]"));'
                    . 'if (rows.length < 2) return false;'
                    . 'return rows.every((row) => row.classList.contains("border-b") && row.classList.contains("border-gray-200") && row.classList.contains("even:bg-gray-50/40"));'
                    . '})()',
                true,
            );
    });
});
