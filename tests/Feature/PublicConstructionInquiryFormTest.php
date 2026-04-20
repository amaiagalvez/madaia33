<?php

use App\Models\User;
use Livewire\Livewire;
use App\Models\Construction;
use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Support\Facades\Mail;
use App\Livewire\PublicConstructionInquiryForm;

it('validates required construction inquiry fields', function () {
    $user = User::factory()->create();
    $construction = Construction::factory()->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    Livewire::actingAs($user)
        ->test(PublicConstructionInquiryForm::class, ['constructionId' => $construction->id])
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors([
            'message' => 'required',
        ]);
});

it('stores a construction contact message with the construction tag and sends the existing contact mails', function () {
    Mail::fake();
    app()->setLocale('eu');

    $user = User::factory()->create([
        'name' => 'Ane',
        'email' => 'ane@example.com',
    ]);
    $construction = Construction::factory()->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    Livewire::actingAs($user)
        ->test(PublicConstructionInquiryForm::class, ['constructionId' => $construction->id])
        ->set('message', 'Obra honen hasiera eguna baieztatu nahi dut.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('statusType', 'success');

    $message = ContactMessage::query()->firstOrFail();

    expect($message->user_id)->toBe($user->id)
        ->and($message->notice_tag_id)->toBe($construction->tag->id)
        ->and($message->name)->toBe('Ane')
        ->and($message->email)->toBe('ane@example.com')
        ->and($message->subject)->toBe('[' . __('constructions.inquiry.message_subject_prefix') . '. ' . $construction->title . ']')
        ->and($message->message)->toContain('Obra honen hasiera eguna baieztatu nahi dut.');

    Mail::assertSent(ContactConfirmation::class, 1);
    Mail::assertSent(ContactNotification::class, 1);
});
