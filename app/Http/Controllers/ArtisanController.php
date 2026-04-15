<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class ArtisanController extends Controller
{
    public function clear(): RedirectResponse
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');

        if (! file_exists(base_path('composer.phar'))) {
            return redirect()->route('admin.dashboard')->with('status', 'Cache cleared, pero no se encontro composer.phar.');
        }

        $composerUpdate = Process::path(base_path())
            ->timeout(1800)
            ->run(['php', 'composer.phar', 'update', '--no-interaction']);

        if ($composerUpdate->failed()) {
            return redirect()->route('admin.dashboard')->with('status', 'Cache cleared, pero composer.phar update ha fallado.');
        }

        return redirect()->route('admin.dashboard')->with('status', 'Cache cleared + composer.phar update ejecutado.');
    }

    public function migration_and_seeds(): RedirectResponse
    {
        Artisan::call('migrate --force');
        Artisan::call('db:seed --force');

        return redirect()->route('admin.dashboard')->with('status', 'Database migrated!');
    }

    public function queueWorkStopWhenEmpty(): RedirectResponse
    {
        try {
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('admin.dashboard')->with('status', __('admin.queue.status_failed'));
        }

        return redirect()->route('admin.dashboard')->with('status', __('admin.queue.status_finished'));
    }
}
