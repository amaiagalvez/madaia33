<?php

use App\Models\User;
use App\Models\Image;
use App\Models\Owner;
use App\Models\Notice;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicVotingController;
use App\Http\Controllers\Auth\PasswordResetLinkController;

$privatePageHandler = static function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return view('public.private');
};

// ─── Root redirect ────────────────────────────────────────────────────────────

Route::get('/', fn() => redirect()->route('home.eu'))->name('root');
Route::middleware(['guest', 'throttle:5,1'])->post('/forgot-password', PasswordResetLinkController::class)->name('password.email');

// ─── Public routes ────────────────────────────────────────────────────────────

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
    Route::get('/bozketak', [PublicVotingController::class, 'index'])->middleware('auth')->name('votings.eu');
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
    Route::get('/votaciones', [PublicVotingController::class, 'index'])->middleware('auth')->name('votings.es');
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

// ─── Admin routes (auth required) ─────────────────────────────────────────────

Route::middleware(['auth', 'admin.panel'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard.index', [
            'publishedNoticesCount' => Notice::where('is_public', true)->count(),
            'totalNoticesCount' => Notice::count(),
            'imagesCount' => Image::count(),
            'unreadMessagesCount' => ContactMessage::where('is_read', false)->count(),
        ]);
    })->name('dashboard');
    Route::get('/avisos', fn() => view('admin.notices'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('notices');
    Route::get('/imagenes', fn() => view('admin.images'))
        ->middleware('role:superadmin')
        ->name('images');
    Route::get('/mensajes', fn() => view('admin.messages'))
        ->middleware('role:superadmin')
        ->name('messages');
    Route::get('/configuracion', fn() => view('admin.settings'))
        ->middleware('role:superadmin')
        ->name('settings');
    Route::get('/portales', fn() => view('admin.locations.index', ['type' => 'portal']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.portals');
    Route::get('/garajes', fn() => view('admin.locations.index', ['type' => 'garage']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.garages');
    Route::get('/trasteros', fn() => view('admin.locations.index', ['type' => 'storage']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.storages');
    Route::get('/ubicaciones/{location}', function (Location $location) {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user?->canManageLocation($location), 403);

        return view('admin.locations.show', ['location' => $location]);
    })->name('locations.show');
    Route::get('/propietarias', fn() => view('admin.owners.index'))
        ->middleware('role:superadmin')
        ->name('owners.index');
    Route::get('/propietarias/{owner}', fn(Owner $owner) => view('admin.owners.show', ['owner' => $owner]))
        ->middleware('role:superadmin')
        ->name('owners.show');
    Route::get('/votaciones', fn() => view('admin.votings'))
        ->middleware('role:superadmin')
        ->name('votings');
    Route::get('/usuarios', fn() => view('admin.users.index'))
        ->middleware('role:superadmin,admin_general')
        ->name('users.index');
});

Route::middleware('auth')->post('/votings/delegated/clear', [PublicVotingController::class, 'clearDelegatedVoting'])
    ->name('votings.delegated.clear');

require __DIR__ . '/settings.php';
