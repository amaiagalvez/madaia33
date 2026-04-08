<?php

use App\SupportedLocales;

dataset('public_seo_pages', [
    ['home', 'home.seo_description'],
    ['notices', 'notices.seo_description'],
    ['gallery', 'gallery.seo_description'],
    ['contact', 'contact.seo_description'],
    ['private', 'general.private.seo_description'],
    ['privacy-policy', 'general.footer.privacy_policy_description'],
    ['legal-notice', 'general.footer.legal_notice_description'],
]);

test('public pages expose specific seo description in eu', function (string $routeName, string $descriptionKey) {
    $response = test()->withSession(['locale' => SupportedLocales::BASQUE])->get(route($routeName));

    $response->assertOk();
    $response->assertSee('<meta name="description" content="'.e(__($descriptionKey)).'">', false);
})->with('public_seo_pages');

test('public pages expose specific seo description in es', function (string $routeName, string $descriptionKey) {
    $response = test()->withSession(['locale' => SupportedLocales::SPANISH])->get(route($routeName));

    $response->assertOk();
    $response->assertSee('<meta name="description" content="'.e(__($descriptionKey)).'">', false);
})->with('public_seo_pages');
