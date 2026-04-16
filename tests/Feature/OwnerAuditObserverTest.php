<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\OwnerAuditLog;
use Illuminate\Support\Facades\Auth;

describe('OwnerAuditObserver', function () {
    it('records audit log entries when owner fields are updated', function () {
        $admin = User::factory()->create();
        Auth::login($admin);

        $owner = Owner::factory()->create(['coprop1_name' => 'Miren Etxeberria']);

        $owner->update(['coprop1_name' => 'Miren Goikoetxea']);

        $log = OwnerAuditLog::where('owner_id', $owner->id)
            ->where('field', 'coprop1_name')
            ->latest()
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->old_value)->toBe('Miren Etxeberria')
            ->and($log->new_value)->toBe('Miren Goikoetxea')
            ->and($log->changed_by_user_id)->toBe($admin->id);
    });

    it('records multiple field changes in separate audit log entries', function () {
        $admin = User::factory()->create();
        Auth::login($admin);

        $owner = Owner::factory()->create([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_phone' => '600111222',
        ]);

        $owner->update([
            'coprop1_name' => 'Miren Goikoetxea',
            'coprop1_phone' => '699999999',
        ]);

        $logsCount = OwnerAuditLog::where('owner_id', $owner->id)->count();

        expect($logsCount)->toBe(2);
    });

    it('resets coprop1 email error counters when coprop1 email changes', function () {
        $owner = Owner::factory()->create([
            'coprop1_email' => 'old@example.test',
            'coprop1_email_error_count' => 3,
            'coprop1_email_invalid' => true,
        ]);

        $owner->update([
            'coprop1_email' => 'new@example.test',
        ]);

        $owner->refresh();

        expect($owner->coprop1_email_error_count)->toBe(0)
            ->and($owner->coprop1_email_invalid)->toBeFalse();
    });

    it('resets coprop1 phone error counters when coprop1 phone changes', function () {
        $owner = Owner::factory()->create([
            'coprop1_phone' => '600111222',
            'coprop1_phone_error_count' => 3,
            'coprop1_phone_invalid' => true,
        ]);

        $owner->update([
            'coprop1_phone' => '699999999',
        ]);

        $owner->refresh();

        expect($owner->coprop1_phone_error_count)->toBe(0)
            ->and($owner->coprop1_phone_invalid)->toBeFalse();
    });

    it('resets coprop2 phone error counters when coprop2 phone changes', function () {
        $owner = Owner::factory()->withSecondCoProp()->create([
            'coprop2_phone' => '611111111',
            'coprop2_phone_error_count' => 3,
            'coprop2_phone_invalid' => true,
        ]);

        $owner->update([
            'coprop2_phone' => '622222222',
        ]);

        $owner->refresh();

        expect($owner->coprop2_phone_error_count)->toBe(0)
            ->and($owner->coprop2_phone_invalid)->toBeFalse();
    });

    it('does not reset unrelated counters when only one contact field changes', function () {
        $owner = Owner::factory()->create([
            'coprop1_email' => 'old@example.test',
            'coprop1_email_error_count' => 3,
            'coprop1_email_invalid' => true,
            'coprop1_phone_error_count' => 2,
            'coprop1_phone_invalid' => true,
            'coprop2_email_error_count' => 2,
            'coprop2_email_invalid' => true,
            'coprop2_phone_error_count' => 2,
            'coprop2_phone_invalid' => true,
        ]);

        $owner->update([
            'coprop1_email' => 'new@example.test',
        ]);

        $owner->refresh();

        expect($owner->coprop1_email_error_count)->toBe(0)
            ->and($owner->coprop1_email_invalid)->toBeFalse()
            ->and($owner->coprop1_phone_error_count)->toBe(2)
            ->and($owner->coprop1_phone_invalid)->toBeTrue()
            ->and($owner->coprop2_email_error_count)->toBe(2)
            ->and($owner->coprop2_email_invalid)->toBeTrue()
            ->and($owner->coprop2_phone_error_count)->toBe(2)
            ->and($owner->coprop2_phone_invalid)->toBeTrue();
    });
});
