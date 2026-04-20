<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Construction;
use App\Models\ConstructionInquiry;
use Illuminate\Support\Facades\Mail;
use App\Livewire\PublicConstructionInquiryForm;
use App\Mail\ConstructionInquiryNotificationMail;

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => Role::CONSTRUCTION_MANAGER]);
});

it('validates required construction inquiry fields', function () {
    $user = User::factory()->create();
    $construction = Construction::factory()->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    Livewire::actingAs($user)
        ->test(PublicConstructionInquiryForm::class, ['constructionId' => $construction->id])
        ->set('name', '')
        ->set('email', '')
        ->set('subject', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);
});

it('validates construction inquiry email format', function () {
    $user = User::factory()->create();
    $construction = Construction::factory()->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    Livewire::actingAs($user)
        ->test(PublicConstructionInquiryForm::class, ['constructionId' => $construction->id])
        ->set('email', 'okerra')
        ->set('subject', 'Gaia')
        ->set('message', 'Mezu luze samarra')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);
});

it('stores a construction inquiry and sends notifications to managers', function () {
    Mail::fake();

    $user = User::factory()->create([
        'name' => 'Ane',
        'email' => 'ane@example.com',
    ]);
    $construction = Construction::factory()->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);
    $managerA = User::factory()->create(['email' => 'manager-a@example.com']);
    $managerA->assignRole(Role::CONSTRUCTION_MANAGER);
    $managerB = User::factory()->create(['email' => 'manager-b@example.com']);
    $managerB->assignRole(Role::CONSTRUCTION_MANAGER);
    $construction->managers()->sync([$managerA->id, $managerB->id]);

    Livewire::actingAs($user)
        ->test(PublicConstructionInquiryForm::class, ['constructionId' => $construction->id])
        ->assertSet('name', 'Ane')
        ->assertSet('email', 'ane@example.com')
        ->set('subject', 'Noiz hasiko da?')
        ->set('message', 'Obra honen hasiera eguna baieztatu nahi dut.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('statusType', 'success');

    $inquiry = ConstructionInquiry::query()->firstOrFail();

    expect($inquiry->construction_id)->toBe($construction->id)
        ->and($inquiry->user_id)->toBe($user->id)
        ->and($inquiry->subject)->toBe('Noiz hasiko da?');

    Mail::assertSent(ConstructionInquiryNotificationMail::class, 2);
});
