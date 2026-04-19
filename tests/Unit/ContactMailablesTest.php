<?php

use App\Models\Owner;
use App\Models\Voting;
use App\Mail\OwnerWelcomeMail;
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

it('localizes VotingConfirmationMail subject by active locale', function (string $locale) {
    app()->setLocale($locale);

    $owner = new Owner(['coprop1_name' => 'Miren Etxeberria', 'coprop1_email' => 'miren@example.com']);
    $voting = new Voting(['name_eu' => 'Boto-ematea', 'name_es' => 'Votacion']);

    $mailable = new VotingConfirmationMail($owner, $voting);

    expect($mailable->envelope()->subject)->toBe(__('votings.mail.subject'));
})->with([
    ['es'],
    ['eu'],
]);

it('resolves voting confirmation mail body translations by locale', function (string $locale, array $expected) {
    app()->setLocale($locale);

    expect(__('votings.mail.greeting', ['name' => 'Ane']))->toBe($expected['greeting'])
        ->and(__('votings.mail.body', ['voting' => 'Asanblada']))->toBe($expected['body'])
        ->and(__('votings.mail.thanks'))->toBe($expected['thanks']);
})->with([
    [
        'es',
        [
            'greeting' => 'Hola Ane,',
            'body' => 'Hemos registrado tu voto en la votación Asanblada.',
            'thanks' => 'Gracias por participar.',
        ],
    ],
    [
        'eu',
        [
            'greeting' => 'Kaixo Ane,',
            'body' => 'Asanblada bozketan zure botoa jaso dugu.',
            'thanks' => 'Eskerrik asko parte hartzeagatik.',
        ],
    ],
]);

it('renders tracking pixel URL in direct-message mail views', function () {
    config()->set('mail.from.address', CONTACT_MAIL_FROM_ADDRESS);
    config()->set('mail.from.name', CONTACT_MAIL_FROM_NAME);

    $trackingPixelUrl = 'https://example.test/track/open/token-direct-message';

    $contactConfirmation = new ContactConfirmation(
        new ContactMailData(
            visitorName: CONTACT_MAIL_VISITOR_NAME,
            messageSubject: CONTACT_MAIL_SUBJECT,
            messageBody: CONTACT_MAIL_BODY,
            legalText: '<p>Testu legala</p>',
        ),
        CONTACT_MAIL_FROM_ADDRESS,
        CONTACT_MAIL_FROM_NAME,
        $trackingPixelUrl,
    );

    $owner = new Owner(['coprop1_name' => 'Miren Etxeberria', 'coprop1_email' => 'miren@example.com']);
    $voting = new Voting(['name_eu' => 'Boto-ematea', 'name_es' => 'Votacion']);

    $votingConfirmation = new VotingConfirmationMail(
        owner: $owner,
        voting: $voting,
        legalText: '<p>Legezko testua</p>',
        trackingPixelUrl: $trackingPixelUrl,
    );

    $ownerWelcome = new OwnerWelcomeMail(
        fromAddress: CONTACT_MAIL_FROM_ADDRESS,
        fromName: CONTACT_MAIL_FROM_NAME,
        subjectLine: 'Ongietorri',
        bodyHtml: '<p>Edukia</p>',
        trackingPixelUrl: $trackingPixelUrl,
    );

    expect($contactConfirmation->render())->toContain($trackingPixelUrl)
        ->and($votingConfirmation->render())->toContain($trackingPixelUrl)
        ->and($ownerWelcome->render())->toContain($trackingPixelUrl);
});
