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

$renderLegalPage = function (string $pageKey, string $titleKey, string $pageSlug) {
    $locale = app()->getLocale();
    $fallbackLocale = $locale === 'eu' ? 'es' : 'eu';
    $primaryKey = "legal_page_{$pageKey}_{$locale}";
    $fallbackKey = "legal_page_{$pageKey}_{$fallbackLocale}";
    $settings = Setting::query()
        ->whereIn('key', [$primaryKey, $fallbackKey])
        ->get(['key', 'value'])
        ->pluck('value', 'key');

    return view('public.legal-page', [
        'content' => (string) ($settings[$primaryKey] ?? $settings[$fallbackKey] ?? ''),
        'pageSlug' => $pageSlug,
        'titleKey' => $titleKey,
    ]);
};

Route::get('/politica-de-privacidad', function () use ($renderLegalPage) {
    return $renderLegalPage('privacy_policy', 'general.footer.privacy_policy', 'privacy-policy');
})->name('privacy-policy');

Route::get('/aviso-legal', function () use ($renderLegalPage) {
    return $renderLegalPage('legal_notice', 'general.footer.legal_notice', 'legal-notice');
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
