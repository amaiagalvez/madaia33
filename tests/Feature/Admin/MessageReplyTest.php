<?php

use Livewire\Livewire;
use App\Models\MessageReply;
use App\Mail\MessageReplyMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->adminUser = adminUser();
});

it('displays message reply status icon in table', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    MessageReply::factory()
        ->create(['contact_message_id' => $message->id]);

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->assertSee(__('contact.admin.replied'));
});

it('shows reply button in open message panel', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->assertSee(__('contact.admin.reply_button'), escape: false);
});

it('does not show reply button if message already has a reply', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    MessageReply::factory()
        ->create(['contact_message_id' => $message->id]);

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->assertDontSeeHtml('wire:click="openReplyModal(' . $message->id . ')"');
});

it('opens reply modal when reply button is clicked', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openReplyModal', $message->id)
        ->assertSet('showReplyModal', true)
        ->assertSet('replyingMessageId', $message->id);
});

it('validates reply body minimum length', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openReplyModal', $message->id)
        ->set('replyBody', 'short')
        ->call('sendReply')
        ->assertHasErrors('replyBody');
});

it('validates reply body maximum length', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    $replyBody = str_repeat('a', 5001);

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openReplyModal', $message->id)
        ->set('replyBody', $replyBody)
        ->call('sendReply')
        ->assertHasErrors('replyBody');
});

it('creates and sends message reply', function () {
    Mail::fake();

    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    $replyBody = 'This is a valid reply message with enough content to pass validation.';

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openReplyModal', $message->id)
        ->set('replyBody', $replyBody)
        ->call('sendReply');

    expect(MessageReply::count())->toBe(1);
    expect(MessageReply::first()->reply_body)->toBe($replyBody);
    expect(MessageReply::first()->sent_at)->not->toBeNull();

    Mail::assertSent(MessageReplyMail::class);
});

it('sends email to message sender', function () {
    Mail::fake();

    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create([
            'email' => 'sender@example.com',
        ]);

    $replyBody = 'This is a valid reply message with enough content to pass validation.';

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openReplyModal', $message->id)
        ->set('replyBody', $replyBody)
        ->call('sendReply');

    Mail::assertSent(MessageReplyMail::class, function ($mail) use ($message) {
        return $mail->hasTo($message->email);
    });
});

it('closes reply modal after sending', function () {
    Mail::fake();

    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    $replyBody = 'This is a valid reply message with enough content to pass validation.';

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openReplyModal', $message->id)
        ->set('replyBody', $replyBody)
        ->call('sendReply')
        ->assertSet('showReplyModal', false)
        ->assertSet('replyBody', '')
        ->assertSet('replyingMessageId', null);
});

it('closes message detail after sending reply', function () {
    Mail::fake();

    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    $replyBody = 'This is a valid reply message with enough content to pass validation.';

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->call('openReplyModal', $message->id)
        ->set('replyBody', $replyBody)
        ->call('sendReply')
        ->assertSet('openMessageId', null);
});

it('displays reply content in message detail panel', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    $reply = MessageReply::factory()
        ->create(['contact_message_id' => $message->id]);

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->assertSee($reply->reply_body);
});

it('shows replied status icon for messages with replies', function () {
    $messageWithReply = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    MessageReply::factory()
        ->create(['contact_message_id' => $messageWithReply->id]);

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->assertSee(__('contact.admin.replied'));
});

it('treats unsent replies as not replied in list status', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    MessageReply::factory()->create([
        'contact_message_id' => $message->id,
        'sent_at' => null,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->assertSee(__('contact.admin.not_replied'))
        ->assertDontSee(__('contact.admin.replied_at_label'), escape: false);
});

it('shows reply button when reply exists but was not sent', function () {
    $message = ContactMessage::factory()
        ->for($this->adminUser)
        ->create();

    MessageReply::factory()->create([
        'contact_message_id' => $message->id,
        'reply_body' => 'Draft not sent yet',
        'sent_at' => null,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->assertSee(__('contact.admin.reply_button'), escape: false);
});
