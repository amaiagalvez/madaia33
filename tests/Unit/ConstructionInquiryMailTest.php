<?php

use App\Models\Construction;
use App\Models\ConstructionInquiry;
use App\Mail\ConstructionInquiryReplyMail;
use App\Mail\ConstructionInquiryNotificationMail;

it('builds construction inquiry notification mail with construction subject', function () {
    $construction = new Construction([
        'title' => 'Fatxadaren konponketa',
    ]);
    $inquiry = new ConstructionInquiry([
        'subject' => 'Noiz amaituko da?',
    ]);

    app()->setLocale('eu');

    $mail = new ConstructionInquiryNotificationMail($inquiry, $construction);

    $content = $mail->content();

    expect($mail->envelope()->subject)->toContain($construction->title)
        ->and($content->view)->toBe('mail.construction-inquiry-notification')
        ->and(($content->with['construction'] ?? null)?->title)->toBe($construction->title);
});

it('builds construction inquiry reply mail with reply body', function () {
    $construction = new Construction([
        'title' => 'Igogailuaren obra',
    ]);
    $inquiry = new ConstructionInquiry([
        'reply' => 'Bihar hasiko dira lanak goizeko zortzietan.',
    ]);
    $inquiry->setRelation('construction', $construction);

    app()->setLocale('eu');

    $mail = new ConstructionInquiryReplyMail($inquiry);
    $content = $mail->content();

    expect($mail->envelope()->subject)->toContain($construction->title)
        ->and($content->view)->toBe('mail.construction-inquiry-reply')
        ->and(($content->with['inquiry'] ?? null)?->reply)->toBe('Bihar hasiko dira lanak goizeko zortzietan.');
});
