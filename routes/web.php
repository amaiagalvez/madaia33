<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Messaging\TrackingController;
use App\Http\Controllers\Auth\PasswordResetLinkController;

Route::middleware(['guest', 'throttle:5,1'])->post('/forgot-password', PasswordResetLinkController::class)->name('password.email');

Route::get('/track/open/{token}', [TrackingController::class, 'open'])->name('tracking.open');
Route::get('/track/click/{token}', [TrackingController::class, 'click'])->name('tracking.click');
Route::get('/track/doc/{token}/{document}', [TrackingController::class, 'document'])->name('tracking.document');

Route::get('/eu/track/open/{token?}', [TrackingController::class, 'open'])->name('tracking.open.eu');
Route::get('/es/track/open/{token?}', [TrackingController::class, 'open'])->name('tracking.open.es');
Route::get('/eu/track/click/{token?}', [TrackingController::class, 'click'])->name('tracking.click.eu');
Route::get('/es/track/click/{token?}', [TrackingController::class, 'click'])->name('tracking.click.es');
Route::get('/eu/track/doc/{token?}/{document?}', [TrackingController::class, 'document'])->name('tracking.document.eu');
Route::get('/es/track/doc/{token?}/{document?}', [TrackingController::class, 'document'])->name('tracking.document.es');

require __DIR__ . '/public.php';
require __DIR__ . '/private.php';
require __DIR__ . '/settings.php';
