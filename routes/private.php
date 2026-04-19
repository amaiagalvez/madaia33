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
use App\Http\Controllers\Admin\OwnersPdfController;
use App\Http\Controllers\Admin\VotingResultsController;
use App\Http\Controllers\CampaignWhatsappCsvController;

Route::middleware(['auth', 'admin.panel'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard.index', [
            'publishedNoticesCount' => Notice::where('is_public', true)->count(),
            'totalNoticesCount' => Notice::count(),
            'imagesCount' => Image::count(),
            'unreadMessagesCount' => ContactMessage::where('is_read', false)->count(),
            'usersCount' => User::query()->count(),
            'ownersCount' => Owner::query()->count(),
            'invalidContactsOwnersCount' => Owner::query()
                ->where('coprop1_email_invalid', true)
                ->orWhere('coprop1_phone_invalid', true)
                ->orWhere('coprop2_email_invalid', true)
                ->orWhere('coprop2_phone_invalid', true)
                ->count(),
            'locationsCount' => Location::query()->count(),
            'votingsCount' => Voting::query()->count(),
        ]);
    })->name('dashboard');

    Route::get('/iragarkiak', fn () => view('admin.notices'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('notices');
    Route::get('/bidalketak', fn () => view('admin.campaigns'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns');
    Route::get('/bidalketak/txantiloiak', fn () => view('admin.campaign-templates'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.templates');
    Route::get('/bidalketak/kontaktu-okerrak', fn () => view('admin.invalid-contacts'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.invalid-contacts');
    Route::get('/bidalketak/{campaign}', fn (Campaign $campaign) => view('admin.campaign-detail', ['campaign' => $campaign]))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.show');
    Route::get('/bidalketak/{campaign}/whatsapp-csv', CampaignWhatsappCsvController::class)
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('campaigns.whatsapp-csv');
    Route::get('/argazkiak', fn () => view('admin.images'))
        ->middleware('role:superadmin')
        ->name('images');
    Route::get('/mezuak', fn () => view('admin.messages'))
        ->middleware('role:superadmin,admin_general')
        ->name('messages');
    Route::get('/konfigurazioa', fn () => view('admin.settings'))
        ->middleware('role:superadmin')
        ->name('settings');
    Route::get('/atariak', fn () => view('admin.locations.index', ['type' => 'portal']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.portals');
    Route::get('/lokalak', fn () => view('admin.locations.index', ['type' => 'local']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.locals');
    Route::get('/garajeak', fn () => view('admin.locations.index', ['type' => 'garage']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.garages');
    Route::get('/trasteroak', fn () => view('admin.locations.index', ['type' => 'storage']))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('locations.storages');
    Route::get('/finkak/{location}', function (Location $location) {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user?->canManageLocation($location), 403);

        return view('admin.locations.show', ['location' => $location]);
    })->name('locations.show');
    Route::get('/jabeak', fn () => view('admin.owners.index'))
        ->middleware('role:superadmin')
        ->name('owners.index');
    Route::get('/jabeak/pdf', OwnersPdfController::class)
        ->middleware('role:superadmin')
        ->name('owners.pdf');
    Route::get('/bozketak', fn () => view('admin.votings'))
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings');
    Route::get('/bozketak/{voting}/emaitzak', [VotingResultsController::class, 'show'])
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->whereNumber('voting')
        ->name('votings.results.show');
    Route::get('/bozketak/pdf/delegatua', [VotingPdfController::class, 'adminDelegated'])
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings.pdf.delegated');
    Route::get('/bozketak/pdf/presentziala', [VotingPdfController::class, 'adminInPerson'])
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings.pdf.in_person');
    Route::get('/bozketak/pdf/emaitzak', [VotingPdfController::class, 'adminResults'])
        ->middleware('role:superadmin,admin_general,admin_comunidad')
        ->name('votings.pdf.results');
    Route::get('/erabiltzaileak', fn () => view('admin.users.index'))
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
    Route::get('/artisan/database-copy', [ArtisanController::class, 'downloadDatabaseCopy'])
        ->middleware('role:superadmin')
        ->name('artisan.database_copy');
});

Route::middleware('auth')->post('/impostazioa/itzuli-nire-erabiltzailea', function (Request $request) {
    $impersonatorUserId = $request->session()->get('impersonator_user_id');

    abort_if(! is_numeric($impersonatorUserId), 403);

    $impersonator = User::query()->findOrFail((int) $impersonatorUserId);

    Auth::login($impersonator);
    $request->session()->forget('impersonator_user_id');

    return redirect()->route('admin.users.index');
})->name('admin.users.stop_impersonation');

Route::middleware('auth')->post('/votings/delegated/clear', [PublicVotingController::class, 'clearDelegatedVoting'])
    ->name('votings.delegated.clear');
