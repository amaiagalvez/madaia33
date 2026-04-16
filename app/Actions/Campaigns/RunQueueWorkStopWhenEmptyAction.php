<?php

namespace App\Actions\Campaigns;

use Throwable;
use Illuminate\Support\Facades\Artisan;

class RunQueueWorkStopWhenEmptyAction
{
    public function execute(): bool
    {
        try {
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return false;
        }

        return true;
    }
}
