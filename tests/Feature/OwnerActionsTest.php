<?php

use App\Actions\CreateOwnerAction;
use App\Models\Owner;
use App\Models\User;
use App\Actions\DeactivateOwnerAction;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

describe('CreateOwnerAction', function () {
    it('creates an owner with the required data', function () {
        Notification::fake();

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_phone' => '600111222',
            'coprop1_email' => 'miren@example.com',
        ]);

        expect($owner)->toBeInstanceOf(Owner::class)
            ->and($owner->coprop1_name)->toBe('Miren Etxeberria')
            ->and($owner->coprop1_dni)->toBe('12345678Z')
            ->and($owner->coprop1_email)->toBe('miren@example.com');
    });

    it('creates a user linked to the owner with DNI as name', function () {
        Notification::fake();

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
        ]);

        $user = User::find($owner->user_id);

        expect($user)->not->toBeNull()
            ->and($user->name)->toBe('12345678Z')
            ->and($user->email)->toBe('miren@example.com')
            ->and($user->is_active)->toBeTrue();
    });

    it('sends a password reset notification to the new user', function () {
        Notification::fake();

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
        ]);

        Notification::assertSentTo(
            User::find($owner->user_id),
            ResetPassword::class,
        );
    });

    it('creates owner with optional second co-owner data', function () {
        Notification::fake();

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
            'coprop2_name' => 'Jon Etxeberria',
            'coprop2_dni' => '87654321X',
            'coprop2_email' => 'jon@example.com',
        ]);

        expect($owner->coprop2_name)->toBe('Jon Etxeberria')
            ->and($owner->coprop2_dni)->toBe('87654321X');
    });
});

describe('DeactivateOwnerAction', function () {
    it('sets is_active to false on the linked user', function () {
        $owner = Owner::factory()->create();

        $action = new DeactivateOwnerAction;
        $action->execute($owner);

        expect(User::find($owner->user_id)->is_active)->toBeFalse();
    });

    it('does not delete the owner record', function () {
        $owner = Owner::factory()->create();

        $action = new DeactivateOwnerAction;
        $action->execute($owner);

        expect(Owner::find($owner->id))->not->toBeNull();
    });
});
