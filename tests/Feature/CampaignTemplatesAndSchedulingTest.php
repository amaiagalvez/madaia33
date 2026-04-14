<?php

use Livewire\Livewire;
use App\Models\Campaign;
use App\Models\CampaignTemplate;
use Illuminate\Support\Facades\Bus;
use App\Jobs\Messaging\DispatchCampaignJob;

it('applies a template to the campaign manager form', function () {
    $user = adminUser();

    $template = CampaignTemplate::factory()->create([
        'name' => 'Plantilla general',
        'subject_eu' => 'Kaixo **nombre**',
        'subject_es' => 'Hola **nombre**',
        'body_eu' => 'Portaleko oharra',
        'body_es' => 'Aviso del portal',
        'channel' => 'email',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('createCampaign')
        ->set('selectedTemplateId', (string) $template->id)
        ->assertSet('subjectEu', 'Kaixo **nombre**')
        ->assertSet('subjectEs', 'Hola **nombre**')
        ->assertSet('bodyEu', 'Portaleko oharra')
        ->assertSet('bodyEs', 'Aviso del portal')
        ->assertSet('channel', 'email');
});

it('dispatches only due scheduled campaigns and can cancel a schedule', function () {
    $user = adminUser();

    Bus::fake();

    $dueCampaign = Campaign::factory()->create([
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinute(),
    ]);

    $futureCampaign = Campaign::factory()->create([
        'status' => 'scheduled',
        'scheduled_at' => now()->addHour(),
    ]);

    test()->artisan('campaigns:dispatch-scheduled')
        ->assertSuccessful();

    Bus::assertDispatched(DispatchCampaignJob::class, fn (DispatchCampaignJob $job): bool => $job->campaignId === $dueCampaign->id);
    Bus::assertNotDispatched(DispatchCampaignJob::class, fn (DispatchCampaignJob $job): bool => $job->campaignId === $futureCampaign->id);

    $dueCampaign->refresh();
    $futureCampaign->refresh();

    expect($dueCampaign->status)->toBe('sending')
        ->and($futureCampaign->status)->toBe('scheduled');

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('cancelSchedule', $futureCampaign->id);

    $futureCampaign->refresh();

    expect($futureCampaign->status)->toBe('draft')
        ->and($futureCampaign->scheduled_at)->toBeNull();
});

it('creates and deletes campaign templates from the admin manager', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-campaign-template-manager')
        ->call('createTemplate')
        ->set('name', 'Plantilla portal')
        ->set('subjectEu', 'Kaixo komunitatea')
        ->set('subjectEs', 'Hola comunidad')
        ->set('bodyEu', 'Edukia prest')
        ->set('bodyEs', 'Contenido preparado')
        ->set('channel', 'email')
        ->call('saveTemplate');

    $template = CampaignTemplate::query()->where('name', 'Plantilla portal')->firstOrFail();

    Livewire::actingAs($user)
        ->test('admin-campaign-template-manager')
        ->call('confirmDelete', $template->id)
        ->call('deleteTemplate');

    expect(CampaignTemplate::withTrashed()->find($template->id)?->trashed())->toBeTrue();
});

it('renders the campaign template manager page', function () {
    $user = adminUser();

    test()->actingAs($user)
        ->get(route('admin.campaigns.templates'))
        ->assertOk();
});
