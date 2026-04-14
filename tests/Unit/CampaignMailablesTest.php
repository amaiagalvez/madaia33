<?php

use App\Mail\CampaignMail;
use App\Models\CampaignDocument;
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

it('renders tracked document links in the campaign email body', function () {
    config()->set('mail.from.address', 'sender@example.test');
    config()->set('mail.from.name', 'Madaia 33');

    $document = new CampaignDocument([
        'id' => 42,
        'filename' => 'convocatoria.pdf',
        'path' => 'campaign-documents/42/convocatoria.pdf',
        'mime_type' => 'application/pdf',
    ]);

    $mailable = new CampaignMail(
        subjectText: 'Aviso comunidad',
        htmlBody: '<p>Contenido del mensaje</p>',
        trackingPixelUrl: 'https://example.test/track/open/token123',
        documentLinks: collect([[
            'label' => 'convocatoria.pdf',
            'url' => 'https://example.test/track/doc/token123/42',
        ]]),
        documents: new Collection([$document]),
    );

    $rendered = $mailable->render();

    expect($rendered)
        ->toContain('convocatoria.pdf')
        ->toContain('https://example.test/track/doc/token123/42');
});

it('wraps campaign emails with the shared branded mail layout', function () {
    config()->set('mail.from.address', 'sender@example.test');
    config()->set('mail.from.name', 'Madaia 33');

    $mailable = new CampaignMail(
        subjectText: 'Aviso comunidad',
        htmlBody: '<p>Contenido del mensaje</p>',
        trackingPixelUrl: 'https://example.test/track/open/token123',
        documents: new Collection,
        legalText: '<p>Aviso legal</p>',
    );

    $rendered = $mailable->render();

    expect($rendered)
        ->toContain('Contenido del mensaje')
        ->toContain('Aviso legal')
        ->toContain('Madaia 33');
});
