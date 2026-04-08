<?php

use App\Models\Image;
use App\Models\Notice;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\LegalPageController;

// ─── Root redirect ────────────────────────────────────────────────────────────

Route::get('/', fn () => redirect()->route('home.eu'))->name('root');

// ─── Public routes ────────────────────────────────────────────────────────────

Route::prefix('eu')->group(function () {
    Route::get('/', fn () => view('public.home'))->name('home.eu');
    Route::get('/iragarkiak', fn () => view('public.notices'))->name('notices.eu');
    Route::get('/argazki-bilduma', fn () => view('public.gallery'))->name('gallery.eu');
    Route::get('/harremana', fn () => view('public.contact'))->name('contact.eu');
    Route::get('/pribatua', fn () => view('public.private'))->name('private.eu');
    Route::get('/pribatutasun-politika', [LegalPageController::class, 'privacyPolicy'])->name('privacy-policy.eu');
    Route::get('/ohar-legala', [LegalPageController::class, 'legalNotice'])->name('legal-notice.eu');
});

Route::prefix('es')->group(function () {
    Route::get('/', fn () => view('public.home'))->name('home.es');
    Route::get('/avisos', fn () => view('public.notices'))->name('notices.es');
    Route::get('/galeria', fn () => view('public.gallery'))->name('gallery.es');
    Route::get('/contacto', fn () => view('public.contact'))->name('contact.es');
    Route::get('/privado', fn () => view('public.private'))->name('private.es');
    Route::get('/politica-de-privacidad', [LegalPageController::class, 'privacyPolicy'])->name('privacy-policy.es');
    Route::get('/aviso-legal', [LegalPageController::class, 'legalNotice'])->name('legal-notice.es');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Disallow: /admin\n";
    $content .= "\n";
    $content .= 'Sitemap: '.route('sitemap')."\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// ─── Admin routes (auth required) ─────────────────────────────────────────────

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard', [
            'publishedNoticesCount' => Notice::where('is_public', true)->count(),
            'totalNoticesCount' => Notice::count(),
            'imagesCount' => Image::count(),
            'unreadMessagesCount' => ContactMessage::where('is_read', false)->count(),
        ]);
    })->name('dashboard');
    Route::get('/avisos', fn () => view('admin.notices'))->name('notices');
    Route::get('/imagenes', fn () => view('admin.images'))->name('images');
    Route::get('/mensajes', fn () => view('admin.messages'))->name('messages');
    Route::get('/configuracion', fn () => view('admin.settings'))->name('settings');
});

// ─── Auth (Fortify / Breeze) ───────────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
