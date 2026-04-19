<?php

namespace App\Actions\Campaigns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Throwable;

class RunQueueWorkStopWhenEmptyAction
{
    private const MAX_JOBS_PER_REQUEST = 1000;
    private const MAX_SECONDS_PER_REQUEST = 300;

    public function execute(): bool
    {
        try {
            $connection = (string) config('queue.default', 'database');
            $queue = (string) config("queue.connections.{$connection}.queue", 'default');
            $startedAt = microtime(true);
            $processedJobs = 0;

            while (Queue::connection($connection)->size($queue) > 0) {
                $exitCode = Artisan::call('queue:work', [
                    '--once' => true,
                    '--queue' => $queue,
                ]);

                if ($exitCode !== 0) {
                    return false;
                }

                $processedJobs++;

                if ($processedJobs >= self::MAX_JOBS_PER_REQUEST) {
                    return false;
                }

                if ((microtime(true) - $startedAt) >= self::MAX_SECONDS_PER_REQUEST) {
                    return false;
                }
            }

            return true;
        } catch (Throwable $throwable) {
            report($throwable);

            return false;
        }
    }
}
