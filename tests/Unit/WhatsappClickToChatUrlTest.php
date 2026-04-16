<?php

use App\Support\Messaging\WhatsappClickToChatUrl;

it('builds click to chat url with normalized phone and encoded message', function () {
    $builder = new WhatsappClickToChatUrl;

    $url = $builder->build('+34 600 11 22 33', "Kaixo\nhttps://example.org/info");

    expect($url)->toBe('https://wa.me/34600112233?text=Kaixo%0Ahttps%3A%2F%2Fexample.org%2Finfo');
});

it('returns null when the phone does not contain digits', function () {
    $builder = new WhatsappClickToChatUrl;

    expect($builder->build('abc', 'Kaixo'))->toBeNull();
});
