<?php

use App\Models\Image;
use App\Models\Notice;
use App\Models\Setting;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SitemapController;

// ─── Public routes ────────────────────────────────────────────────────────────

Route::get('/', fn () => view('public.home'))->name('home');

Route::get('/avisos', fn () => view('public.notices'))->name('notices');

Route::get('/galeria', fn () => view('public.gallery'))->name('gallery');

Route::get('/contacto', fn () => view('public.contact'))->name('contact');

Route::get('/privado', fn () => view('public.private'))->name('private');

Route::get('/politica-de-privacidad', function () {
    $locale = app()->getLocale();
    $fallbackLocale = $locale === 'eu' ? 'es' : 'eu';
    $primaryKey = "legal_page_privacy_policy_{$locale}";
    $fallbackKey = "legal_page_privacy_policy_{$fallbackLocale}";
    $settings = Setting::query()
        ->whereIn('key', [$primaryKey, $fallbackKey])
        ->get(['key', 'value'])
        ->pluck('value', 'key');
    $content = (string) ($settings[$primaryKey] ?? $settings[$fallbackKey] ?? '');

    return view('public.privacy-policy', ['content' => $content]);
})->name('privacy-policy');

Route::get('/aviso-legal', function () {
    $locale = app()->getLocale();
    $fallbackLocale = $locale === 'eu' ? 'es' : 'eu';
    $primaryKey = "legal_page_legal_notice_{$locale}";
    $fallbackKey = "legal_page_legal_notice_{$fallbackLocale}";
    $settings = Setting::query()
        ->whereIn('key', [$primaryKey, $fallbackKey])
        ->get(['key', 'value'])
        ->pluck('value', 'key');
    $content = (string) ($settings[$primaryKey] ?? $settings[$fallbackKey] ?? '');

    return view('public.legal-notice', ['content' => $content]);
})->name('legal-notice');

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
    Route::get('/paginas-legales', fn () => view('admin.legal-pages'))->name('legal-pages');
});

// ─── Auth (Fortify / Breeze) ───────────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
