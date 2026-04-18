<?php

use App\Mail\MessageReplyMail;
use App\Models\ContactMessage;
use App\Models\MessageReply;

it('renders message reply mail with contact data and reply body', function () {
  config()->set('mail.from.address', 'sender@example.test');
  config()->set('mail.from.name', 'Madaia 33');

  $contactMessage = new ContactMessage([
    'name' => 'Ane Etxebarria',
    'email' => 'ane@example.test',
    'subject' => 'Consulta sobre cuotas',
    'message' => 'Mensaje original de contacto',
  ]);

  $reply = new MessageReply([
    'contact_message_id' => 1,
    'reply_body' => 'Eskerrik asko zure mezuagatik. Laster jarriko gara zurekin harremanetan.',
    'sent_at' => now(),
  ]);
  $reply->setRelation('message', $contactMessage);

  $mailable = new MessageReplyMail($reply);
  $envelope = $mailable->envelope();
  $content = $mailable->content();
  $rendered = $mailable->render();

  expect($envelope->subject)
    ->toBe('RE: Consulta sobre cuotas')
    ->and($content->view)->toBe('mail.message-reply')
    ->and($content->with['reply'])->toBe($reply)
    ->and($content->with['contactMessage'])->toBe($contactMessage)
    ->and($rendered)->toContain('Eskerrik asko zure mezuagatik. Laster jarriko gara zurekin harremanetan.')
    ->and($rendered)->toContain('Zure mezua:')
    ->and($rendered)->toContain('Mensaje original de contacto');
});
