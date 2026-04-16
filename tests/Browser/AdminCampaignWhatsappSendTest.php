<?php

/**
 * Validates: whatsapp send-one-by-one flow from admin campaign detail.
 */

use App\Models\User;
use App\Models\Owner;
use Tests\DuskTestCase;
use App\Models\Campaign;
use Laravel\Dusk\Browser;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;

test('admin can send whatsapp messages one by one clicking each recipient button', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $campaign = Campaign::factory()->create([
        'status' => 'completed',
        'channel' => 'whatsapp',
        'body_eu' => '<p>Kaixo komunitatea</p>',
        'body_es' => '<p>Hola comunidad</p>',
    ]);

    $owners = Owner::factory()->count(3)->create([
        'coprop1_phone' => '+34 600 11 22 33',
        'coprop1_phone_invalid' => false,
        'coprop1_phone_error_count' => 0,
    ]);

    $recipients = $owners->map(fn (Owner $owner, int $i) => CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => '+34 600 11 22 3' . $i,
        'tracking_token' => 'dusk-token-wa-' . $i,
        'status' => 'pending',
    ]));

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $campaign, $recipients): void {
        $browser->loginAs($admin)
            ->visit(route('admin.campaigns.show', $campaign))
            ->waitFor('[data-campaign-detail-page]', 10);

        // Suppress window.open so Selenium does not open new tabs
        $browser->script('window.open = function(url) { window.__lastWhatsappUrl = url; };');

        foreach ($recipients as $recipient) {
            $sendSelector = '[data-campaign-whatsapp-send-' . $recipient->id . ']';
            $sentSelector = '[data-campaign-whatsapp-sent-' . $recipient->id . ']';

            $browser->waitFor($sendSelector, 10)
                ->click($sendSelector)
                ->waitFor($sentSelector, 10);

            expect(
                CampaignTrackingEvent::query()
                    ->where('campaign_recipient_id', $recipient->id)
                    ->where('event_type', 'whatsapp_sent')
                    ->exists()
            )->toBeTrue("Recipient {$recipient->id} should have a whatsapp_sent tracking event");

            expect($recipient->fresh()->status)->toBe('sent');
        }
    });

    // Clean up
    CampaignTrackingEvent::query()->whereIn(
        'campaign_recipient_id',
        $recipients->pluck('id')
    )->delete();
    CampaignRecipient::query()->whereIn('id', $recipients->pluck('id'))->delete();
    $campaign->delete();
    $owners->each->delete();
});
