<?php

use App\Mail\CampaignMail;
use Illuminate\Support\Collection;

it('builds campaign mail with expected subject and view payload', function () {
    $mailable = new CampaignMail(
        subjectText: 'Aviso comunidad',
        htmlBody: '<p>Contenido del mensaje</p>',
        trackingPixelUrl: 'https://example.test/track/open/token123',
        documents: new Collection,
    );

    expect($mailable->envelope()->subject)->toBe('Aviso comunidad')
        ->and($mailable->content()->view)->toBe('mail.campaign')
        ->and($mailable->content()->with['htmlBody'])->toBe('<p>Contenido del mensaje</p>');
});

it('includes tracking pixel URL in campaign mail content payload', function () {
    $mailable = new CampaignMail(
        subjectText: 'Aviso comunidad',
        htmlBody: '<p>Contenido del mensaje</p>',
        trackingPixelUrl: 'https://example.test/track/open/token123',
        documents: new Collection,
    );

    expect($mailable->content()->with['trackingPixelUrl'])->toBe('https://example.test/track/open/token123');
});
