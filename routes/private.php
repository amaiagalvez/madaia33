<?php

use App\Models\User;
use App\Models\Image;
use App\Models\Owner;
use App\Models\Notice;
use App\Models\Voting;
use App\Models\Campaign;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\VotingPdfController;
use App\Http\Controllers\PublicVotingController;

Route::middleware(['auth', 'admin.panel'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard.index', [
            'publishedNoticesCount' => Notice::where('is_public', true)->count(),
            'totalNoticesCount' => Notice::count(),
            'imagesCount' => Image::count(),
            'unreadMessagesCount' => ContactMessage::where('is_read', false)->count(),
            'usersCount' => User::query()->count(),
            'ownersCount' => Owner::query()->count(),
            'locationsCount' => Location::query()->count(),
            'votingsCount' => Voting::query()->count(),
        ]);
    })->name('dashboard');

    Route::get('/avisos', fn() => view('admin.notices'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('notices');
    Route::get('/campanas', fn() => view('admin.campaigns'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns');
    Route::get('/campanas/plantillas', fn() => view('admin.campaign-templates'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.templates');
    Route::get('/campanas/contactos-invalidos', fn() => view('admin.invalid-contacts'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.invalid-contacts');
    Route::get('/campanas/{campaign}', fn(Campaign $campaign) => view('admin.campaign-detail', ['campaign' => $campaign]))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.show');
    Route::get('/campanas/{campaign}/whatsapp-csv', App\Http\Controllers\CampaignWhatsappCsvController::class)
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.whatsapp-csv');
    Route::get('/imagenes', fn() => view('admin.images'))
        ->middleware('role:superadmin')
        ->name('images');
    Route::get('/mensajes', fn() => view('admin.messages'))
        ->middleware('role:superadmin,admin_general')
        ->name('messages');
    Route::get('/configuracion', fn() => view('admin.settings'))
        ->middleware('role:superadmin')
        ->name('settings');
    Route::get('/portales', fn() => view('admin.locations.index', ['type' => 'portal']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.portals');
    Route::get('/locales', fn() => view('admin.locations.index', ['type' => 'local']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.locals');
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
    Route::get('/votaciones', fn() => view('admin.votings'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings');
    Route::get('/votaciones/pdf/delegado', [VotingPdfController::class, 'adminDelegated'])
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings.pdf.delegated');
    Route::get('/votaciones/pdf/presencial', [VotingPdfController::class, 'adminInPerson'])
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings.pdf.in_person');
    Route::get('/votaciones/pdf/resultados', [VotingPdfController::class, 'adminResults'])
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings.pdf.results');
    Route::get('/usuarios', fn() => view('admin.users.index'))
        ->middleware('role:superadmin')
        ->name('users.index');

    Route::post('/artisan/clear', [ArtisanController::class, 'clear'])
        ->middleware('role:superadmin')
        ->name('artisan.clear');
    Route::post('/artisan/migrate-and-seed', [ArtisanController::class, 'migration_and_seeds'])
        ->middleware('role:superadmin')
        ->name('artisan.migrate_and_seed');
    Route::post('/artisan/queue-work-stop-when-empty', [ArtisanController::class, 'queueWorkStopWhenEmpty'])
        ->middleware('role:superadmin')
        ->name('artisan.queue_work_stop_when_empty');
});

Route::middleware('auth')->post('/impersonacion/volver-a-mi-usuario', function (Request $request) {
    $impersonatorUserId = $request->session()->get('impersonator_user_id');

    abort_if(! is_numeric($impersonatorUserId), 403);

    $impersonator = User::query()->findOrFail((int) $impersonatorUserId);

    Auth::login($impersonator);
    $request->session()->forget('impersonator_user_id');

    return redirect()->route('admin.users.index');
})->name('admin.users.stop_impersonation');

Route::middleware('auth')->post('/votings/delegated/clear', [PublicVotingController::class, 'clearDelegatedVoting'])
    ->name('votings.delegated.clear');
