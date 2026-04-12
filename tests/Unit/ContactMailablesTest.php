<?php

use App\Models\Owner;
use App\Models\Voting;
use App\Models\ContactMessage;
use App\Support\ContactMailData;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use App\Mail\VotingConfirmationMail;

const CONTACT_MAIL_VISITOR_NAME = 'Ane Etxebarria';
const CONTACT_MAIL_SUBJECT = 'Proba gaia';
const CONTACT_MAIL_BODY = 'Kaixo, mezu bat bidaltzen dut.';
const CONTACT_MAIL_FROM_ADDRESS = 'noreply@example.com';
const CONTACT_MAIL_FROM_NAME = 'Madaia 33';

it('builds ContactConfirmation with expected payload, envelope, and view', function () {
    $mailable = new ContactConfirmation(
        new ContactMailData(
            visitorName: CONTACT_MAIL_VISITOR_NAME,
            messageSubject: CONTACT_MAIL_SUBJECT,
            messageBody: CONTACT_MAIL_BODY,
            legalText: '<p>Testu legala</p>',
        ),
        CONTACT_MAIL_FROM_ADDRESS,
        CONTACT_MAIL_FROM_NAME,
    );

    expect($mailable->visitorName)->toBe(CONTACT_MAIL_VISITOR_NAME)
        ->and($mailable->messageSubject)->toBe(CONTACT_MAIL_SUBJECT)
        ->and($mailable->messageBody)->toBe(CONTACT_MAIL_BODY)
        ->and($mailable->legalText)->toBe('<p>Testu legala</p>')
        ->and($mailable->fromAddress)->toBe(CONTACT_MAIL_FROM_ADDRESS)
        ->and($mailable->fromName)->toBe(CONTACT_MAIL_FROM_NAME)
        ->and($mailable->envelope()->subject)->toBe(CONTACT_MAIL_SUBJECT)
        ->and($mailable->envelope()->from?->address)->toBe(CONTACT_MAIL_FROM_ADDRESS)
        ->and($mailable->envelope()->from?->name)->toBe(CONTACT_MAIL_FROM_NAME)
        ->and($mailable->content()->view)->toBe('mail.contact-confirmation');
});

it('builds ContactNotification with expected payload, envelope, and view', function () {
    $contactMessage = ContactMessage::factory()->make([
        'name' => CONTACT_MAIL_VISITOR_NAME,
        'email' => 'ane@example.com',
        'subject' => CONTACT_MAIL_SUBJECT,
        'message' => CONTACT_MAIL_BODY,
    ]);

    $mailable = new ContactNotification($contactMessage, '<p>Clausula legal</p>', CONTACT_MAIL_FROM_ADDRESS, CONTACT_MAIL_FROM_NAME);

    expect($mailable->visitorName)->toBe(CONTACT_MAIL_VISITOR_NAME)
        ->and($mailable->visitorEmail)->toBe('ane@example.com')
        ->and($mailable->messageSubject)->toBe(CONTACT_MAIL_SUBJECT)
        ->and($mailable->messageBody)->toBe(CONTACT_MAIL_BODY)
        ->and($mailable->legalText)->toBe('<p>Clausula legal</p>')
        ->and($mailable->fromAddress)->toBe(CONTACT_MAIL_FROM_ADDRESS)
        ->and($mailable->fromName)->toBe(CONTACT_MAIL_FROM_NAME)
        ->and($mailable->envelope()->subject)->toBe(CONTACT_MAIL_SUBJECT)
        ->and($mailable->envelope()->from?->address)->toBe(CONTACT_MAIL_FROM_ADDRESS)
        ->and($mailable->envelope()->from?->name)->toBe(CONTACT_MAIL_FROM_NAME)
        ->and($mailable->content()->view)->toBe('mail.contact-notification');
});

it('builds VotingConfirmationMail with expected envelope and view', function () {
    $owner = new Owner(['coprop1_name' => 'Miren Etxeberria', 'coprop1_email' => 'miren@example.com']);
    $voting = new Voting(['name_eu' => 'Boto-ematea', 'name_es' => 'Votación']);

    $mailable = new VotingConfirmationMail($owner, $voting);

    expect($mailable->owner)->toBe($owner)
        ->and($mailable->voting)->toBe($voting)
        ->and($mailable->envelope()->subject)->toBe(__('votings.mail.subject'))
        ->and($mailable->content()->view)->toBe('mail.voting-confirmation');
});
