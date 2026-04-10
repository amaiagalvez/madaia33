<?php

use App\Models\User;
use App\SupportedLocales;
use Illuminate\Support\Facades\Route;

dataset('supported_locales', SupportedLocales::all());

it('privacy and legal pages use readable responsive layout', function () {
    $privacy = test()->get(route(SupportedLocales::routeName('privacy-policy', SupportedLocales::DEFAULT)));
    $privacy->assertOk();
    $privacy->assertSee('max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $privacy->assertSee('data-page-hero="legal"', false);
    $privacy->assertSee('text-base leading-relaxed', false);
    $privacy->assertSee('text-2xl md:text-3xl font-bold text-gray-900 tracking-tight', false);

    $legal = test()->get(route(SupportedLocales::routeName('legal-notice', SupportedLocales::DEFAULT)));
    $legal->assertOk();
    $legal->assertSee('max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $legal->assertSee('data-page-hero="legal"', false);
    $legal->assertSee('text-base leading-relaxed', false);
    $legal->assertSee('text-2xl md:text-3xl font-bold text-gray-900 tracking-tight', false);
});

it('private page shows centered responsive placeholder', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('private', $locale)));

    $response->assertOk();
    $response->assertSee('max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $response->assertSee('min-h-[60vh] flex items-center justify-center', false);
    $response->assertSee('data-private-placeholder', false);
    $response->assertSee(__('general.private.guest_message'));
})->with('supported_locales');

it('private page redirects authenticated users to the admin dashboard', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)->get(route(SupportedLocales::routeName('private', SupportedLocales::DEFAULT)));

    $response->assertRedirect(route('admin.dashboard'));
});

it('404 page renders centered layout with touch friendly action', function () {
    $response = test()->get('/ruta-que-no-existe');

    $response->assertNotFound();
    $response->assertSee('min-h-[70vh] flex items-center justify-center', false);
    $response->assertSee('inline-flex min-h-11 min-w-11 items-center justify-center', false);
});

it('500 page renders centered layout with touch friendly actions', function () {
    Route::get('/__test-500', function () {
        abort(500);
    });

    config(['app.debug' => false]);
    test()->withExceptionHandling();

    $response = test()->get('/__test-500');

    $response->assertStatus(500);
    $response->assertSee('min-h-[70vh] flex items-center justify-center', false);
    $response->assertSee('inline-flex min-h-11 min-w-11 items-center justify-center', false);
    $response->assertSee(route(SupportedLocales::routeName('contact', SupportedLocales::BASQUE)));
});
