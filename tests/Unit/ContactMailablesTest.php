<?php

use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;

it('builds ContactConfirmation with expected payload, envelope, and view', function () {
    $mailable = new ContactConfirmation(
        visitorName: 'Ane Etxebarria',
        messageSubject: 'Proba gaia',
        messageBody: 'Kaixo, mezu bat bidaltzen dut.',
    );

    expect($mailable->visitorName)->toBe('Ane Etxebarria')
        ->and($mailable->messageSubject)->toBe('Proba gaia')
        ->and($mailable->messageBody)->toBe('Kaixo, mezu bat bidaltzen dut.')
        ->and($mailable->envelope()->subject)->toBe('Proba gaia')
        ->and($mailable->content()->view)->toBe('mail.contact-confirmation');
});

it('builds ContactNotification with expected payload, envelope, and view', function () {
    $contactMessage = ContactMessage::factory()->make([
        'name' => 'Ane Etxebarria',
        'email' => 'ane@example.com',
        'subject' => 'Proba gaia',
        'message' => 'Kaixo, mezu bat bidaltzen dut.',
    ]);

    $mailable = new ContactNotification($contactMessage);

    expect($mailable->visitorName)->toBe('Ane Etxebarria')
        ->and($mailable->visitorEmail)->toBe('ane@example.com')
        ->and($mailable->messageSubject)->toBe('Proba gaia')
        ->and($mailable->messageBody)->toBe('Kaixo, mezu bat bidaltzen dut.')
        ->and($mailable->envelope()->subject)->toBe('Proba gaia')
        ->and($mailable->content()->view)->toBe('mail.contact-notification');
});
