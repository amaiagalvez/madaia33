<?php

use Mockery;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use App\Actions\Campaigns\RunQueueWorkStopWhenEmptyAction;

it('processes queue jobs in once mode until queue is empty', function () {
    config()->set('queue.default', 'database');
    config()->set('queue.connections.database.queue', 'default');

    $connection = Mockery::mock();
    $connection->shouldReceive('size')
        ->with('default')
        ->andReturn(2, 1, 0);

    Queue::shouldReceive('connection')
        ->with('database')
        ->andReturn($connection);

    Artisan::shouldReceive('call')
        ->twice()
        ->with('queue:work', [
            '--once' => true,
            '--queue' => 'default',
        ])
        ->andReturn(0);

    expect(app(RunQueueWorkStopWhenEmptyAction::class)->execute())->toBeTrue();
});

it('returns false when queue worker exits with a non-zero code', function () {
    config()->set('queue.default', 'database');
    config()->set('queue.connections.database.queue', 'default');

    $connection = Mockery::mock();
    $connection->shouldReceive('size')
        ->with('default')
        ->andReturn(1);

    Queue::shouldReceive('connection')
        ->with('database')
        ->andReturn($connection);

    Artisan::shouldReceive('call')
        ->once()
        ->with('queue:work', [
            '--once' => true,
            '--queue' => 'default',
        ])
        ->andReturn(1);

    expect(app(RunQueueWorkStopWhenEmptyAction::class)->execute())->toBeFalse();
});
