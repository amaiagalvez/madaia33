<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\VotingPdfController;
use App\Http\Controllers\NoticeReadController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicVotingController;
use App\Http\Controllers\PublicConstructionController;
use App\Http\Controllers\PublicVotingResultsController;

$privatePageHandler = static function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return view('public.private');
};

Route::get('/', fn() => redirect()->route('home.eu'))->name('root');

Route::prefix('eu')->group(function () use ($privatePageHandler) {
    Route::get('/', [PublicHomeController::class, 'index'])->name('home.eu');
    Route::get('/iragarkiak', fn() => view('public.notices'))->name('notices.eu');
    Route::get('/argazki-bilduma', fn() => view('public.gallery'))->name('gallery.eu');
    Route::get('/harremana', fn() => view('public.contact'))->name('contact.eu');
    Route::get('/pribatua', $privatePageHandler)->name('private.eu');
    Route::get('/pasahitza-ahaztu', fn() => view('pages::auth.forgot-password'))->name('password.request.eu');
    Route::get('/pasahitza-berrezarri/{token}', function (Request $request, string $token) {
        return view('pages::auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    })->name('password.reset.eu');

    Route::get('/pribatutasun-politika', [LegalPageController::class, 'privacyPolicy'])->name('privacy-policy.eu');
    Route::get('/ohar-legala', [LegalPageController::class, 'legalNotice'])->name('legal-notice.eu');
    Route::get('/cookie-politika', [LegalPageController::class, 'cookiePolicy'])->name('cookie-policy.eu');
    Route::get('/bozketak', [PublicVotingController::class, 'index'])->middleware('auth')->name('votings.eu');
    Route::get('/bozketak/{voting}/emaitzak', [PublicVotingResultsController::class, 'show'])->middleware('auth')->name('votings.results.eu');
    Route::get('/bozketak/pdf/delegatua', [VotingPdfController::class, 'publicDelegated'])->middleware('auth')->name('votings.pdf.delegated.eu');
    Route::get('/bozketak/pdf/presentziala', [VotingPdfController::class, 'publicInPerson'])->middleware('auth')->name('votings.pdf.in_person.eu');
    Route::get('/obrak', [PublicConstructionController::class, 'index'])->middleware('auth')->name('constructions.eu');
    Route::get('/obrak/{slug}', [PublicConstructionController::class, 'show'])->middleware('auth')->name('constructions.show.eu');
    Route::get('/profila', [ProfileController::class, 'show'])->middleware('auth')->name('profile.eu');
    Route::post('/profila/baldintzak-onartu', [ProfileController::class, 'acceptTerms'])->middleware('auth')->name('profile.terms.accept.eu');
    Route::post('/profila/jabearen-datuak-eguneratu', [ProfileController::class, 'updateOwner'])->middleware('auth')->name('profile.owner.update.eu');
    Route::post('/profila/jabetzak-balidatu', [ProfileController::class, 'validateAssignments'])->middleware('auth')->name('profile.properties.validate.eu');
    Route::post('/iragarkiak/{notice}/irakurria', [NoticeReadController::class, 'store'])->middleware('auth')->name('notices.read.eu');
});

Route::prefix('es')->group(function () use ($privatePageHandler) {
    Route::get('/', [PublicHomeController::class, 'index'])->name('home.es');
    Route::get('/avisos', fn() => view('public.notices'))->name('notices.es');
    Route::get('/galeria', fn() => view('public.gallery'))->name('gallery.es');
    Route::get('/contacto', fn() => view('public.contact'))->name('contact.es');
    Route::get('/privado', $privatePageHandler)->name('private.es');
    Route::get('/olvido-contrasena', fn() => view('pages::auth.forgot-password'))->name('password.request.es');
    Route::get('/restablecer-contrasena/{token}', function (Request $request, string $token) {
        return view('pages::auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    })->name('password.reset.es');
    Route::get('/politica-de-privacidad', [LegalPageController::class, 'privacyPolicy'])->name('privacy-policy.es');
    Route::get('/aviso-legal', [LegalPageController::class, 'legalNotice'])->name('legal-notice.es');
    Route::get('/politica-de-cookies', [LegalPageController::class, 'cookiePolicy'])->name('cookie-policy.es');
    Route::get('/votaciones', [PublicVotingController::class, 'index'])->middleware('auth')->name('votings.es');
    Route::get('/votaciones/{voting}/resultados', [PublicVotingResultsController::class, 'show'])->middleware('auth')->name('votings.results.es');
    Route::get('/votaciones/pdf/delegado', [VotingPdfController::class, 'publicDelegated'])->middleware('auth')->name('votings.pdf.delegated.es');
    Route::get('/votaciones/pdf/presencial', [VotingPdfController::class, 'publicInPerson'])->middleware('auth')->name('votings.pdf.in_person.es');
    Route::get('/obras', [PublicConstructionController::class, 'index'])->middleware('auth')->name('constructions.es');
    Route::get('/obras/{slug}', [PublicConstructionController::class, 'show'])->middleware('auth')->name('constructions.show.es');
    Route::get('/perfil', [ProfileController::class, 'show'])->middleware('auth')->name('profile.es');
    Route::post('/perfil/aceptar-condiciones', [ProfileController::class, 'acceptTerms'])->middleware('auth')->name('profile.terms.accept.es');
    Route::post('/perfil/actualizar-datos-propietaria', [ProfileController::class, 'updateOwner'])->middleware('auth')->name('profile.owner.update.es');
    Route::post('/perfil/validar-propiedades', [ProfileController::class, 'validateAssignments'])->middleware('auth')->name('profile.properties.validate.es');
    Route::post('/avisos/{notice}/leido', [NoticeReadController::class, 'store'])->middleware('auth')->name('notices.read.es');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Disallow: /admin\n";
    $content .= "\n";
    $content .= 'Sitemap: ' . route('sitemap') . "\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');
