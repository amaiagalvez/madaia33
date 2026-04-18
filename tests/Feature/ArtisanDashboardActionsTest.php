<?php

use Mockery;
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
