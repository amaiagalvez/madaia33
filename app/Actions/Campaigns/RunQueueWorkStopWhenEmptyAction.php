<?php

namespace App\Actions\Campaigns;

use Throwable;
use Symfony\Component\Process\Process;

class RunQueueWorkStopWhenEmptyAction
{
    public function execute(): bool
    {
        try {
            $process = new Process(
                [PHP_BINARY, 'artisan', 'queue:work', '--stop-when-empty'],
                base_path(),
            );

            $process->setTimeout(300);
            $process->run();

            return $process->isSuccessful();
        } catch (Throwable $throwable) {
            report($throwable);

            return false;
        }
    }
}
