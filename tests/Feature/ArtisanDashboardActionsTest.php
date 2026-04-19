<?php

use App\Models\Role;
use App\Models\User;
use App\Actions\Campaigns\RunQueueWorkStopWhenEmptyAction;

it('allows superadmin to run queue worker until queue is empty', function () {
    $csrfToken = 'queue-action-token';
    $user = adminUser();

    $actionMock = Mockery::mock(RunQueueWorkStopWhenEmptyAction::class);
    $actionMock->shouldReceive('execute')
        ->once()
        ->andReturn(true);
    app()->instance(RunQueueWorkStopWhenEmptyAction::class, $actionMock);

    test()->withSession(['_token' => $csrfToken])
        ->actingAs($user)
        ->post(route('admin.artisan.queue_work_stop_when_empty'), ['_token' => $csrfToken])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('status', __('admin.queue.status_finished'));
});

it('forbids non superadmin users from running queue worker action', function () {
    $csrfToken = 'queue-action-token';
    $user = User::factory()->create();
    $user->assignRole(Role::GENERAL_ADMIN);

    test()->withSession(['_token' => $csrfToken])
        ->actingAs($user)
        ->post(route('admin.artisan.queue_work_stop_when_empty'), ['_token' => $csrfToken])
        ->assertForbidden();
});

it('allows superadmin users to download an sqlite database copy', function () {
    $user = adminUser();

    $databasePath = tempnam(sys_get_temp_dir(), 'madaia33-db-copy-');

    expect($databasePath)->not->toBeFalse();

    file_put_contents($databasePath, 'sqlite-backup-test-content');

    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', $databasePath);

    $response = test()->actingAs($user)
        ->get(route('admin.artisan.database_copy'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/octet-stream');

    $contentDisposition = (string) $response->headers->get('content-disposition');
    expect($contentDisposition)->toContain('attachment; filename=sqlite-backup-');
    expect($contentDisposition)->toContain('.sqlite');

    @unlink($databasePath);
});

it('forbids non superadmin users from downloading database copy', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($user)
        ->get(route('admin.artisan.database_copy'))
        ->assertForbidden();
});
