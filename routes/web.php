<?php

use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'throttle:5,1'])->post('/forgot-password', PasswordResetLinkController::class)->name('password.email');

require __DIR__ . '/public.php';
require __DIR__ . '/private.php';
require __DIR__ . '/settings.php';
