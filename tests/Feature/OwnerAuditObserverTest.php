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
});
